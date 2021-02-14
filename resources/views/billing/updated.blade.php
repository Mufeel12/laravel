
<!DOCTYPE html>
<html lang="en">
<body style="background-color: #F9FBFB; font-family: sans-serif">
<table width="100%">
    <tr>
        <td width="100%" align="center">

            <table width="540px" border="0" cellspacing="0" cellpadding="50"
                   style="background-color: #FFFFFF; border-radius: 12px; margin-top: 50px">
                <tr>
                    <td>

                        <img src="{{ asset('/img/logo_w_text.png') }}" alt="BigCommand"/>

                        <h3 style="font-weight: 500; color: #21455E; font-size: 20px">Hello {{$full_name}}</h3>


                        <table width="100%"
                               style="font-size: 16px; color: #21455E; font-weight: normal; border-spacing: 0px">

                            <tr>
                                <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;">
                                    An admin has just updated your Adilo account.
                                </td>

                            </tr>
                            <tr>
                                <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;">The updated data is your {{$name}}.</td>
                                @if($old) <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;">Old: {{$old}}</td> @endif
                                <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;">Current: {{$new}}</td>

                            </tr>
                            <tr>
                                <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;">
                                    You're advised to log into your account, change your password and confirm that everything is in other.                                </td>

                            </tr>
                            <tr>
                                <td style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px #E8E8EA solid;">
                                    Note: If you did not request this profile update, please contact our support team immediately<a
                                            href="https://help.bigcommand.com">https://help.bigcommand.com</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <h4 style="font-weight: normal; font-size: 18px; color: #21455E;">Thank you for using Adilo!</h4>
            <h5 style="font-weight: normal; font-size: 15px; color: #21455E;">Regards,</h5>
            <h5 style="font-weight: normal; font-size: 15px; color: #21455E;">Bigcommand LLC</h5>
            <p style="text-align: center; font-size: 14px; color: #788D9D; margin-top: 5px; margin-bottom: 0px;">108
                West 13th Street,</p>
            <p style="text-align: center; font-size: 14px; color: #788D9D; margin-top: 5px; margin-bottom: 0px;">
                Wilmington, DE</p>
            <p style="text-align: center; font-size: 14px; color: #788D9D; margin-top: 5px; margin-bottom: 0px;">
                19801</p>

        </td>
    </tr>
</table>
</body>
</html>