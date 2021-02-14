<!DOCTYPE html>
<html lang="en">
<body style="background-color: #F9FBFB; font-family: sans-serif">
    <table width="100%">
        <tr>
            <td width="100%" align="center">

                <table width="540px" border="0" cellspacing="0" cellpadding="50" style="background-color: #FFFFFF; border-radius: 12px; margin-top: 50px">
                    <tr>
                        <td>
                            <img src="/img/logo_w_text.png" alt="BigCommand" />

                            <p style="font-size: 14px; color: #21455E">Dear {{$company['vendor']}} Customer,</p>
                            <br/>
                            <p style="font-size: 14px; color: #21455E; line-height: 25px; margin-top: 0px;">Your BigCommand has been suspended for non-payment. Our previous attempts to renew your account failed.</p>
                            <br/>
                            <p style="font-size: 14px; color: #21455E; line-height: 25px; margin-top: 0px;">Please log in to your account and pay the outstanding charges to reinstate your account.</p>
                            <div style="width: 100%; text-align: center">
                              <a href="{{$base_url}}" style="background-color: #0DABD8; display: inline-block; text-decoration: none; font-size: 16px; height: 36px; border-radius: 18px; color: #FFFFFF; padding-left: 20px; padding-right: 20px; line-height: 36px">Reinstate my account</a>
                            </div>

                            <p style="font-size: 14px; color: #21455E; margin-top: 30px;">Thank you for being a {{$company['vendor']}} customer.<br/>
                            Sincerely,<br/>{{$company['vendor']}}</p>

                        </td>
                    </tr>
                </table>
                <p style="text-align: center; font-size: 14px; color: #788D9D; margin-bottom: 0px;">&#169; {{now()->year}} {{$company['vendor']}}, All Rights Reserved.</p>
                <p style="text-align: center; font-size: 14px; color: #788D9D; margin-top: 5px; margin-bottom: 0px;">{{$company['street']}}, {{$company['location']}}</p>
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