<?php
//Specify the public key that is corresponding to the private key on the server side.
$client_config = array();

//The callback url after an operation is complete.
$client_config['callbackUrl'] = 'http://localhost/social/demo/client/';

//The public key corresponding to the private key on server.
$client_config['publicKey'] = <<<EOF
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDhHYgpBmCEJIWJIoAkPvJRs1w4
xlltfKKbzaRgU2gVVtH/kMFePotjGkSlONcb9k4Bu+zAjE2LEg2GpfUcjrexqA7H
oLe9XD9/X1tDloXHNe+7G00VxGviStZkYuooVjE7OQ+1WMnSIJMKYAyfuz9sBwmN
VBZK4K6HvXfGIp+u8wIDAQAB
-----END PUBLIC KEY-----
EOF;

?>