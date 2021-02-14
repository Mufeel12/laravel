<?php

use Illuminate\Database\Seeder;

class CtaElementsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cta_elements = [
            [ // row #0
                'video_id' => 40,
                'cta_element_type' => 'lead_capture',
                'cta_element_value' => 'a:7:{s:10:"fullscreen";s:4:"true";s:13:"ask_for_names";s:5:"false";s:11:"allow_close";s:4:"true";s:4:"text";s:72:"Get soundtrack for free, just leave your email to get the download link.";s:11:"button_text";s:8:"Download";s:12:"button_color";s:7:"#ff0001";s:14:"email_provider";s:0:"";}',
                'start_time' => 0,
                'end_time' => 81,
                'created_at' => '2015-11-03 10:46:49',
                'updated_at' => '2015-11-03 10:47:17',
            ],
            [ // row #1
                'video_id' => 40,
                'cta_element_type' => 'social-share',
                'cta_element_value' => 'a:7:{s:8:"facebook";s:4:"true";s:11:"google-plus";s:4:"true";s:9:"instagram";s:5:"false";s:9:"pinterest";s:4:"true";s:6:"reddit";s:5:"false";s:6:"tumblr";s:4:"true";s:7:"twitter";s:4:"true";}',
                'start_time' => 2,
                'end_time' => 22,
                'created_at' => '2015-11-03 15:34:27',
                'updated_at' => '2015-11-03 15:38:38',
            ],
            [ // row #2
                'video_id' => 40,
                'cta_element_type' => 'annotation',
                'cta_element_value' => 'a:7:{s:10:"fullscreen";s:5:"false";s:11:"allow_close";s:4:"true";s:4:"text";s:96:"We owe a special thanks to the people at Borrowlenses, Dynamic Perception and Mountain Hardwear.";s:16:"background_color";s:7:"#ebe716";s:10:"text_color";s:7:"#3dab43";s:3:"url";s:18:"http://mintsapp.io";s:7:"new_tab";s:4:"true";}',
                'start_time' => 10,
                'end_time' => 10,
                'created_at' => '2015-11-03 15:35:27',
                'updated_at' => '2015-11-03 15:38:38',
            ],
            [ // row #3
                'video_id' => 40,
                'cta_element_type' => 'amazon-listing',
                'cta_element_value' => 'a:6:{s:3:"url";s:141:"http://www.amazon.com/Squatty-Potty%C2%AE-Toilet-Stool-Original/dp/B008G9B11E/ref=sr_1_1?ie=UTF8&qid=1446019816&sr=8-1&keywords=squatty+potty";s:10:"show_price";s:4:"true";s:15:"show_reputation";s:4:"true";s:12:"button_color";s:7:"#FE4239";s:11:"allow_close";s:4:"true";s:7:"product";s:292:"[{"image_url":"","rating":"4.3","price":"$24.99","title":"Squatty Potty® Toilet Stool, 7 Inch- The Original - Made in U.S.A.","url":"http://www.amazon.com/dp/B008G9B11E","description":"Made in the USA! The Squatty Potty is a wonderful health aid for the entire family. The Squatty Pott..."}]";}',
                'start_time' => 25,
                'end_time' => 151,
                'created_at' => '2015-11-03 15:37:15',
                'updated_at' => '2015-11-03 15:38:38',
            ],
            [ // row #4
                'video_id' => 40,
                'cta_element_type' => 'button',
                'cta_element_value' => 'a:4:{s:4:"text";s:14:"Visit our site";s:16:"background_color";s:7:"#ff0001";s:3:"url";s:1:"#";s:7:"new_tab";s:4:"true";}',
                'start_time' => 0,
                'end_time' => 88,
                'created_at' => '2015-11-03 15:38:22',
                'updated_at' => '2015-11-03 15:38:38',
            ],
            [ // row #5
                'video_id' => 40,
                'cta_element_type' => 'lead_capture',
                'cta_element_value' => 'O:8:"stdClass":7:{s:10:"fullscreen";s:5:"false";s:13:"ask_for_names";s:5:"false";s:11:"allow_close";s:4:"true";s:4:"text";s:72:"Get soundtrack for free, just leave your email to get the download link.";s:11:"button_text";s:8:"Download";s:12:"button_color";s:7:"#ff0001";s:14:"email_provider";s:0:"";}',
                'start_time' => 0,
                'end_time' => 10,
                'created_at' => '2015-11-10 18:32:32',
                'updated_at' => '2015-11-10 18:32:32',
            ],
            [ // row #6
                'video_id' => 40,
                'cta_element_type' => 'lead_capture',
                'cta_element_value' => 'O:8:"stdClass":7:{s:10:"fullscreen";s:5:"false";s:13:"ask_for_names";s:5:"false";s:11:"allow_close";s:4:"true";s:4:"text";s:72:"Get soundtrack for free, just leave your email to get the download link.";s:11:"button_text";s:8:"Download";s:12:"button_color";s:7:"#ff0001";s:14:"email_provider";s:0:"";}',
                'start_time' => 0,
                'end_time' => 10,
                'created_at' => '2015-11-11 15:50:28',
                'updated_at' => '2015-11-11 15:50:28',
            ],
        ];

        \Illuminate\Database\Eloquent\Model::unguard();

        foreach ($cta_elements as $cta_element) {
            \App\CtaElement::create($cta_element);
        }

        \Illuminate\Database\Eloquent\Model::reguard();
    }
}
