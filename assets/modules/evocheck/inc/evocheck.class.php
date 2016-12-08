<?php
/*
 * EvoCheck Standalone  
 *
 * Script to help finding compromised addons & files in a MODX Evolution installation 
 * 
 * @description Upload to /manager and call via browser - initial password is "changeme" (Line 16) 
 * @version 0.1
 * @author Deesen
 * @lastupdate 2016-11-25
 * 
 **/
if (IN_MANAGER_MODE != 'true') die('<h1>ERROR:</h1><p>Please use the MODx Content Manager instead of accessing this file directly.</p>');

class EvoCheck {

	var $password = 'changeme';
	var $logged_in = false;

	var $db = null;
	var $action = '';
	var $summary_length = 0;
	var $search_term = '';
	var $output = '';
	var $tpl = array();
	var $criteria_db = array();
	var $criteria_f = array();
	var $viewSetup = array();
	var $criteriaSetup_db = array();
	var $criteriaSetup_f = array();
	var $changed_after = 0;
	var $url = '';
	var $version = '0.2';
	var $templates = array();
	var $module_params = array();
	var $language = '';
	var $lang = array();
	var $json_lang = '';
	
	function __construct($_module_params=array())
	{
		global $modx, $database_user, $database_password, $database_server;
		
		// Determine settings for module- or standalone-mode
		if(!empty($_module_params) && is_object($modx)) {
			$this->basedir          = MODX_BASE_PATH;
			$this->module_params    = $_module_params;
			$this->url              = $_module_params['url'];
			$this->inc_dir          = $_module_params['inc_dir'];
			$this->action_dir       = $_module_params['action_dir'];
			$this->processor_dir    = $_module_params['processor_dir'];
			$this->tpl_dir          = $_module_params['tpl_dir'];
			$this->lang_dir         = $_module_params['lang_dir'];
		} else {
			$this->module_params = NULL;
			$this->url = basename(__FILE__);
			$this->basedir = basename(__FILE__);
			// @todo: $this->inc_dir = '';
		}
		
		// Set required PHP-Settings
		mb_internal_encoding("UTF-8");
		mb_regex_encoding("UTF-8");

		// Init DB
		$this->db = new mysqli($database_server, $database_user, $database_password);
		if ($this->db->connect_errno) {
			printf("Connect failed: %s\n", $this->db->connect_error);
			exit();
		}
		
		// Handle Login
		if(!$_SESSION['evocheck_logged_in']) {
			if (isset($_POST['pass'])) {
				if ($_POST['pass'] == $this->password) {
					$_SESSION['evocheck_logged_in'] = true;
				}
				else {
					sleep(rand(2, 5)); // prevent bruit force attacks
				}
			}
			else if (IN_MANAGER_MODE == 'true' && $modx->getLoginUserID() == 1) {
				$_SESSION['evocheck_logged_in'] = true;
			}
		}

		// Set default setup
		$this->setTemplates();
		
		// Prepare translations
		$this->language = isset($modx->config['manager_language']) ? $modx->config['manager_language'] : 'english';
		$this->loadTranslations();
		
		// Set parameters or defaults
		$this->logged_in = isset($_SESSION['evocheck_logged_in']) ? true : false;
		$this->action = isset($_REQUEST['ec_action']) ? $_REQUEST['ec_action'] : 'dashboard';
		$this->summary_length = isset($_GET['ec_summary']) ? $_GET['ec_summary'] : 100;
		$this->search_term = isset($_GET['ec_term']) ? $modx->removeSanitizeSeed($_GET['ec_term']) : '((\d+)\h*\/\h*(\d+)|base64_decode\h*\(|eval\h*\(|system\h*\(|shell_exec\h*\(|<\?php[^\n]{200,}|\$GLOBALS\[\$GLOBALS\[|;\h*\$GLOBALS|\$GLOBALS\h*;)';

		$this->criteria_db = isset($_GET['ec_criteria_db']) ? $_GET['ec_criteria_db'] : 
			(isset($_GET['ec_term']) ? array() : array('plugin','snippet'));
		
		$this->criteria_f = isset($_GET['ec_criteria_f']) ? $_GET['ec_criteria_f'] : array();
		if(in_array('all', $this->criteria_f)) $this->criteria_f = 'all';
		
		if(isset($_GET['ec_changed_after']) && !empty($_GET['ec_changed_after'])) {
			$changed_after = DateTime::createFromFormat('d-m-Y H:i:s', $_GET['ec_changed_after']);
			$this->changed_after = $changed_after->getTimestamp();
		}
	}

