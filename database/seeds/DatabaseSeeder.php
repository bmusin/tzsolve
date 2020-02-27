<?php

use Illuminate\Database\Seeder;

use App\Client;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $u           = new User;
        $u->name     = 'Bulat Musin';
        $u->email    = 'mbulatka@yandex.ru';
        $u->password = Hash::make('bulat123');
        $u->save();

        $c           = new Client;
        $c->name     = 'Musin Bulat';
        $c->email    = 'bulatmusin@outlook.com';
        $c->password = Hash::make('bulat123');
        $c->save();
    }
}
