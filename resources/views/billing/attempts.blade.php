<!DOCTYPE html>
<html lang="en">
   <body style="background-color: #F9FBFB; font-family: sans-serif">
      <table width="100%">
         <tr>
            <td width="100%" align="center">
             
                  <!-- <h3 align="center">[BigCommand] Welcome to Adilo cloud video hosting</h3> -->
                 
                     <table width="540px" border="0" cellspacing="0" cellpadding="50" style="background-color: #FFFFFF; border-radius: 12px; margin-top: 50px">
                        <!-- <tr>
                           <td>
                              <img src="{{ asset('/img/welcome-mail/bigcommand.png') }}" style="float: left;">
                           </td>
                           <td>
                              <img src="{{ asset('/img/welcome-mail/adilo.png') }}" style="float: right;">
                           </td>
                        </tr> -->
                        <tr>
                           <td colspan="2">
                              <p style="line-height: 2; color: #21455E; font-size: 14px;">Dear {{$full_name}}</p>
                              <p style="line-height: 2; color: #21455E; font-size: 14px;">Unfortunately, our recent attempt to renew your BigCommand/Adilo subscription has failed. As of today, you have an outstanding balance of ${{$amount}}.</p>
                              <p style="line-height: 2; color: #21455E; font-size: 14px;">Here at Please log in to your account and update your payment method. We will try to charge your payment method as soon as the settings change:</p>
                              
                           </td>
                        </tr>
                        <tr>
                           <td colspan="2" style="text-align: center; padding: 30px;">
                              <a href="{{ config('env.ROOT_URL') }}" style="background-color: #0DABD8; color: #ffffff; padding: 10px 56px; border-radius: 20px; text-decoration: none;">Update billing information</a>
                           </td>
                        </tr>
                        <tr>
                           <td colspan="2">
                              <p align="justify" style="line-height: 2; color: #21455E; font-size: 14px;">If you do not update your payment method, we will make a few more attempts with the current information in the coming days.</p>
                           </td>
                           <td colspan="2">
                              <p align="justify" style="line-height: 2; color: #21455E; font-size: 14px;">Unless we are successful in renewing your account by {{date(strtotime($enddate))}}, your BigCommand account may be suspended or terminated..</p>
                           </td>
                        </tr>
                        <tr>
                           <td colspan=>
                              <img src="{{ asset('/img/welcome-mail/img.png') }}" style="width: 100%;">
                           </td>
                        </tr>
                        <tr>
                           <td colspan="2">
                              <p style="line-height: 2; color: #21455E; font-size: 14px;">Thank you for being a BigCommand customer.</p>
                              <p style="line-height: 2; color: #21455E; font-size: 14px;">Sincerely,<br> Big Diamond</p>
                              
                           </td>
                        </tr>
                        <!-- <tr>
                           <td colspan="2"></td>
                           </tr> -->
                     </table>
                    
                
               <p style="text-align: center; font-size: 14px; color: #788D9D; margin-bottom: 0px;">&#169; {{now()->year}} {{$company['vendor']}}, All Rights Reserved.</p>
               <p style="margin-top: 5px;">
               <p style="color: #21455E; font-size: 14px; margin: 6px">108 West 13th Street Wilmington, DE 19801 United States</p>
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