	function __destruct() {
		$this->db->close();
	}

	function output()
	{
		if(!$this->logged_in) {
			echo $this->parseTpl('header', array(
				'title'=>'EvoCheck',
			));
			echo $this->parseTpl('login_form', array(
				'title'=>'EvoCheck',
			));
			echo $this->parseTpl('footer');
			exit;
		}

		switch($this->action) {
			case 'dashboard':
				echo $this->parseTpl('header', array( 'title'=>'' ));
				echo $this->parseTpl('navbar');
				$this->passwordWarning();
				echo $this->includeAction('dashboard.static');
				echo $this->parseTpl('footer');
				break;
			case 'search':
				echo $this->parseTpl('header', array( 'title'=>'Menu' ));
				echo $this->parseTpl('navbar');
				echo $this->parseTpl('search', array(
					'search_term'=>$this->search_term,
					'summary_length'=>$this->summary_length,
					'criteria_db'=>$this->renderCheckboxes('ec_criteria_db', $this->criteriaSetup_db, $this->criteria_db),
					'criteria_f'=>$this->renderCheckboxes('ec_criteria_f', $this->criteriaSetup_f, $this->criteria_f),
					'server_time'=>date('d-m-Y H:i:s')
				));
				echo $this->parseTpl('footer');
				break;
			case 'searchresults':
				if($this->search_term) {
					echo $this->parseTpl('header', array( 'title'=>'' ));
					$this->searchDb($this->search_term);
					$this->searchFiles($this->search_term);
					echo $this->parseTpl('footer');
				};
				break;
			case 'view':
				echo $this->parseTpl('header', array(
					'title'=>'View '.$this->viewSetup[$_GET['ec_type']]['label'],
				));
				echo $this->viewSourcecode($this->search_term, $_GET['ec_type'], $_GET['ec_id']);
				echo $this->parseTpl('footer');
				break;
			case 'adminer':
				// @todo: Add https://www.adminer.org/en/
				break;
				
			////////////////////////////////////////////
			// PROCESSORS
			case 'delete':
				$this->includeProcessor('ajax.delete_element');
				break;
		}
	}
	
	function includeAction($action, $vars=array()) {
		if(preg_match('/^[0-9a-z._]+$/i', $action)) {
			extract($vars);
			include($this->action_dir . $action . '.php');
		}
	}

	function includeProcessor($processor, $vars=array()) {
		if(preg_match('/^[0-9a-z._]+$/i', $processor)) {
			extract($vars);
			include($this->processor_dir . $processor . '.php');
		}
	}
	
	function passwordWarning() {
		echo $this->password == 'changeme' && is_null($this->module_params) ? '<br/>
		<ul class="list-group">
		  <li class="list-group-item list-group-item-danger"><b>Security issue! Please change your password in Line 16!</b></li>
		</ul>' : '';
	}

