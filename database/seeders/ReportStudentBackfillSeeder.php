<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;
use App\Models\Student;
use App\Models\User;

class ReportStudentBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $reports = Report::query()
            ->whereNull('student_id')
            ->whereNotNull('user_id')
            ->get();

        foreach ($reports as $report) {
            $user = User::find($report->user_id);
            if (!$user) continue;

            $student = Student::query()->where('user_id', $user->id)->first();
            if (!$student) continue;

            $report->student_id = $student->id;

            // Optional: also backfill Phase 4 fields if empty
            if (empty($report->calamity_type) && !empty($report->calamity)) {
                $report->calamity_type = $report->calamity;
            }

            $report->save();
        }
    }
}
