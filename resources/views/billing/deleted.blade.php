<!DOCTYPE html>
<html lang="en">
<body style="background-color: #F9FBFB; font-family: sans-serif">
<table width="100%">
    <tr>
        <td width="100%" align="center">

            <table width="540px" border="0" cellspacing="0" cellpadding=""
                   style="background-color: #FFFFFF; border-radius: 12px;  margin-top: 50px; padding: 20px 50px 15px 50px;">
                <tr>
                    <td>

                        <div style='display: flex; justify-content: space-between;'>
                            <div class="bigcommandLogo">
                                @include('include.logo')
                            </div>
                            <div class="adiloLogo" style='margin-top: 15px;'>
                                @include('include.logo-adilo')
                            </div>
                        </div>

                        <h3 style="font-weight: 500; color: #21455E; font-size: 20px">Hello {{$full_name}}</h3>

                            <p class='email-lines'>This to notify you that your Adilo account has been permanently closed.</p>
                            <p class='email-lines'>
                                Reason: {{$reason}}
                            </p>
                            <p class='email-lines'>If you think this was a mistake, please contact our support team right now for an appeal.
                            </p>

                            <div style='width: 100%; position: relative; margin: 50px 0; text-align: center;'>
                                <a href='https://help.bigcommand.com' class="sign-in-btn">
                                    Contact Support to Appeal
                                </a>
                            </div>

                            <p class='email-lines'>
                                NOTE: You have only 90 days from the date of receiving this email to regain access to your account and all your content.
                                <br>
                                After 90 days, your data will be permanently wiped and can not be restored again.
                            </p>                                
                    </td>
                </tr>
                 <tr>
                    <td>
                        <p style="font-weight: normal; color: #21455E;">Thank you for using Adilo!</p>
                        <h5 style="font-weight: normal; font-size: 15px; color: #21455E; line-height: 1.5;">
                            Sincerely,
                            <br>
                            Adilo Compliance Team
                        </h5>
                        <h5 style="font-weight: normal; font-size: 15px; color: #21455E;">
                            If you have questions or you need help, 
                            <a href="https://help.bigcommand.com">CONTACT SUPPORT</a>
                        </h5>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div class="footer" style='color: #21455E;'>
    <p style="text-align: center; font-size: 14px; color: #788D9D; margin-top: 15px; margin-bottom: 0px;">
        © 2020 BigCommand LLC, All Rights Reserved.
    </p>
    <p style="text-align: center; font-size: 14px; color: #788D9D; margin-top: 10px; margin-bottom: 0px;">
        108 West 13th Street Wilmington, DE 19801 United States
    </p>
    <p style="text-align: center; font-size: 14px; color: #788D9D; margin-top: 10px; margin-bottom: 0px;">
        <a href="https://help.bigcommand.com/" style='color: #21455E; text-decoration: none !important;'>Help</a>
        <span 
            style='position: relative;
                bottom: 2px;
                font-weight: 700;
                margin-right: 4px;
                margin-left: 4px;'>.
        </span>
        <a href="https://bigcommand.com/privacy" style='color: #21455E; text-decoration: none !important;'>Privacy</a>
        <span 
            style='position: relative;
                bottom: 2px;
                font-weight: 700;
                margin-right: 4px;
                margin-left: 4px;'>.
        </span>
        <a href="https://bigcommand.com/terms" style='color: #21455E; text-decoration: none !important;'>Team</a>
    </p>
</div>
</body>
</html>

<style type="text/css">
    .email-lines {
        line-height: 1.5;
        font-size: 14px;
        color: #21455E;
        font-weight: 300;
        margin-top: 15px;
    }

    .sign-in-btn {
        background: #00ACDC;
        border: 1px solid #0BACDB;
        border-radius: 20px;
        opacity: 1;
        width: 167px;
        cursor: pointer;
        color: #fff;
        text-decoration: none !important;
        padding: 10px 25px;
        position: relative;
        left: 0;
        right: 0;
        margin: auto;
    }
</style>