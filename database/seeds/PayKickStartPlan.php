<?php

use Illuminate\Database\Seeder;

class PayKickStartPlan extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plan = [
            [
                'campaign_id'=>'14335',
                'product_id'=>'44238',
                'name'=>'Adilo Lifetime Video Hosting (Personal)',
                'price'=>'47',
                'stripe_id'=>'personal-paykickstart-static'
            ],
            [
                'campaign_id'=>'14335',
                'product_id'=>'44253',
                'name'=>'Adilo Lifetime Video Hosting (Marketer)',
                'price'=>'77',
                'stripe_id'=>'marketer-paykickstart-static'

            ],
            [
                'campaign_id'=>'14335',
                'product_id'=>'44254',
                'name'=>'Adilo Lifetime Video Hosting (Commercial)',
                'price'=>'97',
                'stripe_id'=>'commercial-paykickstart-static'

            ],
            [
                'campaign_id'=>'14335',
                'product_id'=>'44256',
                'name'=>'Adilo ELITE Membership Upgrade',
                'price'=>'99',
                'stripe_id'=>'elite-paykickstart-static'

            ],
        ];
        foreach($plan as $val){
            $q =  DB::table('paystick_plainfo')->where($val)->first();
            if($q==null){
             DB::table('paystick_plainfo')->insert($val);
            }
        }
    }
}
