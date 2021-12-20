<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTable extends Seeder
{
    public function run()
    {
        $users = [
            ['name' => 'Zahar', 'email' =>  'zahardementiev@gmail.com', 'password' => Hash::make('123045607890z')],
            ['name' => 'Admin', 'email' =>  'admin@adminich.ru', 'password' => Hash::make('qwerty123')],
        ];

        foreach ($users as $user) {
            DB::table('users')->insert($user);
        }

        $roles = [
            ['name' => 'admin', 'guard_name' => 'web']
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert($role);
        }

        $userRoles = [
            ['role_id' => 1, 'model_type' => User::class, 'model_id' => 1],
            ['role_id' => 1, 'model_type' => User::class, 'model_id' => 2],
        ];

        foreach ($userRoles as $role) {
            DB::table('model_has_roles')->insert($role);
        }
    }
}
