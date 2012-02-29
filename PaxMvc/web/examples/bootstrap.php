<?php

ini_set('display_errors', 1);
ini_set('html_errors', 1);
ini_set('error_reporting', E_ALL);

$avancorePath = dirname(__FILE__).'/../../../../Avancore3/Avancore';

require_once($avancorePath.'/classes/Ae/Avancore.php');
require_once(dirname(__FILE__).'/../../classes/Pmt/PaxMvc.php');
Ae_Avancore::getInstance();
