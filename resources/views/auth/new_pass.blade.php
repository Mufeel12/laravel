<!DOCTYPE html>
<html lang="en">
<body style="background-color: #F9FBFB; font-family: sans-serif">

<p>
    Hello {{$full_name}}
</p>
<br>
<p>
    An admin has just updated your Adilo account.</p>
<br>
<br>

<p>The updated data is your {{$name}}.</p>
@if($old) <p>Old: {{$old}}</p> @endif
<p>Current: {{$new}}</p>
<br>
<br>

<p>You're advised to log into your account, change your password and confirm that everything is in other.</p>
<br>
<p>Note: If you did not request this profile update, please contact our support team immediately [https://help.bigcommand.com]
</p>
<br>
<p>Thank you for using Bigcommand!
</p>

<p>Regards,<br>
Bigcommand LLC
</p>
<br>
<p>
    108 West 13th Street,<br>
    Wilmington, DE<br>
    19801<br>
</p>
<br>

<p>Contact support [https://help.bigcommand.com]


</body>
</html>

