<?php

ini_set('display_errors', 1);
ini_set('html_errors', 1);
ini_set('error_reporting', E_ALL);

require(dirname(__FILE__).'/bootstrap.inc.php');

require_once($avancorePath.'/classes/Ae/Avancore.php');
require_once($paxPath.'/classes/Pwg/Pwg.php');
require_once(dirname(__FILE__).'/../classes/Pd/PwgDemos.php');

$i = Pd_PwgDemos::getInstance();
