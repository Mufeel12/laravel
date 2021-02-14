<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RestrictionController extends Controller
{
	/**
	 * Get details of stripe & lifetime plans
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function index(Request $request)
    {
        $user = auth()->user();
        $stripePlan = $user->currentPlan; // TODO: get full details
        $lifeTimePlan = $user->lifeTimePlan;

        $lifeTimeLimits = [
            'modules' => [],
            'sub_modules' => [],
            'content' => [],
        ];
        if ($lifeTimePlan) {
            $lifeTimePlan = $lifeTimePlan->plan;
            $lifeTimeLimits = $this->getLifeTimePlanLimits($lifeTimePlan);
        }

        return response()->json([
            'stripe'    => [
                'name' => $stripePlan->stripe_plan,
            ],
            'lifetime'  => $lifeTimeLimits,
            'videosCount' => $user->videos->count(),
            'storage_used' => $user->getUserSpaceUsage($user->id),
            'bandwidth_used' => $user->summary->bandwidth_usage,
        ]);
    }

    /**
	 * Get details of lifetime plan limits
	 *
	 * @param Request $request
	 * @return Array
	 */
    protected function getLifeTimePlanLimits($planData)
    {
        $plan   = $planData->name;
        $data   = [];

        $common = $this->commonRestrictions();
        $data['modules'] = $common['modules'];
        $data['sub_modules'] = $common['sub_modules'];

        switch ($plan) {
            case 'Adilo Lifetime Video Hosting (Personal)':
                $data['content'] = $this->personalLimits();
            break;

            case 'Adilo Lifetime Video Hosting (Marketer)':
                $data['content'] =  $this->marketerLimits();
            break;

            case 'Adilo Lifetime Video Hosting (Commercial)':
                $data['content'] = $this->commercialLimits();
            break;

            case 'Adilo ELITE Membership Upgrade':
                $data['content'] = $this->eliteLimits();
            break;
        }
        return $data;
    }

    /**
	 * Get common restrictions for lifetime plans
	 *
	 * @param Request $request
	 * @return Array
	 */
    protected function commonRestrictions()
    {
        return [
            'modules' => [
                'projectview.collaboration',
                'projectview.experimentation',
                'snaps',
                'rooms',
                'stage',
                'privacy',
                'auto-tagging',
            ],
            'sub_modules' => [
                'advanced_content_security',
                'subtitles',
                'pixel_retargeting',
                'scheduled_streaming',
            ]
        ];
    }

    /**
	 * Get restrictions for personal lifetime plan.
	 *
	 * @param Request $request
	 * @return Array
	 */
    protected function personalLimits()
    {
        return [
            'videos' => 20,
            'storage' => 10737418240,
            'bandwidth' => 107374182400,
            'projects' => 5,
            'playlists' => 5,
            'contacts' => 500,
            'video_chapters' => true,
            'project_analytics' => false,
            'popover_embed' => false,
            'email_embed' => false,
        ];
    }

    /**
	 * Get restrictions for marketer lifetime plan.
	 *
	 * @param Request $request
	 * @return Array
	 */
    protected function marketerLimits()
    {
        return [
            'videos' => 'unlimited',
            'storage' => 26843545600,
            'bandwidth' => 536870912000,
            'projects' => 25,
            'playlists' => 25,
            'contacts' => 2500,
            'video_chapters' => true,
            'project_analytics' => false,
            'popover_embed' => false,
            'email_embed' => false,
        ];
    }

    /**
	 * Get restrictions for commercial lifetime plan.
	 *
	 * @param Request $request
	 * @return Array
	 */
    protected function commercialLimits()
    {
        return [
            'videos' => 'unlimited',
            'storage' => 107374182400,
            'bandwidth' => 1099511627776,
            'projects' => 100,
            'playlists' => 100,
            'contacts' => 5000,
            'video_chapters' => true,
            'project_analytics' => false,
            'popover_embed' => false,
            'email_embed' => false,
        ];
    }

        /**
	 * Get restrictions for elite lifetime plan.
	 *
	 * @param Request $request
	 * @return Array
	 */
    protected function eliteLimits()
    {
        return [
            'videos' => 'unlimited',
            'storage' => 268435456000,
            'bandwidth' => 2199023255552,
            'projects' => 'unlimited',
            'playlists' => 'unlimited',
            'contacts' => 'unlimited',
            'video_chapters' => true,
            'project_analytics' => true,
            'popover_embed' => true,
            'email_embed' => true,
        ];
    }

}
