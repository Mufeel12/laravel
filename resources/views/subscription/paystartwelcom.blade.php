<!DOCTYPE html>
<html lang="en">
   <body style="background-color: #F9FBFB; font-family: sans-serif">
      <table width="100%">
         <tr>
            <td width="100%" align="center">
             
                  <!-- <h3 align="center">[BigCommand] Welcome to Adilo cloud video hosting</h3> -->
                 
                     <table width="540px" border="0" cellspacing="0" cellpadding="50" style="background-color: #FFFFFF; border-radius: 12px; margin-top: 50px">
                        <tr>
                           <td>
                              <img src="{{ asset('/img/welcome-mail/bigcommand.png') }}" style="float: left;">
                           </td>
                           <td>
                              <img src="{{ asset('/img/welcome-mail/adilo.png') }}" style="float: right;">
                           </td>
                        </tr>
                        <tr>
                           <td colspan="2">
                              <p style="line-height: 2; color: #21455E; font-size: 14px;">Hello {{$full_name}}</p>
                              <p style="line-height: 2; color: #21455E; font-size: 14px;">Thank you for joining Adilo (a product of BigCommand LLC).</p>
                              <p style="line-height: 2; color: #21455E; font-size: 14px;">Here at Adilo, we’re breaking the barriers in cloud video hosting for businesses and we’re glad to welcome you to the family.</p>
                              <p style="line-height: 2; color: #21455E; font-size: 14px;">Your current plan is: {{$plan}}</p>
                              <p style="line-height: 2; color: #21455E; font-size: 14px;">Your username is: {{$username}}</p>
                              <p style="line-height: 2; color: #21455E; font-size: 14px;">Your current password is: {{$password}}</p>
                           </td>
                        </tr>
                        <tr>
                           <td colspan="2" style="text-align: center; padding: 30px;">
                              <a href="{{ config('env.ROOT_URL') }}" style="background-color: #0DABD8; color: #ffffff; padding: 10px 56px; border-radius: 20px; text-decoration: none;">Login to Adilo</a>
                           </td>
                        </tr>
                        <tr>
                           <td colspan="2">
                              <p align="justify" style="line-height: 2; color: #21455E; font-size: 14px;">We have onboarding/walkthrough videos embedded in most of the features that you will be using, it’s always very helpful to watch the videos first before using that feature.</p>
                           </td>
                        </tr>
                        <tr>
                           <td colspan="2">
                              <img src="{{ asset('/img/welcome-mail/img.png') }}" style="width: 100%;">
                           </td>
                        </tr>
                        <tr>
                           <td colspan="2">
                              <p style="line-height: 2; color: #21455E; font-size: 14px;">Thank you for being a BigCommand customer and welcome to Adilo.</p>
                              <p style="line-height: 2; color: #21455E; font-size: 14px;">Sincerely,<br> Adilo onboarding team</p>
                              <p style="line-height: 2; color: #21455E; font-size: 14px;">If you have questions or you need help, <a href="https://help.bigcommand.com" style="color: #075F9B;"><b>CONTACT SUPPORT</b></a></p>
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