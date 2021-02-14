<?php

use Illuminate\Database\Seeder;

class SignUpCouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $Coupon = [
            ['name'=>'Starter Coupon','coupon_id'=>'Jd3Zh9je','plan_id'=>'Starter','status'=>'active','amount'=>'20'],
            ['name'=>'Pro','coupon_id'=>'w9oXv3sR','plan_id'=>'Pro','status'=>'active','amount'=>'100'],
            ['name'=>'Business','coupon_id'=>'xbFxaiOR','plan_id'=>'Business','status'=>'active','amount'=>'100'],

        ];
        foreach($Coupon as $val){
            $q =  DB::table('signup_coupons')->where($val)->first();
            if($q==null){
             DB::table('signup_coupons')->insert($val);
            }
        }
    }
}
