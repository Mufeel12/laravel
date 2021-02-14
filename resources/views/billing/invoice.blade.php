<!DOCTYPE html>
<html lang="en">
<body style="background-color: #F9FBFB; font-family: sans-serif">
    <table width="100%">
        <tr>
            <td width="100%" align="center">

                <table width="540px" border="0" cellspacing="0" cellpadding="50" style="background-color: #FFFFFF; border-radius: 12px; margin-top: 50px">
                    <tr>
                        <td>

                            <img src="{{ asset('/img/logo_w_text.png') }}" alt="BigCommand" />
                            @if ($title)
                                <h3 style="font-weight: 500; color: #21455E; font-size: 20px">{{$title}}</h3>
                            @endif
                            <h4 style="font-weight: normal; font-size: 18px; color: #21455E;">Receipt {{$invoice['receipt_id']}}</h4>

                            <table width="100%" style="font-size: 16px; color: #21455E; font-weight: normal; border-spacing: 0px">
                                <tr>
                                    <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;">Adilo {{$plan->name}} {{$plan->interval}} Subscription</td><td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;"><strong>${{$plan->price}}.00</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;">Adilo Overage Charge</td><td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;"><strong>${{$overage_cost}}</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;">Discounts</td><td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;"><strong>${{isset($discount)?$discount:0}}</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;">
                                        @if ($user->payment_method === 'paypal')
                                            <table width="100%" style="font-size: 16px; color: #21455E; font-weight: normal; border-spacing: 0px">
                                                <tr>
                                                    <td><img src="{{$base_url}}/img/paypal_logo.png" alt="PayPal" /></td>
                                                    <td>
                                                        <strong>Paid</strong> via <strong>PayPal account</strong><br/>
                                                        {{$user->paypal_email}}
                                                    </td>
                                                </tr>
                                            </table>
                                        @else
                                            <table width="100%" style="font-size: 16px; color: #21455E; font-weight: normal; border-spacing: 0px">
                                                <tr>
                                                    <td><img src="{{$base_url}}/img/credit-card_logo.png" alt="Credit card" /></td>
                                                    <td>
                                                        <strong>Paid</strong> via <strong>Credit card</strong><br/>
                                                        &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; {{$user->card_last_four}}
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif
                                    </td>
                                    <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;"><strong>${{$total}}</strong></td>
                                </tr>
                            </table>

                            <table width="100%" style="font-size: 16px; color: #21455E; font-weight: normal; border-spacing: 0px; margin-top: 20px;">
                                <tr>
                                    <td width="50%" valign="top">
                                        <h4 style="font-size: 20px; font-weight: 600; color: #21455E">Issued To</h4>
                                        <p style="font-size: 16px; color: #21455E; font-weight: 600">{{$user->full_name}}</p>
                                        <p style="font-size: 16px; color: #21455E; margin-bottom: 0px;">{{$user->phone}}</p>
                                        <p style="font-size: 16px; color: #21455E; margin: 0px">{{$user->email}}</p>
                                        <p style="font-size: 16px; color: #21455E; margin-bottom: 0px;">{{$user->billing_address}}</p>
                                        <p style="font-size: 16px; color: #21455E; margin: 0px;">{{$user->billing_city}}, {{$user->billing_state}} {{$user->billing_zip}}</p>
                                        <p style="font-size: 16px; color: #21455E; margin: 0px;">{{$user->billing_country}}</p>
                                    </td>
                                    <td width="50%" valign="top">
                                        <h4 style="font-size: 20px; font-weight: 600; color: #21455E">Issued By</h4>
                                        <p style="font-size: 16px; color: #21455E; font-weight: 600">{{$company['vendor']}}</p>
                                        <p style="font-size: 16px; color: #21455E; margin-bottom: 0px;">{{$company['street']}}</p>
                                        <p style="font-size: 16px; color: #21455E; margin: 0px">{{$company['location']}}</p>
                                        {{--<p style="font-size: 16px; color: #21455E; margin: 0px;"><a style="text-decoration: none; color: #00ACDC" href="{{$base_url}}">{{$site_name}}</a></p>--}}
                                        <p style="font-size: 16px; color: #21455E; margin: 0px;">{{$company['phone']}}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <p style="text-align: center; font-size: 14px; color: #788D9D; margin-bottom: 0px;">&#169; {{now()->year}} {{$company['vendor']}}, All Rights Reserved.</p>
                <p style="margin-top: 5px;">
                    <a style="color: #21455E; font-size: 12px; text-decoration: none" href="https://help.bigcommand.com/">Help</a>
                    &bull;
                    <a style="color: #21455E; font-size: 12px; text-decoration: none" href="https://bigcommand.com/privacy">Privacy</a>
                    &bull;
                    <a style="color: #21455E; font-size: 12px; text-decoration: none" href="https://bigcommand.com/terms">Term</a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>