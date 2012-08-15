<?php
require_once('Cryptography.class.php');
$publicKey = <<<EOF
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDhHYgpBmCEJIWJIoAkPvJRs1w4
xlltfKKbzaRgU2gVVtH/kMFePotjGkSlONcb9k4Bu+zAjE2LEg2GpfUcjrexqA7H
oLe9XD9/X1tDloXHNe+7G00VxGviStZkYuooVjE7OQ+1WMnSIJMKYAyfuz9sBwmN
VBZK4K6HvXfGIp+u8wIDAQAB
-----END PUBLIC KEY-----
EOF;

$crypto = new Cryptography($publicKey);

if($_POST['action']=="share"){
    $action = 'share';
    $data = array(
        'channel' => join(',',$_POST['channel']),
        'unique_id' => '1234567890',
        'text' => $_POST['text'],
        'img_url' => $_POST['img_url'],
        'link_url' => $_POST['link_url'],
    );
}

if($_POST['action']=="authorize"){
    $action = 'authorize';
    $data = array(
        'channel' => $_POST['channel'],
        'unique_id' => '1234567890',
        'callback_url' => '/social/',
    );
}

$serialized_data = json_encode($data);
$payload = $crypto->encrypt($serialized_data);
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body{
            background-color: #d9d9d9;
        }
        #container{
            width: 350px;
            margin-left: auto;
            margin-right: auto;
        }
        #loading_text{
            background-color: #ffffdd;
            color: #818181;
            border-radius: 3px;
            font-family: Georgia, "Times New Roman", Times, serif;
            font-style: italic;
            text-align: center;
            background-image: url(img/loading.gif);
            background-repeat: no-repeat;
            padding-left: 80px;
            line-height: 80px;
            height: 80px;
            font-size: 30px;
            width: 270px;
        }
    </style>
</head>
    <body>
        <div id="container">
            <div id="loading_text">Loading...</div>
            <form id="form" method="POST" action="webapi.php?action=<?php echo $action; ?>">
                <input name="payload" type="hidden" value="<?php echo $payload; ?>">
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