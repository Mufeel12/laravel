<?php

namespace App\Providers;

use App\User;
use Braintree_Gateway;
use Braintree_WebhookNotification;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Laravel\Spark\Http\Controllers\Settings\Billing\BraintreeWebhookController;
use Laravel\Spark\LocalInvoice as Invoice;
use Laravel\Spark\Providers\AppServiceProvider as ServiceProvider;
use Laravel\Spark\Spark;

class SparkServiceProvider extends ServiceProvider
{
    /**
     * Your application and company details.
     *
     * @var array
     */
    protected $details = [
        'vendor' => 'BigCommand LLC',
        'product' => 'MotionCTA',
        'street' => '108 West 13th Street,',
        'location' => 'Wilmington, DE',
        'phone' => '19801',
    ];

    /**
     * The address where customer support e-mails should be sent.
     *
     * @var string
     */
    protected $sendSupportEmailsTo = 'erwin@maypower.org';

    /**
     * All of the application developer e-mail addresses.
     *
     * @var array
     */
    protected $developers = [
        'erwin.flaming@gmail.com'
    ];

    /**
     * Indicates if the application will expose an API.
     *
     * @var bool
     */
    protected $usesApi = false;

    /**
     * Finish configuring Spark for the application.
     *
     * @return void
     */
    public function booted()
    {
        // Todo: use braintree
        Spark::useBraintree()->noCardUpFront()->trialDays(10);

        Spark::freeTeamPlan()
            ->features([
                [ 'name' => '5 Video Uploads',
                    'description' => false,
                    'isHighlighted' => false,
                    'isDescription' => false,
                ],
                [ 'name' => '100GB Bandwidth/Month',
                    'description' => 'Account paused after bandwidth is used up',
                    'isHighlighted' => false,
                    'isDescription' => true,
                    ],
                [ 'name' => 'Interaction and Call to Actions',
                    'description' => 'Add call to actions including lead capture forms, images, htmls, auto-redirects and links in your videos to make it more interactive.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Contacts',
                    'description' => '100 Max',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Stage',
                    'description' => "Share your brilliance and brand with the world on a video channel that's truly represents you.",
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Playlists',
                    'description' => false,
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Video Privacy',
                    'description' => 'Make your video private, or lock it with passwords.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Integrations',
                    'description' => 'Only Aweber, Mailchimp, GotoWebinar',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
            ])
            ->attributes([
                'color' => '#FFFFFF',
                'bandwidth_limit' => 100,
                'videos_limit' => 5,
                'contacts_limit' => 100,
                'overage_const_per_gb' => false,
                'antipiracy_limit'=>0,
                'dynamic_watermark_limit'=>0,
                'forensic_watermark_limit'=>0,
                'discount'=>0,
                'antipiracy_per_item' => 0,
                'bandwidth_per_item' => 0,
                'caption_per_item' => 0,
                'dynamic_watermark_per_item' => 0,
                'forensic_watermark_per_item' => 0,
                'contacts_enrichment_per_item' => 0,
                'translations_per_item' => 0
            ]);
            Spark::teamPlan('Adilo Lifetime Video Hosting (Personal)', 'personal-paykickstart-static')
            ->features([
                [ 'name' => '20 Video Uploads',
                    'description' => false,
                    'isHighlighted' => false,
                    'isDescription' => false,
                ],
                [ 'name' => '100GB Bandwidth',
                    'description' => 'Account paused after bandwidth is used up',
                    'isHighlighted' => false,
                    'isDescription' => true,
                    ],
                [ 'name' => 'Interaction and Call to Actions',
                    'description' => 'Add call to actions including lead capture forms, images, htmls, auto-redirects and links in your videos to make it more interactive.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Contacts',
                    'description' => '100 Max',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Stage',
                    'description' => "Share your brilliance and brand with the world on a video channel that's truly represents you.",
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Playlists',
                    'description' => false,
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Video Privacy',
                    'description' => 'Make your video private, or lock it with passwords.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Integrations',
                    'description' => 'Only Aweber, Mailchimp, GotoWebinar',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
            ])
            ->attributes([
                'color' => '#FFFFFF',
                'bandwidth_limit' => 100,
                'videos_limit' => 20,
                'contacts_limit' => 100,
                'overage_const_per_gb' => false,
                'antipiracy_limit'=>0,
                'dynamic_watermark_limit'=>0,
                'forensic_watermark_limit'=>0,
                'discount'=>0,
                'antipiracy_per_item' => 0,
                'bandwidth_per_item' => 0,
                'caption_per_item' => 0,
                'dynamic_watermark_per_item' => 0,
                'forensic_watermark_per_item' => 0,
                'contacts_enrichment_per_item' => 0,
                'translations_per_item' => 0
            ]);



            Spark::teamPlan('Adilo Lifetime Video Hosting (Commercial)', 'commercial-paykickstart-static')
            ->features([
                [ 'name' => 'Unlimited Video Uploads',
                    'description' => false,
                    'isHighlighted' => false,
                    'isDescription' => false,
                ],
                [ 'name' => '1TB Bandwidth/Month',
                    'description' => 'Account paused after bandwidth is used up',
                    'isHighlighted' => false,
                    'isDescription' => true,
                    ],
                [ 'name' => 'Interaction and Call to Actions',
                    'description' => 'Add call to actions including lead capture forms, images, htmls, auto-redirects and links in your videos to make it more interactive.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Contacts',
                    'description' => '100 Max',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Stage',
                    'description' => "Share your brilliance and brand with the world on a video channel that's truly represents you.",
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Playlists',
                    'description' => false,
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Video Privacy',
                    'description' => 'Make your video private, or lock it with passwords.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Integrations',
                    'description' => 'Only Aweber, Mailchimp, GotoWebinar',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
            ])
            ->attributes([
                'color' => '#FFFFFF',
                'bandwidth_limit' => 100,
                'videos_limit' => 'Unlimited',
                'contacts_limit' => 100,
                'overage_const_per_gb' => false,
                'antipiracy_limit'=>0,
                'dynamic_watermark_limit'=>0,
                'forensic_watermark_limit'=>0,
                'discount'=>0,
                'antipiracy_per_item' => 0,
                'bandwidth_per_item' => 0,
                'caption_per_item' => 0,
                'dynamic_watermark_per_item' => 0,
                'forensic_watermark_per_item' => 0,
                'contacts_enrichment_per_item' => 0,
                'translations_per_item' => 0
            ]);



            Spark::teamPlan('Adilo Lifetime Video Hosting (Marketer)', 'marketer-paykickstart-static')
            ->features([
                [ 'name' => 'Unlimited Video Uploads',
                    'description' => false,
                    'isHighlighted' => false,
                    'isDescription' => false,
                ],
                [ 'name' => '500GB Bandwidth',
                    'description' => 'Account paused after bandwidth is used up',
                    'isHighlighted' => false,
                    'isDescription' => true,
                    ],
                [ 'name' => 'Interaction and Call to Actions',
                    'description' => 'Add call to actions including lead capture forms, images, htmls, auto-redirects and links in your videos to make it more interactive.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Contacts',
                    'description' => '100 Max',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Stage',
                    'description' => "Share your brilliance and brand with the world on a video channel that's truly represents you.",
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Playlists',
                    'description' => false,
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Video Privacy',
                    'description' => 'Make your video private, or lock it with passwords.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Integrations',
                    'description' => 'Only Aweber, Mailchimp, GotoWebinar',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
            ])
            ->attributes([
                'color' => '#FFFFFF',
                'bandwidth_limit' => 100,
                'videos_limit' => 'Unlimited',
                'contacts_limit' => 100,
                'overage_const_per_gb' => false,
                'antipiracy_limit'=>0,
                'dynamic_watermark_limit'=>0,
                'forensic_watermark_limit'=>0,
                'discount'=>0,
                'antipiracy_per_item' => 0,
                'bandwidth_per_item' => 0,
                'caption_per_item' => 0,
                'dynamic_watermark_per_item' => 0,
                'forensic_watermark_per_item' => 0,
                'contacts_enrichment_per_item' => 0,
                'translations_per_item' => 0
            ]);



            Spark::teamPlan('Adilo ELITE Membership Upgrade', 'elite-paykickstart-static')
                ->features([
                    [ 'name' => 'Unlimited Video Uploads',
                        'description' => false,
                        'isHighlighted' => false,
                        'isDescription' => false,
                    ],
                    [ 'name' => '2TB Bandwidth',
                        'description' => 'Account paused after bandwidth is used up',
                        'isHighlighted' => false,
                        'isDescription' => true,
                        ],
                    [ 'name' => 'Interaction and Call to Actions',
                        'description' => 'Add call to actions including lead capture forms, images, htmls, auto-redirects and links in your videos to make it more interactive.',
                        'isHighlighted' => true,
                        'isDescription' => false
                    ],
                    [ 'name' => 'Contacts',
                        'description' => '100 Max',
                        'isHighlighted' => true,
                        'isDescription' => false
                    ],
                    [ 'name' => 'Stage',
                        'description' => "Share your brilliance and brand with the world on a video channel that's truly represents you.",
                        'isHighlighted' => true,
                        'isDescription' => false
                    ],
                    [ 'name' => 'Video Playlists',
                        'description' => false,
                        'isHighlighted' => false,
                        'isDescription' => false
                    ],
                    [ 'name' => 'Basic Video Privacy',
                        'description' => 'Make your video private, or lock it with passwords.',
                        'isHighlighted' => true,
                        'isDescription' => false
                    ],
                    [ 'name' => 'Basic Integrations',
                        'description' => 'Only Aweber, Mailchimp, GotoWebinar',
                        'isHighlighted' => true,
                        'isDescription' => false
                    ],
                ])
                ->attributes([
                    'color' => '#FFFFFF',
                    'bandwidth_limit' => 2000,
                    'videos_limit' => 'Unlimited',
                    'contacts_limit' => 100,
                    'overage_const_per_gb' => false,
                    'antipiracy_limit'=>0,
                    'dynamic_watermark_limit'=>0,
                    'forensic_watermark_limit'=>0,
                    'discount'=>0,
                    'antipiracy_per_item' => 0,
                    'bandwidth_per_item' => 0,
                    'caption_per_item' => 0,
                    'dynamic_watermark_per_item' => 0,
                    'forensic_watermark_per_item' => 0,
                    'contacts_enrichment_per_item' => 0,
                    'translations_per_item' => 0
                ]);






        Spark::teamPlan('Starter', 'starter-monthly-static')
            ->trialDays(config('services.subscription.trial_duration'))
            ->price(99)
            ->features([
                [ 'name' => '1 TB bandwidth',
                    'description' => 'Extra bandwidth is billed at $0.10 per GB',
                    'isHighlighted' => false,
                    'isDescription' => true
                ],
                [ 'name' => 'Unlimited Video Uploads',
                    'description' => 'You will be able to upload an unlimited number of videos',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Interaction and Call-to-Action',
                    'description' => 'Add call to actions including lead capture forms, images, htmls, auto-redirects and links in your videos to make it more interactive.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Video Storage',
                    'description' => 'You will have no storage limits',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => '5,000 Contacts', 'description' => '', 'isHighlighted' => true ],
                [ 'name' => '100 Contact Insight & Enrichment',
                    'description' => 'Connect with your audiences in ways you never imagines, discover personal details you can leverage to close deals including interest, watch history, work history and more.',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Experimentation',
                    'description' => 'Easily determine which video or thumbnail will get your more results with the power of advanced split testing',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Stage',
                    'description' => "Share your brilliance and brand with the world on a video channel that's truly represents you.",
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Advanced Intergation',
                    'description' => 'All',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Thumbnails',
                    'description' => '',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Playlists',
                    'description' => '',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Video Privacy',
                    'description' => 'Make your video private, or lock it with passwords.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '100 Minutes Video Captions',
                    'description' => 'Increase your video engagement and conversions by 26%, engage up to 91% of your viewers and reach 85% of viewers that watch video without sound using adilo captions.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '100 Minutes Caption Translation',
                    'description' => 'Reach even larger audience than you imagined before. Quickly subtitle your video in other languages (Spanish, French, Mandarin, Korean, Russian, Portuguese, Italian etc)',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
            ])
            ->attributes([
                'color' => '#A51084',
                'bandwidth_limit' => 1024,
                'overage_const_per_gb' => 0.12,
                'videos_limit' => false,
                'contacts_limit' => 10000,
                'contacts_enrichment_limit' => 500,
                'contacts_enrichment_overage' => 0.02,
                'video_captions_limit' => 100,
                'discount'=>20,
                'video_captions_overage_per_min' => 0.06,
                'translation_captions_limit' => 100,
                'translation_captions_overage_per_min' => 0.06,

                'antipiracy_limit'=>0,
                'dynamic_watermark_limit'=>0,
                'forensic_watermark_limit'=>0,
                
                'antipiracy_per_item' => 0.01,
                'bandwidth_per_item' => 0.01,
                'caption_per_item' => 0.06,
                'dynamic_watermark_per_item' => 0.006,
                'forensic_watermark_per_item' => 0.008,
                'contacts_enrichment_per_item' => 0.008,
                'translations_per_item' => 0.06
            ]);

        Spark::teamPlan('Starter', 'starter-annual-static')
            ->trialDays(config('services.subscription.trial_duration'))
            ->price(79)
            ->attributes([
                'color' => '#A51084',
                'bandwidth_limit' => 1024,
                'overage_const_per_gb' => 0.12,
                'videos_limit' => false,
                'contacts_limit' => 10000,
                'contacts_enrichment_limit' => 500,
                'contacts_enrichment_overage' => 0.02,
                'video_captions_limit' => 100,
                'video_captions_overage_per_min' => 0.06,
                'translation_captions_limit' => 100,
                'translation_captions_overage_per_min' => 0.06,
                'discount'=>20,
                'antipiracy_limit'=>0,
                'dynamic_watermark_limit'=>0,
                'forensic_watermark_limit'=>0,

                'antipiracy_per_item' => 0.01,
                'bandwidth_per_item' => 0.01,
                'caption_per_item' => 0.06,
                'dynamic_watermark_per_item' => 0.006,
                'contacts_enrichment_per_item' => 0.05,
                'forensic_watermark_per_item' => 0.008,
                'translations_per_item' => 0.06
            ])
            ->yearly();

        Spark::teamPlan('Pro', 'pro-monthly-static')
            ->trialDays(config('services.subscription.trial_duration'))
            ->price(299)
            ->features([
                [ 'name' => '3 TB monthly bandwidth',
                    'description' => 'Extra bandwidth is billed at $0.08 per GB',
                    'isHighlighted' => false,
                    'isDescription' => true
                ],
                [ 'name' => 'Unlimited Video Uploads',
                    'description' => 'You will be able to upload an unlimited number of videos',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Interaction and Call-to-Action',
                    'description' => 'Add call to actions including lead capture forms, images, htmls, auto-redirects and links in your videos to make it more interactive.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Video Storage',
                    'description' => 'You will have no storage limits',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Contacts',
                    'description' => 'You will be able to have an unlimited number of contacts.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '250 Contact Insight & Enrichment',
                    'description' => 'Connect with your audiences in ways you never imagines, discover personal details you can leverage to close deals including interest, watch history, work history and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Contact Auto Tagging',
                    'description' => 'Automatically tag your contacts based on the behaviours and watch history',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '3 Account Users',
                    'description' => 'Share your Adilo account with your team, remote works, contractors and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Project Collaboration',
                    'description' => 'Bring your team and work on exciting changes together, collaboration is key to growth in every business.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Experimentation',
                    'description' => 'Easily determine which video or thumbnail will get your more results with the power of advanced split testing',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Stage',
                    'description' => "Share your brilliance and brand with the world on a video channel that's truly represents you.",
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Advanced Intergation',
                    'description' => 'All',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Thumbnails',
                    'description' => '',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Playlists',
                    'description' => '',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Video Privacy',
                    'description' => 'Make your video private, or lock it with passwords.',
                    'isHighlighted' => true,
                    'isDescription' => false

                ],
                [ 'name' => '2,000 licenses DRM Security',
                    'description' => 'Advanced Hollywood grade video security protects your premium/paid content against piracy, illegal sharing, copying & downloading (even with browser add-on/download managers).',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'IP and Regional Video Blocking',
                    'description' => 'Take full control of your video assets and determine where your videos can be watched. Block IP, IP ranges or whole cities and regions from streaming your videos.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Chapters',
                    'description' => 'Add chapters in your videos (especially long videos like webinars, interviews etc.) and make it easy for your viewers to jump to the most important parts of your video.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '300 Minutes Video Captions',
                    'description' => 'Increase your video engagement and conversions by 26%, engage up to 91% of your viewers and reach 85% of viewers that watch video without sound using adilo captions.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '300 Minutes Caption Translation',
                    'description' => 'Reach even larger audience than you imagined before. Quickly subtitle your video in other languages (Spanish, French, Mandarin, Korean, Russian, Portuguese, Italian etc)',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Caption Localization',
                    'description' => 'Automatically display the subtitle that matches your viewer\'s location (Example: automatically displaying Spanish captions for a viewer watching from Spain and then English captions for a viewer watching from the USA).',
                    'isHighlighted' => true
                ],
                [ 'name' => 'Migration Assistant',
                    'description' => 'Get personalised assistance, tips, training and all the help you need to moving from your previous host to Adilo.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],

            ])
            ->attributes([
                'color' => '#0AD688',
                'bandwidth_limit' => 3072,
                'overage_const_per_gb' => 0.10,
                'videos_limit' => false,
                'contacts_limit' => false,
                'sub_users_limit' => 1,
                'sub_users_overage' => 49,
                'contacts_enrichment_limit' => 1000,
                'contacts_enrichment_overage' => 0.02,
                'licenses_limit' => 2000,
                'licenses_overage' => 0.008,
                'video_captions_limit' => 300,
                'video_captions_overage_per_min' => 0.05,
                'translation_captions_limit' => 300,
                'translation_captions_overage_per_min' => 0.05,
                'discount'=>100,
               
                'antipiracy_limit'=>2000,
                'dynamic_watermark_limit'=>2000,
                'forensic_watermark_limit'=>5000,

                'antipiracy_per_item' => 0.008,
                'bandwidth_per_item' => 0.08,
                'caption_per_item' => 0.05,
                'dynamic_watermark_per_item' => 0.004,
                'contacts_enrichment_per_item' => 0.05,
                'forensic_watermark_per_item' => 0.006,
                'translations_per_item' => 0.05
            ]);

        Spark::teamPlan('Pro', 'pro-annual-static')
            ->trialDays(config('services.subscription.trial_duration'))
            ->price(239)
            ->attributes([
                'color' => '#0AD688',
                'bandwidth_limit' => 3072,
                'overage_const_per_gb' => 0.10,
                'videos_limit' => false,
                'contacts_limit' => false,
                'sub_users_limit' => 1,
                'sub_users_overage' => 49,
                'contacts_enrichment_limit' => 250,
                'contacts_enrichment_overage' => 0.02,
                'licenses_limit' => 2000,
                'licenses_overage' => 0.008,
                'video_captions_limit' => 300,
                'video_captions_overage_per_min' => 0.05,
                'translation_captions_limit' => 300,
                'translation_captions_overage_per_min' => 0.05,
                'bandwidth_unit_charge' => 0.08,

                'antipiracy_limit'=>2000,
                'dynamic_watermark_limit'=>2000,
                'forensic_watermark_limit'=>5000,
                'discount'=>100,

                'antipiracy_per_item' => 0.08,
                'bandwidth_per_item' => 0.08,
                'caption_per_item' => 0.05,
                'dynamic_watermark_per_item' => 0.04,
                'contacts_enrichment_per_item' => 0.05,
                'forensic_watermark_per_item' => 0.06,
                'translations_per_item' => 0.05
            ])
            ->yearly();

        Spark::teamPlan('Business', 'business-monthly-static')
            ->trialDays(config('services.subscription.trial_duration'))
            ->price(799)
            ->features([
                [ 'name' => '10 TB monthly bandwidth',
                    'description' => 'Extra bandwidth is billed at $0.08 per GB',
                    'isHighlighted' => false,
                    'isDescription' => true
                ],
                [ 'name' => 'Unlimited Video Uploads',
                    'description' => 'You will be able to upload an unlimited number of videos',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Interaction and Call-to-Action',
                    'description' => 'Add call to actions including lead capture forms, images, htmls, auto-redirects and links in your videos to make it more interactive.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Video Storage',
                    'description' => 'You will have no storage limits',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Contacts',
                    'description' => 'You will be able to have an unlimited number of contacts.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '800 Contact Insight & Enrichment',
                    'description' => 'Connect with your audiences in ways you never imagines, discover personal details you can leverage to close deals including interest, watch history, work history and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Contact Auto Tagging',
                    'description' => 'Automatically tag your contacts based on the behaviours and watch history',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '5 Account Users',
                    'description' => 'Share your Adilo account with your team, remote works, contractors and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Project Collaboration',
                    'description' => 'Bring your team and work on exciting changes together, collaboration is key to growth in every business.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Experimentation',
                    'description' => 'Easily determine which video or thumbnail will get your more results with the power of advanced split testing',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Stage',
                    'description' => "Share your brilliance and brand with the world on a video channel that's truly represents you.",
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Advanced Intergation',
                    'description' => 'All',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Thumbnails',
                    'description' => '',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Playlists',
                    'description' => '',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Video Privacy',
                    'description' => 'Make your video private, or lock it with passwords.',
                    'isHighlighted' => true,
                    'isDescription' => false

                ],
                [ 'name' => '5,000 licenses DRM Security',
                    'description' => 'Advanced Hollywood grade video security protects your premium/paid content against piracy, illegal sharing, copying & downloading (even with browser add-on/download managers).',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'IP and Regional Video Blocking',
                    'description' => 'Take full control of your video assets and determine where your videos can be watched. Block IP, IP ranges or whole cities and regions from streaming your videos.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Chapters',
                    'description' => 'Add chapters in your videos (especially long videos like webinars, interviews etc.) and make it easy for your viewers to jump to the most important parts of your video.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '1,000 Minutes Video Captions',
                    'description' => 'Increase your video engagement and conversions by 26%, engage up to 91% of your viewers and reach 85% of viewers that watch video without sound using adilo captions.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '1,000 Minutes Caption Translation',
                    'description' => 'Reach even larger audience than you imagined before. Quickly subtitle your video in other languages (Spanish, French, Mandarin, Korean, Russian, Portuguese, Italian etc)',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Caption Localization',
                    'description' => 'Automatically display the subtitle that matches your viewer\'s location (Example: automatically displaying Spanish captions for a viewer watching from Spain and then English captions for a viewer watching from the USA).',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Migration Assistant',
                    'description' => 'Get personalised assistance, tips, training and all the help you need to moving from your previous host to Adilo.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Concierge Service',
                    'description' => 'Let our expert team offer you a hands-off migration service, we\'ll take care of moving your entire library from your previous hosts to adilo and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Dedicated Account Manager',
                    'description' => 'This is 10X support... you get a dedicated manager that will personally resolve all issues you have with our team, this account manager serves as a personal liason between you and Adilo.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],

            ])
            ->attributes([
                'color' => '#00ACDC',
                'bandwidth_limit' => 10240,
                'overage_const_per_gb' => 0.08,
                'videos_limit' => false,
                'contacts_limit' => false,
                'sub_users_limit' => 3,
                'sub_users_overage' => 69,
                'contacts_enrichment_limit' => 3000,
                'contacts_enrichment_overage' => 0.02,
                'licenses_limit' => 10000,
                'discount'=>100,

                'licenses_overage' => 0.008,
                'video_captions_limit' => 1000,
                'video_captions_overage_per_min' => 0.04,
                'translation_captions_limit' => 1000,
                'translation_captions_overage_per_min' => 0.04,

                'antipiracy_limit'=>5000,
                'dynamic_watermark_limit'=>10000,
                'forensic_watermark_limit'=>5000,

                'antipiracy_per_item' => 0.008,
                'bandwidth_per_item' => 0.08,
                'caption_per_item' => 0.04,
                'dynamic_watermark_per_item' => 0.02,
                'contacts_enrichment_per_item' => 0.05,
                'forensic_watermark_per_item' => 0.04,
                'translations_per_item' => 0.04
            ]);
                
        Spark::teamPlan('Business', 'business-annual-static')
            ->trialDays(config('services.subscription.trial_duration'))
            ->price(639)
            ->attributes([
                'color' => '#00ACDC',
                'bandwidth_limit' => 8192,
                'overage_const_per_gb' => 0.08,
                'videos_limit' => false,
                'contacts_limit' => false,
                'sub_users_limit' => 3,
                'sub_users_overage' => 69,
                'contacts_enrichment_limit' => 3000,
                'contacts_enrichment_overage' => 0.02,
                'licenses_limit' => 10000,
                'licenses_overage' => 0.008,
                'video_captions_limit' => 1000,
                'video_captions_overage_per_min' => 0.04,
                'translation_captions_limit' => 1000,
                'translation_captions_overage_per_min' => 0.04,
                'discount'=>100,

                'antipiracy_limit'=>5000,
                'dynamic_watermark_limit'=>10000,
                'forensic_watermark_limit'=>5000,

                'antipiracy_per_item' => 0.08,
                'bandwidth_per_item' => 0.08,
                'caption_per_item' => 0.04,
                'dynamic_watermark_per_item' => 0.002,
                'contacts_enrichment_per_item' => 0.05,
                'forensic_watermark_per_item' => 0.004,
                'translations_per_item' => 0.04
            ])
            ->yearly();

        Spark::teamPlan('Business (Quarterly Promo Bundle)', 'business-quarterly-bundle-static')
            ->trialDays(config('services.subscription.trial_duration'))
            ->price(2097)
            ->features([
                [ 'name' => '10 TB monthly bandwidth',
                    'description' => 'Extra bandwidth is billed at $0.08 per GB',
                    'isHighlighted' => false,
                    'isDescription' => true
                ],
                [ 'name' => 'Unlimited Video Uploads',
                    'description' => 'You will be able to upload an unlimited number of videos',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Interaction and Call-to-Action',
                    'description' => 'Add call to actions including lead capture forms, images, htmls, auto-redirects and links in your videos to make it more interactive.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Video Storage',
                    'description' => 'You will have no storage limits',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Contacts',
                    'description' => 'You will be able to have an unlimited number of contacts.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '800 Contact Insight & Enrichment',
                    'description' => 'Connect with your audiences in ways you never imagines, discover personal details you can leverage to close deals including interest, watch history, work history and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Contact Auto Tagging',
                    'description' => 'Automatically tag your contacts based on the behaviours and watch history',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '5 Account Users',
                    'description' => 'Share your Adilo account with your team, remote works, contractors and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Project Collaboration',
                    'description' => 'Bring your team and work on exciting changes together, collaboration is key to growth in every business.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Experimentation',
                    'description' => 'Easily determine which video or thumbnail will get your more results with the power of advanced split testing',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Stage',
                    'description' => "Share your brilliance and brand with the world on a video channel that's truly represents you.",
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Advanced Intergation',
                    'description' => 'All',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Thumbnails',
                    'description' => '',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Playlists',
                    'description' => '',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Video Privacy',
                    'description' => 'Make your video private, or lock it with passwords.',
                    'isHighlighted' => true,
                    'isDescription' => false

                ],
                [ 'name' => '5,000 licenses DRM Security',
                    'description' => 'Advanced Hollywood grade video security protects your premium/paid content against piracy, illegal sharing, copying & downloading (even with browser add-on/download managers).',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'IP and Regional Video Blocking',
                    'description' => 'Take full control of your video assets and determine where your videos can be watched. Block IP, IP ranges or whole cities and regions from streaming your videos.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Chapters',
                    'description' => 'Add chapters in your videos (especially long videos like webinars, interviews etc.) and make it easy for your viewers to jump to the most important parts of your video.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '1,000 Minutes Video Captions',
                    'description' => 'Increase your video engagement and conversions by 26%, engage up to 91% of your viewers and reach 85% of viewers that watch video without sound using adilo captions.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '1,000 Minutes Caption Translation',
                    'description' => 'Reach even larger audience than you imagined before. Quickly subtitle your video in other languages (Spanish, French, Mandarin, Korean, Russian, Portuguese, Italian etc)',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Caption Localization',
                    'description' => 'Automatically display the subtitle that matches your viewer\'s location (Example: automatically displaying Spanish captions for a viewer watching from Spain and then English captions for a viewer watching from the USA).',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Migration Assistant',
                    'description' => 'Get personalised assistance, tips, training and all the help you need to moving from your previous host to Adilo.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Concierge Service',
                    'description' => 'Let our expert team offer you a hands-off migration service, we\'ll take care of moving your entire library from your previous hosts to adilo and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Dedicated Account Manager',
                    'description' => 'This is 10X support... you get a dedicated manager that will personally resolve all issues you have with our team, this account manager serves as a personal liason between you and Adilo.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],

            ])
            ->attributes([
                'color' => '#00ACDC',
                'bandwidth_limit' => 8192,
                'overage_const_per_gb' => 0.08,
                'videos_limit' => false,
                'contacts_limit' => false,
                'sub_users_limit' => 3,
                'sub_users_overage' => 69,
                'contacts_enrichment_limit' => 3000,
                'contacts_enrichment_overage' => 0.02,
                'licenses_limit' => 10000,
                'licenses_overage' => 0.008,
                'video_captions_limit' => 1000,
                'video_captions_overage_per_min' => 0.04,
                'translation_captions_limit' => 1000,
                'translation_captions_overage_per_min' => 0.04,
                'discount'=>100,

                'antipiracy_limit'=>5000,
                'dynamic_watermark_limit'=>10000,
                'forensic_watermark_limit'=>5000,

                'antipiracy_per_item' => 0.08,
                'bandwidth_per_item' => 0.08,
                'caption_per_item' => 0.04,
                'dynamic_watermark_per_item' => 0.002,
                'contacts_enrichment_per_item' => 0.05,
                'forensic_watermark_per_item' => 0.004,
                'translations_per_item' => 0.04
            ]);
        Spark::teamPlan('Business (Semi-Annual Promo Bundle)', 'business-semi-bundle-static')
            ->trialDays(config('services.subscription.trial_duration'))
            ->price(3594)
            ->features([
                [ 'name' => '10 TB monthly bandwidth',
                    'description' => 'Extra bandwidth is billed at $0.08 per GB',
                    'isHighlighted' => false,
                    'isDescription' => true
                ],
                [ 'name' => 'Unlimited Video Uploads',
                    'description' => 'You will be able to upload an unlimited number of videos',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Interaction and Call-to-Action',
                    'description' => 'Add call to actions including lead capture forms, images, htmls, auto-redirects and links in your videos to make it more interactive.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Video Storage',
                    'description' => 'You will have no storage limits',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Contacts',
                    'description' => 'You will be able to have an unlimited number of contacts.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '800 Contact Insight & Enrichment',
                    'description' => 'Connect with your audiences in ways you never imagines, discover personal details you can leverage to close deals including interest, watch history, work history and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Contact Auto Tagging',
                    'description' => 'Automatically tag your contacts based on the behaviours and watch history',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '5 Account Users',
                    'description' => 'Share your Adilo account with your team, remote works, contractors and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Project Collaboration',
                    'description' => 'Bring your team and work on exciting changes together, collaboration is key to growth in every business.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Experimentation',
                    'description' => 'Easily determine which video or thumbnail will get your more results with the power of advanced split testing',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Stage',
                    'description' => "Share your brilliance and brand with the world on a video channel that's truly represents you.",
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Advanced Intergation',
                    'description' => 'All',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Thumbnails',
                    'description' => '',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Playlists',
                    'description' => '',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Video Privacy',
                    'description' => 'Make your video private, or lock it with passwords.',
                    'isHighlighted' => true,
                    'isDescription' => false

                ],
                [ 'name' => '5,000 licenses DRM Security',
                    'description' => 'Advanced Hollywood grade video security protects your premium/paid content against piracy, illegal sharing, copying & downloading (even with browser add-on/download managers).',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'IP and Regional Video Blocking',
                    'description' => 'Take full control of your video assets and determine where your videos can be watched. Block IP, IP ranges or whole cities and regions from streaming your videos.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Chapters',
                    'description' => 'Add chapters in your videos (especially long videos like webinars, interviews etc.) and make it easy for your viewers to jump to the most important parts of your video.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '1,000 Minutes Video Captions',
                    'description' => 'Increase your video engagement and conversions by 26%, engage up to 91% of your viewers and reach 85% of viewers that watch video without sound using adilo captions.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '1,000 Minutes Caption Translation',
                    'description' => 'Reach even larger audience than you imagined before. Quickly subtitle your video in other languages (Spanish, French, Mandarin, Korean, Russian, Portuguese, Italian etc)',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Caption Localization',
                    'description' => 'Automatically display the subtitle that matches your viewer\'s location (Example: automatically displaying Spanish captions for a viewer watching from Spain and then English captions for a viewer watching from the USA).',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Migration Assistant',
                    'description' => 'Get personalised assistance, tips, training and all the help you need to moving from your previous host to Adilo.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Concierge Service',
                    'description' => 'Let our expert team offer you a hands-off migration service, we\'ll take care of moving your entire library from your previous hosts to adilo and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Dedicated Account Manager',
                    'description' => 'This is 10X support... you get a dedicated manager that will personally resolve all issues you have with our team, this account manager serves as a personal liason between you and Adilo.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],

            ])
            ->attributes([
                'color' => '#00ACDC',
                'bandwidth_limit' => 8192,
                'overage_const_per_gb' => 0.08,
                'videos_limit' => false,
                'contacts_limit' => false,
                'sub_users_limit' => 3,
                'sub_users_overage' => 69,
                'contacts_enrichment_limit' => 3000,
                'contacts_enrichment_overage' => 0.02,
                'licenses_limit' => 10000,
                'licenses_overage' => 0.008,
                'video_captions_limit' => 1000,
                'video_captions_overage_per_min' => 0.04,
                'translation_captions_limit' => 1000,
                'translation_captions_overage_per_min' => 0.04,
                'discount'=>100,

                'antipiracy_limit'=>5000,
                'dynamic_watermark_limit'=>10000,
                'forensic_watermark_limit'=>5000,

                'antipiracy_per_item' => 0.08,
                'bandwidth_per_item' => 0.08,
                'caption_per_item' => 0.04,
                'dynamic_watermark_per_item' => 0.002,
                'contacts_enrichment_per_item' => 0.05,
                'forensic_watermark_per_item' => 0.004,
                'translations_per_item' => 0.04
            ]);
        Spark::teamPlan('Business Plan (Annual Promo Bundle)', 'business-annual-bundle-static')
            ->trialDays(config('services.subscription.trial_duration'))
            ->price(7188)
            ->features([
                [ 'name' => '10 TB monthly bandwidth',
                    'description' => 'Extra bandwidth is billed at $0.08 per GB',
                    'isHighlighted' => false,
                    'isDescription' => true
                ],
                [ 'name' => 'Unlimited Video Uploads',
                    'description' => 'You will be able to upload an unlimited number of videos',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Interaction and Call-to-Action',
                    'description' => 'Add call to actions including lead capture forms, images, htmls, auto-redirects and links in your videos to make it more interactive.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Video Storage',
                    'description' => 'You will have no storage limits',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Contacts',
                    'description' => 'You will be able to have an unlimited number of contacts.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '800 Contact Insight & Enrichment',
                    'description' => 'Connect with your audiences in ways you never imagines, discover personal details you can leverage to close deals including interest, watch history, work history and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Contact Auto Tagging',
                    'description' => 'Automatically tag your contacts based on the behaviours and watch history',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '5 Account Users',
                    'description' => 'Share your Adilo account with your team, remote works, contractors and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Project Collaboration',
                    'description' => 'Bring your team and work on exciting changes together, collaboration is key to growth in every business.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Experimentation',
                    'description' => 'Easily determine which video or thumbnail will get your more results with the power of advanced split testing',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Stage',
                    'description' => "Share your brilliance and brand with the world on a video channel that's truly represents you.",
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Advanced Intergation',
                    'description' => 'All',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Thumbnails',
                    'description' => '',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Playlists',
                    'description' => '',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Video Privacy',
                    'description' => 'Make your video private, or lock it with passwords.',
                    'isHighlighted' => true,
                    'isDescription' => false

                ],
                [ 'name' => '5,000 licenses DRM Security',
                    'description' => 'Advanced Hollywood grade video security protects your premium/paid content against piracy, illegal sharing, copying & downloading (even with browser add-on/download managers).',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'IP and Regional Video Blocking',
                    'description' => 'Take full control of your video assets and determine where your videos can be watched. Block IP, IP ranges or whole cities and regions from streaming your videos.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Chapters',
                    'description' => 'Add chapters in your videos (especially long videos like webinars, interviews etc.) and make it easy for your viewers to jump to the most important parts of your video.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '1,000 Minutes Video Captions',
                    'description' => 'Increase your video engagement and conversions by 26%, engage up to 91% of your viewers and reach 85% of viewers that watch video without sound using adilo captions.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '1,000 Minutes Caption Translation',
                    'description' => 'Reach even larger audience than you imagined before. Quickly subtitle your video in other languages (Spanish, French, Mandarin, Korean, Russian, Portuguese, Italian etc)',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Caption Localization',
                    'description' => 'Automatically display the subtitle that matches your viewer\'s location (Example: automatically displaying Spanish captions for a viewer watching from Spain and then English captions for a viewer watching from the USA).',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Migration Assistant',
                    'description' => 'Get personalised assistance, tips, training and all the help you need to moving from your previous host to Adilo.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Concierge Service',
                    'description' => 'Let our expert team offer you a hands-off migration service, we\'ll take care of moving your entire library from your previous hosts to adilo and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Dedicated Account Manager',
                    'description' => 'This is 10X support... you get a dedicated manager that will personally resolve all issues you have with our team, this account manager serves as a personal liason between you and Adilo.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],

            ])
            ->attributes([
                'color' => '#00ACDC',
                'bandwidth_limit' => 8192,
                'overage_const_per_gb' => 0.08,
                'videos_limit' => false,
                'contacts_limit' => false,
                'sub_users_limit' => 3,
                'sub_users_overage' => 69,
                'contacts_enrichment_limit' => 3000,
                'contacts_enrichment_overage' => 0.02,
                'licenses_limit' => 10000,
                'licenses_overage' => 0.008,
                'video_captions_limit' => 1000,
                'video_captions_overage_per_min' => 0.04,
                'translation_captions_limit' => 1000,
                'translation_captions_overage_per_min' => 0.04,
                'discount'=>100,

                'antipiracy_limit'=>5000,
                'dynamic_watermark_limit'=>10000,
                'forensic_watermark_limit'=>5000,

                'antipiracy_per_item' => 0.08,
                'bandwidth_per_item' => 0.08,
                'caption_per_item' => 0.04,
                'dynamic_watermark_per_item' => 0.002,
                'contacts_enrichment_per_item' => 0.05,
                'forensic_watermark_per_item' => 0.004,
                'translations_per_item' => 0.04
            ]) ;

        Spark::teamPlan('Pro (Quarterly Promo Bundle)', 'pro-quarterly-bundle-static')
            ->trialDays(config('services.subscription.trial_duration'))
            ->price(747)
            ->features([
                [ 'name' => '3 TB monthly bandwidth',
                    'description' => 'Extra bandwidth is billed at $0.08 per GB',
                    'isHighlighted' => false,
                    'isDescription' => true
                ],
                [ 'name' => 'Unlimited Video Uploads',
                    'description' => 'You will be able to upload an unlimited number of videos',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Interaction and Call-to-Action',
                    'description' => 'Add call to actions including lead capture forms, images, htmls, auto-redirects and links in your videos to make it more interactive.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Video Storage',
                    'description' => 'You will have no storage limits',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Contacts',
                    'description' => 'You will be able to have an unlimited number of contacts.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '250 Contact Insight & Enrichment',
                    'description' => 'Connect with your audiences in ways you never imagines, discover personal details you can leverage to close deals including interest, watch history, work history and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Contact Auto Tagging',
                    'description' => 'Automatically tag your contacts based on the behaviours and watch history',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '3 Account Users',
                    'description' => 'Share your Adilo account with your team, remote works, contractors and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Project Collaboration',
                    'description' => 'Bring your team and work on exciting changes together, collaboration is key to growth in every business.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Experimentation',
                    'description' => 'Easily determine which video or thumbnail will get your more results with the power of advanced split testing',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Stage',
                    'description' => "Share your brilliance and brand with the world on a video channel that's truly represents you.",
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Advanced Intergation',
                    'description' => 'All',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Thumbnails',
                    'description' => '',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Playlists',
                    'description' => '',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Video Privacy',
                    'description' => 'Make your video private, or lock it with passwords.',
                    'isHighlighted' => true,
                    'isDescription' => false

                ],
                [ 'name' => '2,000 licenses DRM Security',
                    'description' => 'Advanced Hollywood grade video security protects your premium/paid content against piracy, illegal sharing, copying & downloading (even with browser add-on/download managers).',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'IP and Regional Video Blocking',
                    'description' => 'Take full control of your video assets and determine where your videos can be watched. Block IP, IP ranges or whole cities and regions from streaming your videos.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Chapters',
                    'description' => 'Add chapters in your videos (especially long videos like webinars, interviews etc.) and make it easy for your viewers to jump to the most important parts of your video.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '300 Minutes Video Captions',
                    'description' => 'Increase your video engagement and conversions by 26%, engage up to 91% of your viewers and reach 85% of viewers that watch video without sound using adilo captions.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '300 Minutes Caption Translation',
                    'description' => 'Reach even larger audience than you imagined before. Quickly subtitle your video in other languages (Spanish, French, Mandarin, Korean, Russian, Portuguese, Italian etc)',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Caption Localization',
                    'description' => 'Automatically display the subtitle that matches your viewer\'s location (Example: automatically displaying Spanish captions for a viewer watching from Spain and then English captions for a viewer watching from the USA).',
                    'isHighlighted' => true
                ],
                [ 'name' => 'Migration Assistant',
                    'description' => 'Get personalised assistance, tips, training and all the help you need to moving from your previous host to Adilo.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],

            ])
            ->attributes([
                'color' => '#0AD688',
                'bandwidth_limit' => 3072,
                'overage_const_per_gb' => 0.10,
                'videos_limit' => false,
                'contacts_limit' => false,
                'sub_users_limit' => 1,
                'sub_users_overage' => 49,
                'contacts_enrichment_limit' => 250,
                'contacts_enrichment_overage' => 0.02,
                'licenses_limit' => 2000,
                'licenses_overage' => 0.008,
                'video_captions_limit' => 300,
                'video_captions_overage_per_min' => 0.05,
                'translation_captions_limit' => 300,
                'translation_captions_overage_per_min' => 0.05,
                'bandwidth_unit_charge' => 0.08,

                'antipiracy_limit'=>2000,
                'dynamic_watermark_limit'=>2000,
                'forensic_watermark_limit'=>5000,
                'discount'=>100,

                'antipiracy_per_item' => 0.08,
                'bandwidth_per_item' => 0.08,
                'caption_per_item' => 0.05,
                'dynamic_watermark_per_item' => 0.04,
                'contacts_enrichment_per_item' => 0.05,
                'forensic_watermark_per_item' => 0.06,
                'translations_per_item' => 0.05
            ]);    
        Spark::teamPlan('Pro (Semi-Annual Promo Bundle)', 'pro-semi-bundle-static')
            ->trialDays(config('services.subscription.trial_duration'))
            ->price(1194)
            ->features([
                [ 'name' => '3 TB monthly bandwidth',
                    'description' => 'Extra bandwidth is billed at $0.08 per GB',
                    'isHighlighted' => false,
                    'isDescription' => true
                ],
                [ 'name' => 'Unlimited Video Uploads',
                    'description' => 'You will be able to upload an unlimited number of videos',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Interaction and Call-to-Action',
                    'description' => 'Add call to actions including lead capture forms, images, htmls, auto-redirects and links in your videos to make it more interactive.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Video Storage',
                    'description' => 'You will have no storage limits',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Contacts',
                    'description' => 'You will be able to have an unlimited number of contacts.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '250 Contact Insight & Enrichment',
                    'description' => 'Connect with your audiences in ways you never imagines, discover personal details you can leverage to close deals including interest, watch history, work history and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Contact Auto Tagging',
                    'description' => 'Automatically tag your contacts based on the behaviours and watch history',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '3 Account Users',
                    'description' => 'Share your Adilo account with your team, remote works, contractors and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Project Collaboration',
                    'description' => 'Bring your team and work on exciting changes together, collaboration is key to growth in every business.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Experimentation',
                    'description' => 'Easily determine which video or thumbnail will get your more results with the power of advanced split testing',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Stage',
                    'description' => "Share your brilliance and brand with the world on a video channel that's truly represents you.",
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Advanced Intergation',
                    'description' => 'All',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Thumbnails',
                    'description' => '',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Playlists',
                    'description' => '',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Video Privacy',
                    'description' => 'Make your video private, or lock it with passwords.',
                    'isHighlighted' => true,
                    'isDescription' => false

                ],
                [ 'name' => '2,000 licenses DRM Security',
                    'description' => 'Advanced Hollywood grade video security protects your premium/paid content against piracy, illegal sharing, copying & downloading (even with browser add-on/download managers).',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'IP and Regional Video Blocking',
                    'description' => 'Take full control of your video assets and determine where your videos can be watched. Block IP, IP ranges or whole cities and regions from streaming your videos.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Chapters',
                    'description' => 'Add chapters in your videos (especially long videos like webinars, interviews etc.) and make it easy for your viewers to jump to the most important parts of your video.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '300 Minutes Video Captions',
                    'description' => 'Increase your video engagement and conversions by 26%, engage up to 91% of your viewers and reach 85% of viewers that watch video without sound using adilo captions.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '300 Minutes Caption Translation',
                    'description' => 'Reach even larger audience than you imagined before. Quickly subtitle your video in other languages (Spanish, French, Mandarin, Korean, Russian, Portuguese, Italian etc)',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Caption Localization',
                    'description' => 'Automatically display the subtitle that matches your viewer\'s location (Example: automatically displaying Spanish captions for a viewer watching from Spain and then English captions for a viewer watching from the USA).',
                    'isHighlighted' => true
                ],
                [ 'name' => 'Migration Assistant',
                    'description' => 'Get personalised assistance, tips, training and all the help you need to moving from your previous host to Adilo.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],

            ])
            ->attributes([
                'color' => '#0AD688',
                'bandwidth_limit' => 3072,
                'overage_const_per_gb' => 0.10,
                'videos_limit' => false,
                'contacts_limit' => false,
                'sub_users_limit' => 1,
                'sub_users_overage' => 49,
                'contacts_enrichment_limit' => 250,
                'contacts_enrichment_overage' => 0.02,
                'licenses_limit' => 2000,
                'licenses_overage' => 0.008,
                'video_captions_limit' => 300,
                'video_captions_overage_per_min' => 0.05,
                'translation_captions_limit' => 300,
                'translation_captions_overage_per_min' => 0.05,
                'bandwidth_unit_charge' => 0.08,

                'antipiracy_limit'=>2000,
                'dynamic_watermark_limit'=>2000,
                'forensic_watermark_limit'=>5000,
                'discount'=>100,

                'antipiracy_per_item' => 0.08,
                'bandwidth_per_item' => 0.08,
                'caption_per_item' => 0.05,
                'dynamic_watermark_per_item' => 0.04,
                'contacts_enrichment_per_item' => 0.05,
                'forensic_watermark_per_item' => 0.06,
                'translations_per_item' => 0.05
            ]); 
        Spark::teamPlan('Pro (Annual Promo Bundle)', 'pro-annual-bundle-static')
            ->trialDays(config('services.subscription.trial_duration'))
            ->price(2388)
            ->features([
                [ 'name' => '3 TB monthly bandwidth',
                    'description' => 'Extra bandwidth is billed at $0.08 per GB',
                    'isHighlighted' => false,
                    'isDescription' => true
                ],
                [ 'name' => 'Unlimited Video Uploads',
                    'description' => 'You will be able to upload an unlimited number of videos',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Interaction and Call-to-Action',
                    'description' => 'Add call to actions including lead capture forms, images, htmls, auto-redirects and links in your videos to make it more interactive.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Video Storage',
                    'description' => 'You will have no storage limits',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Unlimited Contacts',
                    'description' => 'You will be able to have an unlimited number of contacts.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '250 Contact Insight & Enrichment',
                    'description' => 'Connect with your audiences in ways you never imagines, discover personal details you can leverage to close deals including interest, watch history, work history and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Contact Auto Tagging',
                    'description' => 'Automatically tag your contacts based on the behaviours and watch history',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '3 Account Users',
                    'description' => 'Share your Adilo account with your team, remote works, contractors and more.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Project Collaboration',
                    'description' => 'Bring your team and work on exciting changes together, collaboration is key to growth in every business.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Experimentation',
                    'description' => 'Easily determine which video or thumbnail will get your more results with the power of advanced split testing',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Stage',
                    'description' => "Share your brilliance and brand with the world on a video channel that's truly represents you.",
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Advanced Intergation',
                    'description' => 'All',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Thumbnails',
                    'description' => '',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Playlists',
                    'description' => '',
                    'isHighlighted' => false,
                    'isDescription' => false
                ],
                [ 'name' => 'Basic Video Privacy',
                    'description' => 'Make your video private, or lock it with passwords.',
                    'isHighlighted' => true,
                    'isDescription' => false

                ],
                [ 'name' => '2,000 licenses DRM Security',
                    'description' => 'Advanced Hollywood grade video security protects your premium/paid content against piracy, illegal sharing, copying & downloading (even with browser add-on/download managers).',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'IP and Regional Video Blocking',
                    'description' => 'Take full control of your video assets and determine where your videos can be watched. Block IP, IP ranges or whole cities and regions from streaming your videos.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Video Chapters',
                    'description' => 'Add chapters in your videos (especially long videos like webinars, interviews etc.) and make it easy for your viewers to jump to the most important parts of your video.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '300 Minutes Video Captions',
                    'description' => 'Increase your video engagement and conversions by 26%, engage up to 91% of your viewers and reach 85% of viewers that watch video without sound using adilo captions.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => '300 Minutes Caption Translation',
                    'description' => 'Reach even larger audience than you imagined before. Quickly subtitle your video in other languages (Spanish, French, Mandarin, Korean, Russian, Portuguese, Italian etc)',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],
                [ 'name' => 'Caption Localization',
                    'description' => 'Automatically display the subtitle that matches your viewer\'s location (Example: automatically displaying Spanish captions for a viewer watching from Spain and then English captions for a viewer watching from the USA).',
                    'isHighlighted' => true
                ],
                [ 'name' => 'Migration Assistant',
                    'description' => 'Get personalised assistance, tips, training and all the help you need to moving from your previous host to Adilo.',
                    'isHighlighted' => true,
                    'isDescription' => false
                ],

            ])
            ->attributes([
                'color' => '#0AD688',
                'bandwidth_limit' => 3072,
                'overage_const_per_gb' => 0.10,
                'videos_limit' => false,
                'contacts_limit' => false,
                'sub_users_limit' => 1,
                'sub_users_overage' => 49,
                'contacts_enrichment_limit' => 250,
                'contacts_enrichment_overage' => 0.02,
                'licenses_limit' => 2000,
                'licenses_overage' => 0.008,
                'video_captions_limit' => 300,
                'video_captions_overage_per_min' => 0.05,
                'translation_captions_limit' => 300,
                'translation_captions_overage_per_min' => 0.05,
                'bandwidth_unit_charge' => 0.08,

                'antipiracy_limit'=>2000,
                'dynamic_watermark_limit'=>2000,
                'forensic_watermark_limit'=>5000,
                'discount'=>100,

                'antipiracy_per_item' => 0.08,
                'bandwidth_per_item' => 0.08,
                'caption_per_item' => 0.05,
                'dynamic_watermark_per_item' => 0.04,
                'contacts_enrichment_per_item' => 0.05,
                'forensic_watermark_per_item' => 0.06,
                'translations_per_item' => 0.05
            ])->yearly();       

        
        
        
        
            /*Spark::useRoles([
            'viewer' => 'Viewer',
            'editor' => 'Editor',
        ]);*/
    }

    public function register()
    {
        Spark::identifyTeamsByPath();
    }

    public static function getClientTokenForPayPal($user, $payPalEmail)
    {
        $gateway = new Braintree_Gateway(config('services.braintree'));

        $userName = isset($user->name) ? explode(' ', $user->name) : [];

        $customer = $gateway->customer()->create([
            'firstName' => isset($userName[0]) ? $userName[0] : null,
            'lastName' => isset($userName[1]) ? $userName[1] : null,
            'email' => $user->email,
        ]);

        $userData = \App\User::where('id', $user->id)->first();
        $userData->paypal_email = $payPalEmail;
        $userData->braintree_id = $customer->customer->id;
        $userData->save();


        return $gateway->clientToken()->generate([
            "customerId" => $customer->customer->id,
        ]);
    }

    public static function getTrial($user, $pm, $subscriptionId)
    {
        $gateway = new Braintree_Gateway(config('services.braintree'));
        $status = \App\Status::where('name', 'active')->first();
        $plan = Spark::teamPlans()->where('id', $subscriptionId)->first();

        $customer = $gateway->customer()->find($user->braintree_id);
        $paymentMethod = $gateway->paymentMethod()->create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $pm,
        ]);

        $new_sub = $gateway->subscription()->create([
            'paymentMethodToken' => $paymentMethod->paymentMethod->token,
            'planId' => $plan->id,
            'trialPeriod' => true,
            'trialDurationUnit' => 'day',
            'trialDuration' => config('services.subscription.trial_duration'),
        ]);

        $user->billing_status = 'Trial';
        $user->payment_method = 'paypal';
        $user->trial_ends_at = now()->addDays(config('services.subscription.trial_duration'));
        $user->status_id = $status->id;
        $user->save();

        $subscription = \App\Subscription::create();
        $subscription->stripe_plan = $plan->id;
        $subscription->name = $plan->name;
        $subscription->stripe_id =  $new_sub->subscription->id;
        $subscription->user_id = $user->id;
        $subscription->trial_ends_at = now()->addDays(config('services.subscription.trial_duration'));
        $subscription->ends_at = now()->addMonths(1);
        $subscription->save();

        $userInfo = User::getUserDetails($user);

        return response()->json([
            'result'   => 'success',
            'userInfo' => $userInfo,
        ], 200);
    }
}
