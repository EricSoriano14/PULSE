<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $ecoastId = DB::table('departments')->where('name', 'ECOAST')->value('id');
            $ccsId = DB::table('departments')->where('name', 'CCS')->value('id');
            $coeId = DB::table('departments')->where('name', 'COE')->value('id');

            if (!$ecoastId) {
                throw new RuntimeException('ECOAST department not found.');
            }

            DB::table('users')
                ->whereIn('department', ['CCS', 'COE'])
                ->update(['department' => 'ECOAST']);

            DB::table('reports')
                ->whereIn('department', ['CCS', 'COE'])
                ->update(['department' => 'ECOAST']);

            if (DB::getSchemaBuilder()->hasColumn('announcements', 'department')) {
                DB::table('announcements')
                    ->whereIn('department', ['CCS', 'COE'])
                    ->update(['department' => 'ECOAST']);
            }

            if ($ccsId) {
                DB::table('students')
                    ->where('department_id', $ccsId)
                    ->update(['department_id' => $ecoastId]);

                if (DB::getSchemaBuilder()->hasColumn('announcements', 'department_target_id')) {
                    DB::table('announcements')
                        ->where('department_target_id', $ccsId)
                        ->update(['department_target_id' => $ecoastId]);
                }
            }

            if ($coeId) {
                DB::table('students')
                    ->where('department_id', $coeId)
                    ->update(['department_id' => $ecoastId]);

                if (DB::getSchemaBuilder()->hasColumn('announcements', 'department_target_id')) {
                    DB::table('announcements')
                        ->where('department_target_id', $coeId)
                        ->update(['department_target_id' => $ecoastId]);
                }
            }

            $ecoastBsitId = DB::table('courses')
                ->where('department_id', $ecoastId)
                ->where('name', 'Bachelor of Science in Information Technology')
                ->value('id');

            $ecoastBscsId = DB::table('courses')
                ->where('department_id', $ecoastId)
                ->where('name', 'Bachelor of Science in Computer Science')
                ->value('id');

            $ecoastCivilId = DB::table('courses')
                ->where('department_id', $ecoastId)
                ->where('name', 'Bachelor of Science in Civil Engineering')
                ->value('id');

            $ccsBsitId = $ccsId
                ? DB::table('courses')
                    ->where('department_id', $ccsId)
                    ->where('name', 'Bachelor of Science in Information Technology')
                    ->value('id')
                : null;

            $ccsBscsId = $ccsId
                ? DB::table('courses')
                    ->where('department_id', $ccsId)
                    ->where('name', 'Bachelor of Science in Computer Science')
                    ->value('id')
                : null;

            $coeCivilId = $coeId
                ? DB::table('courses')
                    ->where('department_id', $coeId)
                    ->where('name', 'Bachelor of Science in Civil Engineering')
                    ->value('id')
                : null;

            if ($ccsBsitId && $ecoastBsitId) {
                DB::table('students')->where('course_id', $ccsBsitId)->update(['course_id' => $ecoastBsitId]);
                DB::table('courses')->where('id', $ccsBsitId)->delete();
            }

            if ($ccsBscsId && $ecoastBscsId) {
                DB::table('students')->where('course_id', $ccsBscsId)->update(['course_id' => $ecoastBscsId]);
                DB::table('courses')->where('id', $ccsBscsId)->delete();
            }

            if ($coeCivilId && $ecoastCivilId) {
                DB::table('students')->where('course_id', $coeCivilId)->update(['course_id' => $ecoastCivilId]);
                DB::table('courses')->where('id', $coeCivilId)->delete();
            }

            if ($ccsId) {
                DB::table('departments')->where('id', $ccsId)->delete();
            }

            if ($coeId) {
                DB::table('departments')->where('id', $coeId)->delete();
            }
        });
    }

    public function down(): void
    {
        // Data correction only. No rollback.
    }
};