<?php

define('IN_MANAGER_MODE', 'true');

if(file_exists('assets/cache/siteManager.php')) {
    require('assets/cache/siteManager.php');
    if(file_exists(MGR_DIR.'/includes/config.inc.php')) {
        require(MGR_DIR.'/includes/config.inc.php');
    } else {
        exit('config.inc.php not found');
    }
} else {
    exit('EvoCheck Standalone works only in your EVO root dir.');
}

$standaloneFile = basename(__FILE__);
startCMSSession();
require(MODX_BASE_PATH.'assets/modules/evocheck/evocheck.module.php');