	// @todo: Prepare for snippets, modules, chunks etc
	function searchDb($term) {

		foreach($this->criteria_db as $key) {
			echo '<hr><h3>'.$this->criteriaSetup_db[$key]['label'].'<br/><small>'.$this->lang['searched_for'].' <code>"' . $term . '"</code></small></h3>';

			$query = $this->parseSqlStatement($this->criteriaSetup_db[$key]['sql_select']);
			$results = false;
			
			if ($rs = $this->db->query($query)) {
				while ($row = $rs->fetch_assoc()) {
					if(!$matches = $this->findMatches($term, $row['code'])) continue;
					$results = true;
					$summary = $this->renderSummary($term, $row['code'], $matches);
					$link = $this->createViewSourceLink($row['name'], $key, $row['id']);
					$buttons = $this->renderElementButtons($key, $row['id']);
					echo '<li id="res_'.$key.'_'.$row['id'].'">'. $link . ' <strong>(' . $row['id'] . ')</strong> '. $buttons . $summary . '<br/></li>';
					flush();
					ob_flush();
				}
				$rs->free_result();
			}
			if(!$results) echo $this->lang['no_results'];
		}
	}

	function searchFiles($term, $startdir=MODX_BASE_PATH, $displayTitle=true) {
		if(empty($this->criteria_f)) return;
		if($displayTitle) echo '<hr><h3>Files<br/><small>'.$this->lang['searched_for'].' <code>"'.$term.'"</code></small></h3>';
		$dir = new DirectoryIterator($startdir);
		foreach ($dir as $file) {
			if($file->isDot()) continue;
			$f = $file->getPathname();
			if(!is_dir($f)) {

				// Search only through specific file-extensions
				if($this->criteria_f !== 'all' && !in_array($file->getExtension(), $this->criteria_f)) continue;

				// Check for filedate if required
				if ($file->getMTime() < $this->changed_after) continue;

				if(is_readable($f)) {
					$code = file_get_contents($file->getPathname());
					if(!$matches = $this->findMatches($term, $code)) continue;
					$summary = $this->renderSummary($term, $code, $matches);
					$fileName = str_replace(array('\\', MODX_BASE_PATH), array('/', ''), $f);
					$link = $this->createViewSourceLink($fileName, 'file', $fileName);
					// @todo: add details like changed_on ?
					$buttons = $this->renderElementButtons('file', $fileName);
					echo '<li id="file_'.md5($fileName).'">'. $link . '&nbsp;'. $buttons . $summary .'<br/></li>';
					flush();
					ob_flush();
					
				} else {
					echo 'File not readable: '.$f.'</br>';
					flush();
					ob_flush();
				}
			} else {
				$this->searchFiles($term, $f, false);
			}
		}
	}
	
	function createViewSourceLink($label, $type, $id, $markDisabled=false) {
		$href = '[+baseurl+]&ec_action=view&ec_type='.$type.'&ec_id='.$id;
		$href .= $this->search_term ? '&ec_term='.urlencode($this->search_term) : '';
		$disabled = $markDisabled ? ' disabled' : '';
		return $this->parsePlaceholders('<a href="'.$href.'" class="popup'.$disabled.'">'. $label .'</a>');
	}
	
	function renderSummary($term, $code, $matches)
	{
		if(!is_array($matches[0]) || $this->summary_length == 0) return '';
		
		$summary = '';
		foreach ($matches[0] as $index => $match) {
			$term = $match[0];
			$pos  = $match[1];

			$cutPos    = $pos - $this->summary_length < 0 ? 0 : $pos - $this->summary_length;
			$cutLength = $this->summary_length * 2;

			$tmp = mb_substr($code, $cutPos, $cutLength);
			$tmp = htmlspecialchars($tmp, ENT_QUOTES, 'UTF-8');
			$tmp = $this->highlightSearchTerm($tmp);

			$summary .= '<pre>' . $tmp . '</pre>';
		}
		return $summary;
	}
	
	function findMatches($term, $code)
	{
		if(!$term) return false;
		preg_match_all('/(*UTF8)'.$term.'/i', $code, $matches, PREG_OFFSET_CAPTURE);
		return empty($matches[0]) ? false : $matches;
	}
	
	function highlightSearchTerm($code)
	{
		return preg_replace('/(*UTF8)'.$this->search_term.'/i', '<span class="highlighted">${0}</span>', $code);
	}
	
