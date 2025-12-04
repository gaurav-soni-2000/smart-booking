<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::create(['name'=>'Haircut','duration_minutes'=>30,'price'=>15]);
        Service::create(['name'=>'Coloring','duration_minutes'=>60,'price'=>50]);
        Service::create(['name'=>'Consultation','duration_minutes'=>15,'price'=>0]);
    }
}
