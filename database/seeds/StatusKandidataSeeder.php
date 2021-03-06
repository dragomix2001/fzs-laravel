<?php

use Illuminate\Database\Seeder;

class StatusKandidataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statusi = array('уписан', 'одустао', 'није уписан');

        foreach ($statusi as $s) {
            $status= new \App\StatusKandidata();

            $status->naziv = $s;

            $status->indikatorAktivan = 1;

            $status->save();
        }
    }
}