	function viewSourcecode($term, $type, $id)
	{
		switch($type) {
			case 'plugin':
			case 'snippet':
			case 'template':
			case 'chunk':
			case 'module':
			case 'content':
				$query = $this->criteriaSetup_db[$type]['sql_select'];
				break;
			case 'file':
				$file = $id;
				break;
			default:
				return 'Wrong type or ID';
		}

		$row = array();
		if(isset($file)) {
			$row['name'] = $file. ($this->search_term ? '<br/><small>[%searched_for%] <code>"'.$this->search_term.'"</code></small>' : '');
			$row['description'] = $this->renderFileDetails($file);
			$code = file_get_contents(MODX_BASE_PATH.$file);
		} else {
			$id = intval($id);
			$query .= " WHERE id='{$id}'";
			$query = $this->parseSqlStatement($query);
			if($rs = $this->db->query($query)) {
				$row = $rs->fetch_assoc();
				$code =& $row['code'];
				$row['description'] = $row['description']. ($this->search_term ? '<br/><small>[%searched_for%] <code>"'.$this->search_term.'"</code></small>' . '<hr/>' : '<hr/>');
			} else {
				$code = 'SQL-Query not successful';
			};
			
		}
		
		// highlight_string() needs prepending "<?php" to work - add only for PHP
		if ((isset($file) && substr($file, -4) == '.php' || isset($query)) && substr($code, 0, 5) != '<?php') $code = "<?php\n".$code; 
		$code = highlight_string($code, true);
		
		if($matches = $this->findMatches($term, $code)) {
			$code = $this->highlightSearchTerm($code);
		}
		
		return $this->parseTpl('source', array(
			'name'=>'<strong>'.$this->viewSetup[$type]['label'] .'</strong> '. (isset($row['name']) ? $row['name'] : ''),
			'description'=>(isset($row['description']) ? $row['description'] : ''),
			'source'=>$code
		));
	}

	function renderElementButtons($type, $id)
	{
		return $this->parsePlaceholders('<a href="[+baseurl+]" data-action="delete" data-type="'.$type.'" data-id="'.$id.'" class="btn btn-xs btn-danger ajax">[%btn_delete%]</a>');
	}
	
	function renderFileDetails($file)
	{
		$s = stat(MODX_BASE_PATH.$file);
			
		$details = '
		<table class="table filedetails">
			<tr><td>dev</td><td>'.$s['dev'].'</td><td>&nbsp;</td><td>rdev</td><td>'.$s['rdev'].'</td></tr>
			<tr><td>ino</td><td>'.$s['ino'].'</td><td>&nbsp;</td><td>size</td><td>'.$s['size'].'</td></tr>
			<tr><td>mode</td><td>'.$s['mode'].'</td><td>&nbsp;</td><td>atime</td><td>'.date('d-m-Y H:i:s', $s['atime']).'</td></tr>
			<tr><td>nlink</td><td>'.$s['nlink'].'</td><td>&nbsp;</td><td>mtime</td><td>'.date('d-m-Y H:i:s', $s['mtime']).'</td></tr>
			<tr><td>uid</td><td>'.$s['uid'].'</td><td>&nbsp;</td><td>ctime</td><td>'.date('d-m-Y H:i:s', $s['ctime']).'</td></tr>
			<tr><td>gid</td><td>'.$s['gid'].'</td><td>&nbsp;</td><td></td><td></td></tr>
		</table>';
		
		return $details;
	}

