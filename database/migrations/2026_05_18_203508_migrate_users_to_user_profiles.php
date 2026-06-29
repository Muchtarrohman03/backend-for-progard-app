<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('users')->get();

        foreach ($users as $user) {

            $divisionId = null;

            if ($user->division) {

                $division = DB::table('divisions')
                    ->where('name', $user->division)
                    ->first();

                if (!$division) {
                    $divisionId = DB::table('divisions')->insertGetId([
                        'name' => $user->division,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $divisionId = $division->id;
                }
            }

            DB::table('user_profiles')->insert([
                'user_id' => $user->id,
                'name' => $user->name,
                'gender' => $user->gender,
                'division_id' => $divisionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('user_profiles')->truncate();
    }
};
