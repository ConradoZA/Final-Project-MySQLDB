  <?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'User',
                'email' => 'user@gmail.com',
                'password' => Hash::make('123User+'),
                'image_path'=>'profile.jpg'
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('123Admin+'),
                'image_path'=>'profile.jpg'
            ],
            [
                'name' => 'Another',
                'email' => 'another@gmail.com',
                'password' => Hash::make('123Another+'),
                'image_path'=>'profile.jpg'
            ]
        ]);
    }
}
