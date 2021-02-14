<?php

use Illuminate\Database\Seeder;

class MeteredPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plan = [
                ['plan_id' => 'business-year-metered-dynamic-watermark',
                'plan_type' => 'business-annual-static',
                'unit_price' => 0.002
                ],
                [
                'plan_id' => 'business-year-metered-forensic-watermark',
                'plan_type' => 'business-annual-static',
                'unit_price' => 0.004
                ],
                [
                    'plan_id' => 'business-year-metered-enriched-contacts',
                    'plan_type' => 'business-annual-static',
                    'unit_price' => 0.05
                ],
                [
                    'plan_id' => 'business-year-metered-translations',
                    'plan_type' => 'business-annual-static',
                    'unit_price' => 0.04
                ],
                [
                    'plan_id' => 'business-year-metered-captions',
                    'plan_type' => 'business-annual-static',
                    'unit_price' => 0.04
                ],
                [
                    'plan_id' => 'business-year-metered-anti-piracy',
                    'plan_type' => 'business-annual-static',
                    'unit_price' => 0.08
                ],
                [
                    'plan_id' => 'business-year-metered-bandwidth',
                    'plan_type' => 'business-annual-static',
                    'unit_price' => 0.08
                ],
                //pro yearly
                [
                    'plan_id' => 'pro-yearly-metered-dynamic-watermark',
                    'plan_type' => 'pro-annual-static',
                    'unit_price' => 0.04
                ],
                [
                    'plan_id' => 'pro-yearly-metered-forensic-watermark',
                    'plan_type' => 'pro-annual-static',
                    'unit_price' => 0.06
                ],
                [
                    'plan_id' => 'pro-yearly-metered-enriched-contacts',
                    'plan_type' => 'pro-annual-static',
                    'unit_price' => 0.05
                ],
                [
                    'plan_id' => 'pro-yearly-metered-translations',
                    'plan_type' => 'pro-annual-static',
                    'unit_price' => 0.05
                ],
                [
                    'plan_id' => 'pro-yearly-metered-captions',
                    'plan_type' => 'pro-annual-static',
                    'unit_price' => 0.05
                ],
                [
                    'plan_id' => 'pro-yearly-metered-anti-piracy',
                    'plan_type' => 'pro-annual-static',
                    'unit_price' => 0.08
                ],
                [
                    'plan_id' => 'pro-yearly-metered-bandwidth',
                    'plan_type' => 'pro-annual-static',
                    'unit_price' => 0.08
                ],
                //starter yearly
                [
                    'plan_id' => 'starter-yearly-metered-dynamic-watermark',
                    'plan_type' => 'starter-annual-static',
                    'unit_price' => 0.006
                ],
                [
                    'plan_id' => 'starter-yearly-metered-forensic-watermark',
                    'plan_type' => 'starter-annual-static',
                    'unit_price' => 0.008
                ],
                [
                    'plan_id' => 'starter-yearly-metered-enriched-contacts',
                    'plan_type' => 'starter-annual-static',
                    'unit_price' => 0.05
                ],
                [
                    'plan_id' => 'starter-yearly-metered-translations',
                    'plan_type' => 'starter-annual-static',
                    'unit_price' => 0.06
                ],
                [
                    'plan_id' => 'starter-yearly-metered-anti-piracy',
                    'plan_type' => 'starter-annual-static',
                    'unit_price' => 0.01
                ],
                [
                    'plan_id' => 'starter-yearly-metered-bandwidth',
                    'plan_type' => 'starter-annual-static',
                    'unit_price' => 0.01
                ], [
                    'plan_id' => 'starter-yearly-metered-captions',
                    'plan_type' => 'starter-annual-static',
                    'unit_price' => 0.06
                ],
                //business monthly
                [
                    'plan_id' => 'business-monthly-metered-dynamic-watermark',
                    'plan_type' => 'business-month-static',
                    'unit_price' => 0.02
                ],
                [
                    'plan_id' => 'business-monthly-metered-forensic-watermark',
                    'plan_type' => 'business-month-static',
                    'unit_price' => 0.04
                ],
                [
                    'plan_id' => 'business-monthly-metered-enriched-contacts',
                    'plan_type' => 'business-month-static',
                    'unit_price' => 0.05
                ],
                [
                    'plan_id' => 'business-monthly-metered-translations',
                    'plan_type' => 'business-month-static',
                    'unit_price' => 0.04
                ],
                [
                    'plan_id' => 'business-monthly-metered-captions',
                    'plan_type' => 'business-month-static',
                    'unit_price' => 0.04
                ],
                [
                    'plan_id' => 'business-monthly-metered-anti-piracy',
                    'plan_type' => 'business-month-static',
                    'unit_price' => 0.008
                ],
                [
                    'plan_id' => 'business-monthly-metered-bandwidth',
                    'plan_type' => 'business-month-static',
                    'unit_price' => 0.08
                ],
                //pro monthly
                [
                    'plan_id' => 'pro-monthly-metered-dynamic-watermark',
                    'plan_type' => 'pro-monthly-static',
                    'unit_price' => 0.004
                ],
                [
                    'plan_id' => 'pro-monthly-metered-forensic-watermark',
                    'plan_type' => 'pro-monthly-static',
                    'unit_price' => 0.006
                ],
                [
                    'plan_id' => 'pro-monthly-metered-enriched-contacts',
                    'plan_type' => 'pro-monthly-static',
                    'unit_price' => 0.05
                ],
                [
                    'plan_id' => 'pro-monthly-metered-translations',
                    'plan_type' => 'pro-monthly-static',
                    'unit_price' => 0.05
                ],
                [
                    'plan_id' => 'pro-monthly-metered-captions',
                    'plan_type' => 'pro-monthly-static',
                    'unit_price' => 0.05
                ],
                [
                    'plan_id' => 'pro-monthly-metered-anti-piracy',
                    'plan_type' => 'pro-monthly-static',
                    'unit_price' => 0.008
                ],
                [
                    'plan_id' => 'pro-monthly-metered-bandwidth',
                    'plan_type' => 'pro-monthly-static',
                    'unit_price' => 0.08
                ],
                //starter monthly
                [
                    'plan_id' => 'starter-monthly-metered-dynamic-watermark',
                    'plan_type' => 'starter-monthly-static',
                    'unit_price' => 0.006
                ],
                [
                    'plan_id' => 'starter-monthly-metered-forensic-watermark',
                    'plan_type' => 'starter-monthly-static',
                    'unit_price' => 0.008
                ],
                [
                    'plan_id' => 'starter-monthly-metered-enriched-contacts',
                    'plan_type' => 'starter-monthly-static',
                    'unit_price' => 0.008
                ],
                [
                    'plan_id' => 'starter-monthly-metered-translations',
                    'plan_type' => 'starter-monthly-static',
                    'unit_price' => 0.06
                ],
                [
                    'plan_id' => 'starter-monthly-metered-captions',
                    'plan_type' => 'starter-monthly-static',
                    'unit_price' => 0.06
                ],
                [
                    'plan_id' => 'starter-monthly-metered-anti-piracy',
                    'plan_type' => 'starter-monthly-static',
                    'unit_price' => 0.01
                ],
                [
                    'plan_id' => 'starter-monthly-metered-bandwidth',
                    'plan_type' => 'starter-monthly-static',
                    'unit_price' => 0.01
                ],
                [
                    'plan_id' => 'starter-monthly-metered-captions',
                    'plan_type' => 'starter-monthly-static',
                    'unit_price' => 0.06
                ],
                //free plan
                [
                    'plan_id' => 'free-metered-dynamic-watermark',
                    'plan_type' => 'free',
                    'unit_price' => 0
                ],
                [
                    'plan_id' => 'free-metered-forensic-watermark',
                    'plan_type' => 'free',
                    'unit_price' => 0
                ],
                [
                    'plan_id' => 'free-metered-enriched-contacts',
                    'plan_type' => 'free',
                    'unit_price' => 0
                ],
                [
                    'plan_id' => 'free-metered-translations',
                    'plan_type' => 'free',
                    'unit_price' => 0
                ],
                [
                    'plan_id' => 'free-metered-captions',
                    'plan_type' => 'free',
                    'unit_price' => 0
                ],
                [
                    'plan_id' => 'free-metered-anti-piracy',
                    'plan_type' => 'free',
                    'unit_price' => 0
                ],
                [
                    'plan_id' => 'free-metered-bandwidth',
                    'plan_type' => 'free',
                    'unit_price' => 0
                ],
                [
                    'plan_id' => 'free-metered-captions',
                    'plan_type' => 'free',
                    'unit_price' => 0
                ],
        ];
        foreach($plan as $val){
            $q =  DB::table('metered_plan_info')->where($val)->first();
            if($q==null){
             DB::table('metered_plan_info')->insert($val);
            }
        }
    }
}
