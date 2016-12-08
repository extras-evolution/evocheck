// <?php 
/**
 * EvoCheck Standalone  
 *
 * A small assistant to help finding compromised code in a MODX Evolution installation for the seldom case, it has been "hacked".
 * 
 * @version     0.2
 * @author      Deesen
 * @category	module
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties
 * @internal	@guid evocheck92485832rt3
 * @internal	@shareparams 0
 * @internal	@modx_category Manager and Admin
 * @lastupdate  08/12/2016
 */

if (IN_MANAGER_MODE != 'true') die('<h1>ERROR:</h1><p>Please use the MODx Content Manager instead of accessing this file directly.</p>');

if ($modx->getLoginUserID() != 1) {
	die('<h1>ERROR:</h1>No permission.');
}

require(MODX_BASE_PATH.'assets/modules/evocheck/inc/evocheck.class.php');

$module_id = intval($_GET['id']);

$_module_params = array(
	'action_id'         => $modx->manager->action,
	'module_id'         => $module_id,
	// 'language'          => 'en',
	'dirname'           => basename( dirname(__FILE__) ),
	'path'              => realpath( dirname(__FILE__) ) . DIRECTORY_SEPARATOR,
	'inc_dir'           => realpath( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR,
	'action_dir'        => realpath( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'actions' . DIRECTORY_SEPARATOR,
	'processor_dir'     => realpath( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'processors' . DIRECTORY_SEPARATOR,
	'tpl_dir'           => realpath( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR,
	'lang_dir'          => realpath( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR,
	'base_url'          => MODX_SITE_URL.'assets/modules/'.basename( dirname(__FILE__) ).'/',
	'url'               => 'index.php?a='. $modx->manager->action .'&amp;id=' . $module_id,
);

$ec = new EvoCheck($_module_params);
$ec->output();