<?php
namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $akun = [
            ['Admin Sistem',   'admin@sei.test',    'admin'],
            ['Sales Satu',     'sales@sei.test',    'sales'],
            ['Gudang Satu',    'gudang@sei.test',   'manajer_gudang'],
            ['Keuangan Satu',  'keuangan@sei.test', 'keuangan'],
            ['Pimpinan Satu',  'pimpinan@sei.test', 'pimpinan'],
        ];

        foreach ($akun as [$nama, $email, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $nama,
                    'password' => 'password123',          // otomatis di-hash (cast 'hashed')
                    'role_id' => Role::where('nama', $role)->value('id'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}