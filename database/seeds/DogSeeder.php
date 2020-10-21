<?php

use App\Dog;

use Illuminate\Database\Seeder;

class DogSeeder extends Seeder  {

    public function run () {

        factory (Dog::class, 30) -> create ();

    }
}
