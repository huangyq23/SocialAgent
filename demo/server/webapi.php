<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(dirname(__FILE__).'/config.inc.php');
require_once(dirname(__FILE__).'/../../lib/server/SocialAgent.class.php');
$agent = new SocialAgent($server_config);
$agent->handleClientRequest();
?>