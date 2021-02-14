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

                                <h3 style="font-weight: 500; color: #21455E; font-size: 20px">Hello {{$full_name}}</h3>
                            <h4 style="font-weight: normal; font-size: 18px; color: #21455E;">Your Adilo subscription plan has been changed from {{ $current_plan_name }} to {{ $new_plan_name }}.</h4>

                            <table width="100%" style="font-size: 16px; color: #21455E; font-weight: normal; border-spacing: 0px">
                                <tr>
                                    <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;">
                                        Your prorated fee today is: </td>
                                    <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;"><strong>${{$fee_due_today}}</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;">
                                        Your subscription will rebill on {{ $next_rebill_date }} at full subscription of ${{ $new_plan_subscription_fee }} pending any overages.
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;">
                                        If you didn't make this change, contact support now <a href="https://help.bigcommand.com">https://help.bigcommand.com</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <h4 style="font-weight: normal; font-size: 18px; color: #21455E;">Thank you for using Adilo!</h4>
                <h5 style="font-weight: normal; font-size: 15px; color: #21455E;">Regards,</h5>
                <h5 style="font-weight: normal; font-size: 15px; color: #21455E;">Bigcommand LLC</h5>
                <p style="text-align: center; font-size: 14px; color: #788D9D; margin-top: 5px; margin-bottom: 0px;">108 West 13th Street,</p>
                <p style="text-align: center; font-size: 14px; color: #788D9D; margin-top: 5px; margin-bottom: 0px;">Wilmington, DE</p>
                <p style="text-align: center; font-size: 14px; color: #788D9D; margin-top: 5px; margin-bottom: 0px;">19801</p>
                <p style="margin-top: 5px;">
                    <a style="color: #21455E; font-size: 12px; text-decoration: none" href="{{$base_url}}/help">Help</a>
                    &bull;
                    <a style="color: #21455E; font-size: 12px; text-decoration: none" href="{{$base_url}}/privacy">Privacy</a>
                    &bull;
                    <a style="color: #21455E; font-size: 12px; text-decoration: none" href="{{$base_url}}/Terms">Term</a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>