<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        \Illuminate\Database\Eloquent\Model::unguard();

        $this->call(KrsnaSlavaSeeder::class);
        $this->call(MestoSeeder::class);
        $this->call(PredmetSeeder::class);
        $this->call(SportSeeder::class);
        $this->call(SportskoAngazovanjeSeeder::class);
        $this->call(SrednjeSkoleFakultetiSeeder::class);
        $this->call(StatusStudiranjaSeeder::class);
        $this->call(StudijskiProgramSeeder::class);
        $this->call(TipStudijaSeeder::class);
        $this->call(PrilozenaDokumentaSeeder::class);
    }
}
