<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MailersTest extends TestCase
{
    protected $user;

    /**
     * @group project
     */
    public function login()
    {
        Auth::loginUsingId(1);
        $this->user = Auth::user();
    }

    public function testSubscribing()
    {
        $ctaElementId = 162;
        $name = 'Erwin Flaming';
        $email = 'erwin@maypower.org';
        $ctaElement = \App\CtaElement::find($ctaElementId);
        $userId = 1;

        $autoresponder = \App\CtaElement::getLeadCaptureProvider($ctaElement);

        $return = \App\LeadCapture::captureSubscriber($userId, $autoresponder, $email, $name, $ctaElementId);

        // Increase statistics for this lead
        \App\Statistic::storeAction('cta_element', $ctaElement->id, 'lead_capture');
    }

    /**
     * @group mailers
     */
    public function testGetAutoresponder()
    {
        $this->login();
        $response = $this->get('/autoresponders/get');
        $data = $response->json();
        $this->assertTrue($data['connected'][0] == 'mailchimp');
    }
}
