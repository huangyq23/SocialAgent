<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('config.inc.php');
require_once('../../lib/client/SocialClient.class.php');
$client = new SocialClient($client_config);
$client->handleClientRequest();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Loading...</title>
    <meta charset="utf-8">
    <link type="text/css" rel="stylesheet" href="css/style.css">
</head>
    <body>
        <div id="container">
            <div id="loading_text">Loading...</div>
            <form id="form" method="POST" action="../server/webapi.php?action=<?php echo $client->getAction(); ?>">
                <input name="payload" type="hidden" value="<?php echo $client->getPayload(); ?>">
            </form>
        </div>
        <script type=text/javascript>
            setTimeout(function(){
                var form = document.getElementById('form');
                form.submit();
            }, 1000);
        </script>
    </body>
</html>