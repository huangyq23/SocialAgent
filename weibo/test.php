<?php
include_once('../vendor/Base62/Base62.class.php');
function getCodeByMid($mid){
    $url = '';

    for ($i = strlen($mid) - 7; $i > -7; $i -=7)
    {
        $offset1 = $i < 0 ? 0 : $i;
        $offset2 = $i + 7;
        $num = substr($mid, $offset1,$offset2-$offset1);
        //mid.substring(offset1, offset2);
        $base62 = new Base62();
        $num = $base62->convert($num);
        $url = $num .$url;
    }

    return $url;
}
echo getCodeByMid('3476698693738423');