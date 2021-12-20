<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTable extends Seeder
{
    public function run()
    {
        $settings = [
            ['name' => 'next_send_days', 'value' =>  10],
            ['name' => 'setup_payment_amount', 'value' =>  1],
            ['name' => 'payment_amount', 'value' =>  199],
            ['name' => 'shop_id', 'value' =>  1],
            ['name' => 'secret_key', 'value' => 'live__1111'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insert($setting);
        }
    }
}
