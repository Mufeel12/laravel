<?php

use IlluminAppte\Database\Seeder;

class SlateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $slateTemplates = [
            [
                'title' => 'Blue Ocean',
                'description' => 'Note for blue ocean template',
                'template' => 'first_slate_template',
                'fields' => [
                    'background' => '#4097D6',
                    'title' => 'Blue Ocean',
                    'button_text' => 'VIEW GUIDE',
                    'button_url' => '#',
                    'button_background' => '#E59C00',
                ]
            ],
            [
                'title' => 'Second slate template',
                'description' => 'Note for second slate template',
                'template' => 'second_slate_template',
                'fields' =>
                    array(
                        'title' => 'Open Space',
                        'subtitle' => 'Many other special traits. This compound does display. Water is indeed unique. I\'m glad it\'s here to stay.',
                        'header_button_text' => 'DOWNLOAD NOW',
                        'header_button_url' => '#',
                        'header_background_color' => '#203248',
                        'footer_title' => 'Download the PDF',
                        'footer_subtitle' => 'Do you want to download the PDF?*',
                        'footer_fine_print' => '*Here for you to change is some footer text.',
                        'footer_button_text' => 'DOWNLOAD',
                        'footer_button_url' => '#',
                        'footer_button_background' => '#203248',
                        'footer_background' => '#292929',
                    )
            ],
            [
                'title' => 'Third slate template',
                'description' => 'Note for third slate template',
                'template' => 'third_slate_template',
                'fields' => [
                    'title' => "Utah's first National Park",
                    'subtitle' => 'Are you ready to go hiking this summer? Well, The Watchman trail is ready for you.',
                    'header_image' => url('img/branding/zion-national.jpg'),
                    'content_headline' => 'Plan your hike in Zion National Park',
                    'content_image' => url('img/branding/zion-2.jpg'),
                    'content_text' => 'Our Zion National Park Travel and Information Guide contains great content for hotels, lodging restaurants, activities, weather, shopping, maps, and much more.',
                    'content_button_text' => 'VIEW GUIDE',
                    'content_button_url' => '#',
                    'content_button_background' => '#203248',
                    'footer_title' => 'Zion National Park',
                    'footer_subtitle' => '(C) Copyright 2016 - CTA Monkey',
                    'footer_background' => '#292929',
                ]
            ],
            [
                'title' => 'Travel slate template',
                'description' => 'Note for travel slate template',
                'template' => 'travel_slate_template',
                'fields' => [
                    'background' => '#333333',
                    'title' => "7 days in a hotel for 944$ including breakfast & flights",
                    'subtitle' => '"The whole experience of staying in their hotel was beyond perfection. 
I actually found myself unable to describe the wonderful experience by words... 
Beautiful resort, luxurious yet feel-so-much-like-home rooms, the friendly staff, 
the tasty food and the great facilities & activities.. What more can you ask for?" - Janette Machow',
                    'button_text' => 'Book Now',
                    'button_url' => '#',
                    'button_background' => '#e46b40',
                ]
            ],
            /*[
                'title' => 'Video background slate template',
                'description' => 'Full video background template',
                'template' => 'video_background_slate_template',
                'fields' => [
                    'title' => "YOUTUBE DEMONSTRATION",
                    'subtitle' => 'Set your video and it will play as a background.
If you are done, click the button below to get more information about our offer.',
                    'button_text' => 'Get More Information',
                    'button_url' => '#',
                    'button_background' => '#e46b40',
                ]
            ]
            */[
                'title' => 'Early Bird',
                'description' => 'Note for early bird template',
                'template' => 'early_bird_slate_template',
                'fields' => [
                    'logo' => 'http://motioncta.io/img/slates/logo.png',
                    'title' => 'Sign-up today and be the first who 
get\'s notified when we launch!',
                    'subtitle' => 'Stay informed with just one click, and learn more about our product as we get ready to launch it globally.',
                    'countdown_day_text' => 'Wednesday',
                    'countdown_month' => 'December',
                    'countdown_days' => '21-22',
                    'countdown_time' => 'at 1pm Pacific, 4pm Eastern',
                    'email_label' => 'Enter your email',
                    'email_button_text' => 'Let me know please',
                    'email_provider' => '',
                    'email_redirect' => '#',
                    'footer_title' => '(C) Copyright 2017 - Motion CTA',
                ]
            ],
            [
                'title' => 'Easy Download',
                'description' => 'Note for easy download template',
                'template' => 'easy_download_slate_template',
                'fields' => [
                    'logo' => 'http://motioncta.io/img/slates/logo.png',
                    'title' => '"Do You Know The One Critical Strategy Behind The Most Successful Product Launches?"',
                    'countdown_title' => 'FREE TRAINING ENDING IN...',
                    'countdown_date' => '',
                    'countdown_time' => '',
                    'button_text' => 'Instantly Download This Mindmap and System',
                    'button_url' => '#',
                    'terms_text' => '*This is a Free service and no credit card is required.',
                    'footer_title' => '(C) Copyright 2017 - Motion CTA',
                ]
            ],
            [
                'title' => 'Rewards',
                'description' => 'Note for easy download template',
                'template' => 'rewards_slate_template',
                'fields' => [
                    'logo' => 'http://motioncta.io/img/slates/logo.png',
                    'title' => 'Here is the bonus video we promised you',
                    'subtitle' => 'As promised, here\'s part 1/4 of our amazing course!',
                    'content_headline' => 'Interested to start scaling & expanding your business?',
                    'content_subheadline' => 'Start getting results by signing up to this free 4 part course that will guide you through the process of bringing in more customers with paid advertising.',
                    'button_text' => 'Download our E-book manual',
                    'button_url' => '#',
                    'footer_title' => '(C) Copyright 2017 - Motion CTA',
                ]
            ],
            [
                'title' => 'Showcase',
                'description' => 'Note for easy download template',
                'template' => 'showcase_slate_template',
                'fields' => [
                    'logo' => 'http://motioncta.io/img/slates/logo.png',
                    'title' => 'World\'s Most Powerful Drag & Drop Visual Site Builder',
                    'bullet_1' => 'Drag & Drop easy',
                    'bullet_2' => 'Create any type of website in minutes',
                    'bullet_3' => 'No messing with codes, we promise!',
                    'bullet_4' => 'Over 30 element blocks',
                    'bullet_5' => 'Look good on every device',


                    'box_1_image' => 'http://motioncta.io/img/slates/pic1.png',
                    'box_1_title' => 'Easy Drag & Drop',
                    'box_1_text' => 'Origin Builder makes it super easy to build your Wordpress sites and pages the way you want it. No creative limits or coding needed.',

                    'box_2_image' => 'http://motioncta.io/img/slates/pic2.png',
                    'box_2_title' => 'Look good on all devices',
                    'box_2_text' => 'Your sites and pages will look amazing on any device. Everything is responsive.',

                    'box_3_image' => 'http://motioncta.io/img/slates/pic3.png',
                    'box_3_title' => 'Easily replaces your current site builder',
                    'box_3_text' => 'With all the features packed into Origin Builder, it easily replaces any site builder you are using currently.',


                    'button_text' => 'Download It Now',
                    'button_url' => '#',

                    'footer_title' => '(C) Copyright 2017 - Motion CTA',
                ]
            ],
            [
                'title' => 'Thank you',
                'description' => 'Note for thank you template',
                'template' => 'thank_you_slate_template',
                'fields' => [
                    'logo' => 'http://motioncta.io/img/slates/logo_green.png',
                    'title' => 'World\'s Most Powerful Drag & Drop Visual Site Builder',
                    'subtitle' => 'The bonuses and gifts we prepared for you are now ready for you to download. Comment below and the download button will activate.',

                    'button_title' => 'Download my bonuses',
                    'button_url' => '#',

                    'image' => 'http://motioncta.io/img/slates/thank_you.png',

                    'comments_url' => 'https://developers.facebook.com/docs/plugins/comments#configurator',

                    'footer_title' => '(C) Copyright 2017 - Motion CTA',
                ]
            ],
            [
                'title' => 'The Tutor',
                'description' => 'Note for the tutor template',
                'template' => 'the_tutor_slate_template',
                'fields' => [
                    'logo' => 'http://motioncta.io/img/slates/logo.png',
                    'title' => 'The Last Lead Generation Tool Your Business Will Ever Need',

                    'button_title' => 'Click here to get access now',
                    'button_url' => '#',

                    'privacy_text' => 'I respect your privacy and have ZERO TOLERANCE for spam.',

                    'footer_title' => '(C) Copyright 2017 - Motion CTA',
                ]
            ],
            [
                'title' => 'The Tutor 2',
                'description' => 'Note for the tutor 2 template',
                'template' => 'the_tutor_2_slate_template',
                'fields' => [
                    'logo' => 'http://motioncta.io/img/slates/logo.png',
                    'title' => 'Thanks for sign up! Here is your FREE webinar',
                    'subtitle' => 'Everything we promised you is on its way to your inbox. Before you go, we recommend that you sign up for this Wednesdays webinar that will help your business grow.',

                    'button_title' => 'Watch free now',
                    'button_url' => '#',

                    'webinar_date' => 'Wednesday, August 17, 2016 - 2:00pm',
                    'webinar_title' => '5 ways to build your brand on social media',
                    'tutor_1_name' => 'SANDY BATLOUNGE',
                    'tutor_1_title' => 'Social Media instructor',
                    'tutor_1_image' => 'http://motioncta.io/img/slates/tutor2_man1.png',
                    'tutor_2_name' => 'MICHAEL ROBINSSON',
                    'tutor_2_title' => 'CEO AfterGreat',
                    'tutor_2_image' => 'http://motioncta.io/img/slates/tutor2_man2.png',

                    'tutor_text' => 'Social media has transformed the way brands can interact with customers, providing a platform to engage in new and exciting ways. It can be challenging to stand out in the crowd, but also extremely rewarding. This is the best webinar in its niche.',

                    'second_button_title' => 'CLICK HERE TO WATCH #1 VIDEO FOR FREE',
                    'second_button_url' => '#',

                    'footer_title' => '(C) Copyright 2017 - Motion CTA',
                ]
            ]
        ];

        foreach ($slateTemplates as $slateTemplate) {
            \App\SlateTemplate::create($slateTemplate);
        }
    }
}
