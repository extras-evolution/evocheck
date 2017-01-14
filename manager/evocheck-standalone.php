<?php

define('IN_MANAGER_MODE', 'true');
require('includes/config.inc.php');
$standaloneFile = basename(__FILE__);
startCMSSession();
require(MODX_BASE_PATH.'assets/modules/evocheck/evocheck.module.php');