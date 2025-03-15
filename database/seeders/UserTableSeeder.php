<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class UserTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $users = User::create([
            'name' => 'MD Aidid Alam',
            'email' => 'test@test.com',
            'password' => Hash::make('12341234')
        ]);
    }
}
