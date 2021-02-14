<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta Information -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
</head>
<body>
<!-- Hubspot ready-->
<div class="container" style="width:100px;height:100px; border-style:double; padding: 3px;" onclick="openWindow('https://app.hubspot.com/oauth/authorize?client_id=c491fbcc-19f4-4b32-ae85-7c780eb6be34&redirect_uri='.config('env.ROOT_URL').'/oauth/hubspot&scope=oauth','Hubspot')">
    <img style="width:100%" src="https://blog.salecycle.com/wp-content/uploads/2018/12/hubspot-img.png" />
</div>

<!-- Zoho ready -->
<div class="container" style="width:100px;height:100px; border-style:double; padding: 3px;" onclick="openWindow('https://accounts.zoho.com/oauth/v2/auth?scope=ZohoCRM.users.ALL&client_id=1000.PFA73ODT2X92NOOSKIHUBCHDO0X1JH&response_type=code&access_type=offline&redirect_uri='.config('env.ROOT_URL').'/oauth/zoho','ZOHO')">
    <img style="width:100%" src="https://www.mailigen.ru/assets/templates/rise-n-tell/img/illustrations/integrations/zoho.svg" />
</div>

<!-- ZOOM ready-->
<div class="container" style="width:100px;height:100px; border-style:double; padding: 3px;" onclick="openWindow('https://zoom.us/oauth/authorize?response_type=code&client_id=_8VYLookRnabgHGdH9u0oA&redirect_uri='.config('env.ROOT_URL').'/oauth/zoom','ZOOM')">
    <img style="width:100%" src="https://d24cgw3uvb9a9h.cloudfront.net/static/93719/image/new/ZoomLogo.png" />
</div>

<!-- GoToWebinar ready-->
<div class="container" style="width:100px;height:100px; border-style:double; padding: 3px;" onclick="openWindow('https://api.getgo.com/oauth/v2/authorize?client_id=0RORKv5KHcHjG4RgVorGT0gp4pOQCKWa&response_type=code','GoToWebinar')">
    <img style="width:100%" src="https://www.scaleupconsulting.com/wp-content/uploads/2015/01/Infusionsoft-Logo-EPS-vector-image-300x200.png" />
</div>

<!-- GetResponse ready-->
<div class="container" style="width:100px;height:100px; border-style:double; padding: 3px;" onclick="openWindow('https://app.getresponse.com/oauth2_authorize.html?response_type=code&client_id=3ebeff9f-f180-11e9-bb53-f04da2754d84&state=xyz&redirect_uri='.config('env.ROOT_URL').'/oauth/getresponse','GetResponse')">
    <img style="width:100%" src="https://assets.pcmag.com/media/images/432645-getresponse-logo.jpg?width=333&height=245" />
</div>

<script>
    function openWindow(url, title) {
        window.location.href =url;
    }
</script>

</body>
</html>
