<?php
//Specify the public key that is corresponding to the private key on the server side.
$client_config = array();
$client_config['publicKey'] = <<<EOF
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDhHYgpBmCEJIWJIoAkPvJRs1w4
xlltfKKbzaRgU2gVVtH/kMFePotjGkSlONcb9k4Bu+zAjE2LEg2GpfUcjrexqA7H
oLe9XD9/X1tDloXHNe+7G00VxGviStZkYuooVjE7OQ+1WMnSIJMKYAyfuz9sBwmN
VBZK4K6HvXfGIp+u8wIDAQAB
-----END PUBLIC KEY-----
EOF;

$client_config['callback_link'] = 'http://localhost/social/demo/client/';
?>