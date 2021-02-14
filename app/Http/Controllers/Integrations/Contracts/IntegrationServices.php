<?php


namespace App\Http\Controllers\Integrations\Contracts;


use App\Subscriber;
use Illuminate\Http\Request;

interface IntegrationServices
{
    public function connect(Request $request);

    public function lists();

    public function subscribe(Subscriber $subscriber, $lists = [], $tags = null);
}
