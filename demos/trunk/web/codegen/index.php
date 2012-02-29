<?php

require_once('../deploy.settings.php');
ini_set('include_path', _DEPLOY_AVANCORE_PATH.PATH_SEPARATOR.'.');
require_once('Cg/Frontend.php');
$f = new Cg_Frontend();
$f->processWebRequest();
