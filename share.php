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

$data = array(
    'channel' => 'weibo',
    'unique_id' => '1234567890',
    'text' => '盗图测试5',
    'img_url' => 'http://fmn.rrimg.com/fmn065/xiaozhan/20120807/0905/x_large_VKyd_347800006da21263.jpg',
    'link_url' => 'http://www.conti.com.cn/weibo/data/21213/',
);
$serialized_data = json_encode($data);
$payload = $crypto->encrypt($serialized_data);
?>


<html>
<head>
</head>
    <body>
        <form method="POST" action="http://127.0.0.1/social/webapi.php?action=authorize">
            <textarea name="payload" rows="10" cols="50"><?php echo $payload; ?></textarea><br />
            <input type="submit" />
        </form>
        <form method="POST" action="http://127.0.0.1/social/webapi.php?action=share">
            <textarea name="payload" rows="10" cols="50"><?php echo $payload; ?></textarea><br />
            <input type="submit" />
        </form>
    </body>
</html>