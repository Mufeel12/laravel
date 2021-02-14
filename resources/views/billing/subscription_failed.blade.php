<!DOCTYPE html>
<html lang="en">
<body style="background-color: #F9FBFB; font-family: sans-serif">
    <table width="100%">
        <tr>
            <td width="100%" align="center">

                <table width="540px" border="0" cellspacing="0" cellpadding="50" style="background-color: #FFFFFF; border-radius: 12px; margin-top: 50px">
                    <tr>
                        <td>
                            <img src="{{ asset('/img/welcome-mail/bigcommand.png') }}" alt="BigCommand" />

                            <p style="font-size: 14px; color: #21455E">Dear {{$company['vendor']}} Customer,</p>
                            <br/>
                            <p style="font-size: 14px; color: #21455E; line-height: 25px; margin-top: 0px;">Unfortunately, our recent attempt to renew your BigCommand subscription has failed. As of today, you have an outstanding balance of ${{$invoice->total}}.</p>
                            <br/>
                            <p style="font-size: 14px; color: #21455E; line-height: 25px; margin-top: 0px;">Please log in to your account and update your payment method. We will try to charge your payment method as soon as the settings change:</p>
                            <div style="width: 100%; text-align: center">
                              <a href="{{$base_url}}/settings/billing/information" style="background-color: #0DABD8; display: inline-block; text-decoration: none; font-size: 16px; height: 36px; border-radius: 18px; color: #FFFFFF; padding-left: 20px; padding-right: 20px; line-height: 36px">Update billing information</a>
                            </div>
                            <p style="font-size: 14px; color: #21455E; line-height: 25px; margin-top: 30px;">If you do not update your payment method, we will make a few more attempts with the current information in the coming days.</p>
                            <p style="font-size: 14px; color: #21455E; line-height: 25px; margin-top: 0px;">Unless we are successful in renewing your account <strong>by {{date('F, d, Y',strtotime($enddate))}}, your {{$company['vendor']}} account may be suspended or terminated.</strong></p>

                            <p style="font-size: 14px; color: #21455E">Thank you for being a {{$company['vendor']}} customer.<br/>
                            Sincerely,<br/>{{$company['vendor']}}</p>

                        </td>
                    </tr>
                </table>
                <p style="text-align: center; font-size: 14px; color: #788D9D; margin-bottom: 0px;">&#169; {{now()->year}} {{$company['vendor']}}, All Rights Reserved.</p>
                <p style="text-align: center; font-size: 14px; color: #788D9D; margin-top: 5px; margin-bottom: 0px;">{{$company['street']}}, {{$company['location']}}</p>

            </td>
        </tr>
    </table>
</body>
</html>