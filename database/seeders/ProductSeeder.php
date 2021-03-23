<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $length = 5;
        for ($i=0; $i < $length; $i++) { 
            DB::table('products')->insert([
                'product_id' => $i,
                'product_name' => 'Product'.$i,
                'quantity' => ($i * 10),
            ]);
        }
    }
}