	function setTemplates()
	{
		$this->criteriaSetup_db = array(
			'plugin'=>array(
				'label'=>'Plugins',
				'sql_select'=>"SELECT id, `name`, description, plugincode AS code FROM [+prefix+]site_plugins"
			),
			'snippet'=>array(
				'label'=>'Snippets',
				'sql_select'=>"SELECT id, `name`, description, snippet AS code FROM [+prefix+]site_snippets"
			),
			'template'=>array(
				'label'=>'Templates',
				'sql_select'=>"SELECT id, templatename AS `name`, description, content AS code FROM [+prefix+]site_templates"
			),
			'chunk'=>array(
				'label'=>'Chunks',
				'sql_select'=>"SELECT id, `name`, description, snippet AS code FROM [+prefix+]site_htmlsnippets"
			),
			'content'=>array(
				'label'=>'Content',
				'sql_select'=>"SELECT id, pagetitle AS `name`, description, content AS code FROM [+prefix+]site_content"
			),
			'module'=>array(
				'label'=>'Modules',
				'sql_select'=>"SELECT id, `name`, description, modulecode AS code FROM [+prefix+]site_plugins"
			)
		);

		$this->criteriaSetup_f = array(
			'php'=>array( 'label'=>'.php', 'extension'=>'php' ),
			'js' =>array( 'label'=>'.js',  'extension'=>'js' ),
			'htaccess' =>array( 'label'=>'.htaccess',  'extension'=>'htaccess' ),
			'all' =>array( 'label'=>'All Files', 'extension'=>'*' ),
		);
		
		$this->viewSetup = array(
			'plugin'=>array( 'label'=>'Plugin' ),
			'snippet' =>array( 'label'=>'Snippet' ),
			'template' =>array( 'label'=>'Template' ),
			'chunk' =>array( 'label'=>'Chunk' ),
			'content' =>array( 'label'=>'Content' ),
			'module' =>array( 'label'=>'Module' ),
			'file' =>array( 'label'=>'File' ),
		);
		
	}

	function fetchTpl($tpl)
	{
		if(!isset($this->templates[$tpl])) {
			$tplFile = $this->tpl_dir.$tpl.'.tpl';
			if(file_exists($tplFile)) {
				$this->templates[$tpl] = file_get_contents($tplFile);
			} else {
				$alert = 'Tpl not found: '.$tpl;
				$this->templates[$tpl] = $alert;
			}
		}
		return $this->templates[$tpl];
	}

	function parseTpl($tpl, $placeholders=array())
	{
		$content = $this->fetchTpl($tpl);
		$content = $this->parsePlaceholders($content, $placeholders);
		return $content;
	}

	function parsePlaceholders($content, $placeholders=array())
	{
		// Prepare default placeholders
		$placeholders = array_merge($placeholders, array(
			'brand'=>'EvoCheck',
			'version'=>$this->version,
			'baseurl'=>$this->url,
			'action_id'=>$this->module_params['action_id'],
			'module_id'=>$this->module_params['module_id'],
			'json_lang'=>$this->json_lang
		));

		foreach($placeholders as $key=>$value)
			$content = str_replace('[+'.$key.'+]', $value, $content);
		
		foreach($this->lang as $key=>$value)
			$content = str_replace('[%'.$key.'%]', $value, $content);

		return $content;
	}
	
	function parseSqlStatement($statement, $placeholders=array())
	{
		global $dbase, $table_prefix;
		
		// Prepare default placeholders
		$placeholders = array_merge($placeholders, array(
			'prefix'=>$dbase.'.'.$table_prefix,
		));
		
		foreach($placeholders as $key=>$value)
			$statement = str_replace('[+'.$key.'+]', $value, $statement);
		
		return $statement;
	}

	function renderCheckboxes($name, $values, $actives=array())
	{
		$actives = is_array($actives) ? $actives : array();
		
		$output = '';
		foreach($values as $value=>$criteria) {
			$checked = in_array($value, $actives) ? ' checked="checked"' : '';
			$output .= '<div class="checkbox inline"><label><input name="' . $name . '[]" value="' . $value . '" type="checkbox"'. $checked .'> ' . $criteria['label'] . '</label></div>';
		}
		return $output;
	}
	
	function loadTranslations()
	{
		$_lang = array();
		require($this->lang_dir . 'english.inc.php');
		if($this->language != 'english') {
			$file = $this->lang_dir . $this->language .'.inc.php';
			if(file_exists($file)) include($file);
		}
		$this->lang = $_lang;
		$this->json_lang = json_encode($_lang);
	}
}

?>