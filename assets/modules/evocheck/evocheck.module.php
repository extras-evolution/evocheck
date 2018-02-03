<?php
if (IN_MANAGER_MODE != 'true') die('<h1>ERROR:</h1><p>Please use the EVO Content Manager instead of accessing this file directly.</p>');

define('EC_STANDALONE', !isset($modx->manager));

require(MODX_BASE_PATH.'assets/modules/evocheck/inc/evocheck.class.php');

$module_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$_module_params = array(
	'action_id'         => isset($modx->manager->action) ? $modx->manager->action : 0,
	'module_id'         => $module_id,
	// 'language'          => 'en',
	'dirname'           => basename( dirname(__FILE__) ),
	'path'              => realpath( dirname(__FILE__) ) . DIRECTORY_SEPARATOR,
	'inc_dir'           => realpath( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR,
	'action_dir'        => realpath( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'actions' . DIRECTORY_SEPARATOR,
	'processor_dir'     => realpath( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'processors' . DIRECTORY_SEPARATOR,
	'tpl_dir'           => realpath( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR,
	'lang_dir'          => realpath( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR,
	'integrity_dir'     => realpath( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'integrity' . DIRECTORY_SEPARATOR,
	'base_path'         => MODX_SITE_URL.'assets/modules/'.basename( dirname(__FILE__) ).'/',
	'url'               => !EC_STANDALONE
							? 'index.php?a='. $modx->manager->action .'&amp;id=' . $module_id
							: MODX_MANAGER_URL.$standaloneFile.'?',
);

$ec = new EvoCheck($_module_params);
$ec->output();