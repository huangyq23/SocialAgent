<?php
require_once('config.inc.php');
require_once('../../lib/server/SocialAgent.class.php');
$agent = new SocialAgent($server_config);
$agent->handleOAuthEndPoint('weibo');
?>