<?php
require_once('../SocialAgent.class.php');
$agent = new SocialAgent();
$agent->handleOAuthEndPoint('weibo');
?>