<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Report;
use App\Models\ReportAction;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportActionController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    protected function ensureUserCanActOnReport(string $role, Report $report): void
    {
        $userId = Auth::id();

        if (in_array($role, ['admin', 'css'], true)) {
            return;
        }

        if ($role === 'faculty') {
            if ((int) $report->assigned_faculty_id !== (int) $userId) {
                abort(403, 'You are not allowed to act on this report.');
            }
            return;
        }

        if ($role === 'co_css') {
            if ((int) $report->assigned_co_css_id !== (int) $userId) {
                abort(403, 'You are not allowed to act on this report.');
            }
            return;
        }

        abort(403, 'Unauthorized.');
    }

    public function recommend(Request $request, Report $report)
    {
        $role = strtolower(trim((string) (Auth::user()?->role ?? '')));

        if (!in_array($role, ['css', 'faculty', 'co_css'], true)) {
            abort(403);
        }

        $this->ensureUserCanActOnReport($role, $report);

        $data = $request->validate([
            'recommended_action' => ['required', 'in:accept,decline'],
            'recommended_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($report->status !== 'pending') {
            return back()->with('error', 'Recommendation is only allowed while the report is pending.');
        }

        $action = ReportAction::firstOrCreate(
            ['report_id' => $report->id],
            ['report_id' => $report->id]
        );

        $action->update([
            'recommended_by_user_id' => Auth::id(),
            'recommended_action' => $data['recommended_action'],
            'recommended_note' => $data['recommended_note'] ?? null,
            'recommended_at' => now(),
        ]);

        return back()->with('success', 'Recommendation submitted.');
    }

    /**
     * Step 3: CSS/Admin/Faculty/Co-CSS Decision (with remarks)
     */
    public function decide(Request $request, Report $report)
    {
        $role = strtolower(trim((string) (Auth::user()?->role ?? '')));
        if (!in_array($role, ['admin', 'css', 'faculty', 'co_css'], true)) {
            abort(403);
        }

        $this->ensureUserCanActOnReport($role, $report);

        $data = $request->validate([
            'decision' => ['required', 'in:accepted,declined'],
            'decision_public_remark' => ['nullable', 'string', 'max:2000'],
            'decision_internal_note' => ['nullable', 'string', 'max:2000'],
            'recommended_action' => ['nullable', 'in:accept,decline'],
            'recommended_note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            DB::transaction(function () use ($report, $data) {
                $lockedReport = Report::where('id', $report->id)->lockForUpdate()->first();

                if (!$lockedReport) {
                    abort(404, 'Report not found.');
                }

                if ($lockedReport->status !== 'pending') {
                    abort(422, 'Decision is only allowed while the report is pending.');
                }

                $action = ReportAction::firstOrNew(['report_id' => $lockedReport->id]);

                if (!empty($action->decision_at) || !empty($action->decision)) {
                    abort(409, 'This report already has a decision.');
                }

                if (
                    array_key_exists('recommended_action', $data) ||
                    array_key_exists('recommended_note', $data)
                ) {
                    if (!empty($data['recommended_action']) || !empty($data['recommended_note'])) {
                        $action->recommended_by_user_id = $action->recommended_by_user_id ?? Auth::id();
                        $action->recommended_action = $data['recommended_action']
                            ?? $action->recommended_action
                            ?? ($data['decision'] === 'accepted' ? 'accept' : 'decline');
                        $action->recommended_note = $data['recommended_note'] ?? $action->recommended_note;
                        $action->recommended_at = $action->recommended_at ?? now();
                    }
                }

                $action->decided_by_user_id = Auth::id();
                $action->decision = $data['decision'];
                $action->decision_public_remark = $data['decision_public_remark'] ?? null;
                $action->public_remark = $data['decision_public_remark'] ?? null;
                $action->decision_internal_note = $data['decision_internal_note'] ?? null;
                $action->decision_at = now();
                $action->save();

                $lockedReport->status = $data['decision'];
                $lockedReport->reviewed_at = now();
                $lockedReport->reviewed_by = Auth::id();
                $lockedReport->save();

                $this->notifyStudentAboutDecision($lockedReport, $action);
            });

            return back()->with('success', 'Decision saved.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Step 4: Action Taken
     * - Only allowed when report status = accepted
     * - Writes action_taken fields to report_actions
     * - Updates reports.status => action_taken
     */
    public function actionTaken(Request $request, Report $report)
    {
        $role = strtolower(trim((string) (Auth::user()?->role ?? '')));
        if (!in_array($role, ['admin', 'css', 'faculty', 'co_css'], true)) {
            abort(403);
        }

        $this->ensureUserCanActOnReport($role, $report);

        $data = $request->validate([
            'action_taken_note' => ['required', 'string', 'max:4000'],
        ]);

        try {
            DB::transaction(function () use ($report, $data) {
                $lockedReport = Report::where('id', $report->id)->lockForUpdate()->first();

                if (!$lockedReport) {
                    abort(404, 'Report not found.');
                }

                if ($lockedReport->status !== 'accepted') {
                    abort(422, 'Action Taken is only allowed when the report is accepted.');
                }

                $action = ReportAction::firstOrNew(['report_id' => $lockedReport->id]);

                if (!empty($action->action_taken_at) || !empty($action->action_taken_note)) {
                    abort(409, 'Action Taken is already recorded for this report.');
                }

                $action->action_taken_by_user_id = Auth::id();
                $action->action_taken_note = $data['action_taken_note'];
                $action->action_taken_at = now();
                $action->save();

                $lockedReport->status = 'action_taken';
                $lockedReport->save();

                $this->notifyStudentAboutActionTaken($lockedReport, $action);
            });

            return back()->with('success', 'Action Taken saved.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function notifyStudentAboutDecision(Report $report, ReportAction $action): void
    {
        $student = $this->resolveStudentRecipient($report);

        if (!$student) {
            return;
        }

        $reportTitle = trim((string) $report->title) !== '' ? $report->title : 'Your report';
        $statusLabel = $report->status === 'accepted' ? 'accepted' : 'declined';

        $message = "Your report \"{$reportTitle}\" was {$statusLabel}.";

        $publicRemark = trim((string) ($action->decision_public_remark ?? ''));
        if ($publicRemark !== '') {
            $message .= " Remark: {$publicRemark}";
        }

        $this->notificationService->createForUser(
            $student,
            Notification::TYPE_STUDENT_REPORT_UPDATE,
            'Report Update',
            $message,
            $report,
            null,
            Auth::user(),
            null
        );
    }

    private function notifyStudentAboutActionTaken(Report $report, ReportAction $action): void
    {
        $student = $this->resolveStudentRecipient($report);

        if (!$student) {
            return;
        }

        $reportTitle = trim((string) $report->title) !== '' ? $report->title : 'Your report';
        $message = "Action has been taken on your report \"{$reportTitle}\".";

        $actionNote = trim((string) ($action->action_taken_note ?? ''));
        if ($actionNote !== '') {
            $message .= " Note: {$actionNote}";
        }

        $this->notificationService->createForUser(
            $student,
            Notification::TYPE_STUDENT_REPORT_UPDATE,
            'Report Update',
            $message,
            $report,
            null,
            Auth::user(),
            null
        );
    }

    private function resolveStudentRecipient(Report $report): ?User
    {
        if (empty($report->user_id)) {
            return null;
        }

        return User::query()
            ->where('id', $report->user_id)
            ->where('role', 'student')
            ->where('status', 'active')
            ->first();
    }
}