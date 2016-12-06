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

class EvoCheck {

	var $password = 'changeme';
	var $logged_in = false;

	var $db = null;
	var $frame = '';
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
	var $basename = '';
	var $version = '0.1';

	function __construct()
	{
		global $database_user, $database_password, $database_server;
		
		// Allows renaming of this file to increase security
		$this->basename = basename(__FILE__);
		
		// Init DB
		$this->db = new mysqli($database_server, $database_user, $database_password);
		if ($this->db->connect_errno) {
			printf("Connect failed: %s\n", $this->db->connect_error);
			exit();
		}
		
		// Catch Bots
		if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			header('HTTP/1.0 404 Not Found');
			exit('error');
		}
		
		// Handle Login
		if(isset($_POST['pass'])) {
			if($_POST['pass'] == $this->password) {
				$_SESSION['logged_in'] = true;
			} else {
				sleep(rand(2,5)); // prevent bruit force attacks
			}
		}
		
		// Set parameters
		$this->logged_in = isset($_SESSION['logged_in']) ? true : false;
		$this->frame = isset($_GET['f']) ? $_GET['f'] : 'mainframe';
		$this->summary_length = isset($_GET['summary_length']) ? $_GET['summary_length'] : $this->summary_length;
		$this->search_term = isset($_GET['search_term']) ? $_GET['search_term'] : $this->search_term;
		$this->criteria_db = isset($_GET['criteria_db']) ? $_GET['criteria_db'] : $this->criteria_db;
		
		$this->criteria_f = isset($_GET['criteria_f']) ? $_GET['criteria_f'] : $this->criteria_f;
		if(in_array('all', $this->criteria_f)) $this->criteria_f = 'all';
		
		if(isset($_GET['changed_after']) && !empty($_GET['changed_after'])) {
			$changed_after = DateTime::createFromFormat('d-m-Y H:i:s', $_GET['changed_after']);
			$this->changed_after = $changed_after->getTimestamp();
		}
		
		$this->setTemplates();
	}

	function __destruct() {
		$this->db->close();
	}

	function output()
	{
		if(!$this->logged_in) {
			echo $this->parseTpl($this->tpl["header"], array(
				'title'=>'MODX Evolution Hack-Assistant',
			));
			echo $this->parseTpl($this->tpl["login_form"], array(
				'title'=>'MODX Evolution <small>Hack-Assistant</small>',
			));
			echo $this->parseTpl($this->tpl["footer"]);
			exit;
		}

		switch($this->frame) {
			case 'mainframe':
				echo $this->parseTpl($this->tpl["header"], array(
					'title'=>'EvoCheck v'.$this->version,
				));
				echo $this->parseTpl($this->tpl["frame_setup"]);
				echo $this->parseTpl($this->tpl["footer"]);
				break;
			case 'fl':
				echo $this->parseTpl($this->tpl["header"], array(
					'title'=>'Menu',
				));
				echo $this->parseTpl($this->tpl["frame_left"], array(
					'version'=>$this->version,
				));
				echo $this->parseTpl($this->tpl["footer"]);
				break;
			case 'fr':
				echo $this->parseTpl($this->tpl["header"], array(
					'title'=>'',
				));
				$this->passwordWarning();
				echo $this->renderDashboard();
				echo $this->parseTpl($this->tpl["footer"]);
				break;
			case 'search':
				if($this->search_term) {
					echo $this->parseTpl($this->tpl["header"], array(
						'title'=>'',
					));
					$this->passwordWarning();
					$this->searchDb($this->search_term);
					$this->searchFiles($this->search_term);
					echo $this->parseTpl($this->tpl["footer"]);
				};
				break;
			case 'view':
				echo $this->parseTpl($this->tpl["header"], array(
					'title'=>'View '.$this->viewSetup[$_GET['type']]['label'],
				));
				echo $this->viewSourcecode($this->search_term, $_GET['type'], $_GET['id']);
				echo $this->parseTpl($this->tpl["footer"]);
				break;
			case 'delete':
				if(unlink(__FILE__)) {
					echo 'Successfully deleted '.$this->basename;
					exit;
				} else {
					echo 'Error deleting '.$this->basename;
					exit;
				}
				break;
		}
	}
	
	function passwordWarning() {
		echo $this->password == 'changeme' ? '<br/>
		<ul class="list-group">
		  <li class="list-group-item list-group-item-danger"><b>Security issue! Please change your password in Line 16!</b></li>
		</ul>' : '';
	}

	// @todo: Prepare for snippets, modules, chunks etc
	function searchDb($term) {

		foreach($this->criteria_db as $key) {
			echo '<hr><h3>'.$this->criteriaSetup_db[$key]['label'].' searched for "' . $term . '"</h3>';

			$query = $this->parseTpl($this->criteriaSetup_db[$key]['sql_select']);

			if ($rs = $this->db->query($query)) {
				while ($row = $rs->fetch_assoc()) {
					if(!$matches = $this->findMatches($term, $row['code'])) continue;
					$summary = $this->renderSummary($term, $row['code'], $matches);
					$link = $this->createViewSourceLink($row['name'], $key, $row['id']);
					echo '<li>'. $link . ' <strong>(' . $row['id'] . ')</strong>'. $summary . '<br/></li>';
					flush();
					ob_flush();
				}
				$rs->free_result();
			} else {
				echo 'No results';
			}
		}
	}

	function searchFiles($term, $startdir=MODX_BASE_PATH, $displayTitle=true) {
		if(empty($this->criteria_f)) return;
		if($displayTitle) echo '<hr><h3>Files searched for "'.$term.'"</h3>';
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
					echo '<li>'. $link . $summary .'<br/></li>';
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
	
	function createViewSourceLink($label, $type, $id) {
		$href = '[+basename+]?f=view&type='.$type.'&id='.$id;
		$href .= $this->search_term ? '&search_term='.urlencode($this->search_term) : '';
		return $this->parseTpl('<a href="'.$href.'" class="popup">'. $label .'</a>');
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
			$row['name'] = $file. ($this->search_term ? '<br/><small><i>searched for "'.$this->search_term.'"</i></small>' : '');
			$row['description'] = $this->renderFileDetails($file);
			$code = file_get_contents(MODX_BASE_PATH.$file);
		} else {
			$id = intval($id);
			$query .= " WHERE id='{$id}'";
			$query = $this->parseTpl($query);
			if($rs = $this->db->query($query)) {
				$row = $rs->fetch_assoc();
				$code =& $row['code'];
				$row['description'] = $row['description']. ($this->search_term ? '<br/><i>searched for "'.$this->search_term.'"</i>' . '<hr/>' : '<hr/>');
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
		
		return $this->parseTpl($this->tpl['source'], array(
			'name'=>'<strong>'.$this->viewSetup[$type]['label'] .'</strong> '. (isset($row['name']) ? $row['name'] : ''),
			'description'=>(isset($row['description']) ? $row['description'] : ''),
			'source'=>$code
		));
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

	function renderDashboard()
	{
		return $this->parseTpl($this->tpl['dashboard'], array(
			'critical_events'=>$this->renderCriticalEvents(),
			'modx_configuration'=>$this->renderConfigurationWarning()
		));
	}

	function renderCriticalEvents()
	{
		// Add more in future by experience
		// 18 OnBeforeCacheUpdate
		// 19 OnCacheUpdate
		// 81 OnManagerAuthentication
		// 89 OnManagerPageInit
		// 90 OnWebPageInit
		// 3  OnWebPagePrerender
		
		$criticalEvents = array(18,19,81,89,90,3);
		$criticalEvents = implode(',', $criticalEvents);

		$select = "SELECT sysevt.name as evtname, sysevt.id as evtid, pe.pluginid, plugs.name, pe.priority, plugs.disabled
		FROM [+prefix+]system_eventnames sysevt
		INNER JOIN [+prefix+]site_plugin_events pe ON pe.evtid = sysevt.id
		INNER JOIN [+prefix+]site_plugins plugs ON plugs.id = pe.pluginid
		WHERE evtid IN ({$criticalEvents})
		ORDER BY sysevt.name,pe.priority";

		$query = $this->parseTpl($select);

		if ($rs = $this->db->query($query)) {
			$insideUl = 0;
			$preEvt = '';
			$evtLists = '';
			while ($plugins = $rs->fetch_assoc()) {
				if ($preEvt !== $plugins['evtid']) {
					$evtLists .= $insideUl? '</ul>': '';
					$evtLists .= '<h4>'.$plugins['evtname'].' ['.$plugins['evtid'].']</h4><ul>';
					$insideUl = 1;
				}
				$link = $this->createViewSourceLink($plugins['name']. ' ['.$plugins['pluginid'].']'. ($plugins['disabled']?' (disabled)':''), 'plugin', $plugins['pluginid']);
				$evtLists .= '<li '.($plugins['disabled']?' style="color:#AAA"':'').'>'.$link.'</li>';
				$preEvt = $plugins['evtid'];
			}
			if ($insideUl) $evtLists .= '</ul>';
			$rs->free_result();
		} else {
			return 'No results';
		}

		return $evtLists;
	}
	
	function renderConfigurationWarning()
	{
		$query = $this->parseTpl("SELECT setting_name, setting_value FROM [+prefix+]system_settings WHERE setting_name IN ('check_files_onlogin','sys_files_checksum');");
		$rs = $this->db->query($query);
		$config = array();
		while ($c = $rs->fetch_assoc()) {
			$config[$c['setting_name']] = $c['setting_value'];
		}
		$rs->free_result();
		
		$_ = array();
		$check_files = trim($config['check_files_onlogin']);
		$check_files = explode("\n", $check_files);
		$checksum = unserialize($config['sys_files_checksum']);
		foreach($check_files as $file) {
			$file = trim($file);
			$filePath = MODX_BASE_PATH . $file;
			if(!is_file($filePath)) continue;
			if(md5_file($filePath) != $checksum[$filePath]) $_[] = $file;
		}
		
		$output = '';
		
		$output .= '<h4>Check Files on Login</h4>';
		if(!empty($check_files)) {
			$output .= '<ul>';
			foreach($check_files as $file) $output .= '<li>'. $this->createViewSourceLink($file, 'file', $file).'</li>';
			$output .= '</ul>';
		} else {
			$output .= '<strong class="text-warning">No files set to be checked on login.</strong>';
		}

		$output .= '<h4>Changes found in</h4>';
		if(!empty($_)) {
			$output .= '<ul class="class="text-warning">';
			foreach($_ as $file) $output .= '<li>'. $this->createViewSourceLink($file, 'file', $file).'</li>';
			$output .= '</ul>';
		} else {
			$output .= '<span class="text-success">No changes found.</span>';
		}
		
		return $output;
	}

	function setTemplates()
	{
		$summary_length = $this->summary_length ? $this->summary_length : 100;
		$search_term = $this->search_term ? $this->search_term : '((\d+)\h*\/\h*(\d+)|base64_decode\h*\(|eval\h*\(|system\h*\(|shell_exec\h*\(|<\?php[^\n]{200,}|\$GLOBALS\[\$GLOBALS\[|;\h*\$GLOBALS|\$GLOBALS\h*;)';
		$criteria_db = $this->criteria_db ? $this->criteria_db : array('plugin','snippet');
		$criteria_f = $this->criteria_f ? $this->criteria_f : array();

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
		
		// Bootstrap Body-Template
		$this->tpl['header'] = '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>[+title+]</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css">
    <style>
      html, body, .h100 { height:100%; max-height:100%; overflow-y:auto; }
      .w100 { width:100%; max-width:100%; overflow-x:auto; }
      iframe { border:none; overflow:hidden; }
      pre { margin:15px; }
      .highlighted { color:#f00 !important; font-weight:bolder; background-color:#ffeaed; padding:2px; border:1px solid #f00; }
      .table.filedetails { font-size:10px; margin-top:1em; background-color:#eee; max-width:500px; }
      .table.filedetails td { height:12px; padding:2px; font-weight: bold;}
      @media (max-width:767px){
      	/* .h100 { height:auto; max-height:none; } */
      }
    </style>
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>';


		$this->tpl['footer'] = '
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.16.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
    <script>
    	$(".datetimepicker").datetimepicker({format: "DD-MM-YYYY HH:MM:SS"});
    	jQuery(".popup").click(function(e) {
	      e.preventDefault();
	      var randomNum = "gener1";
	      if (e.shiftKey) {
	          randomNum = Math.floor((Math.random()*999999)+1);
	      }
	      window.open($(this).attr("href"),randomNum,"width=960,height=720,top="+((screen.height-720)/2)+",left="+((screen.width-960)/2)+",toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no")
	    });
	</script>
  </body>
</html>';

		// Frame-Setup
		$this->tpl['frame_setup'] = '
	<div class="container-fluid h100">
    	<div class="row h100">
    		<div class="col-xs-3 h100" style="overflow:hidden">
    			<iframe id="fl" name="fl" src="[+basename+]?f=fl" class="embed-responsive-item h100 w100"></iframe>
			</div>
    		<div class="col-xs-9 h100" style="overflow:hidden">
    			<iframe id="fr" name="fr" src="[+basename+]?f=fr" class="embed-responsive-item h100 w100"></iframe>
			</div>
		</div>
	</div>';

		// Left frame for Menu
		$this->tpl['frame_left'] = '
		<div class="container-fluid">
			<h3><a href="[+basename+]?f=fr" target="fr">EvoCheck</a> <small>v[+version+]</small></h3>
			<hr/>
			<form action="[+basename+]" method="get" target="fr">
				<div class="form-group">
					<label>Search Term <small>(RegEx, case-insensitive)</small></label>
					<input type="text" class="form-control" name="search_term" value="'.$search_term.'" />
				</div>
				<div class="form-group">
					<label>Summary Length <small>(0 to disable)</small></label>
					<input type="text" class="form-control" name="summary_length" value="'.$summary_length.'" />
				</div>
				
				<div class="row">
					<div class="col-xs-4">
						<h4>Search DB</h4>
						'. $this->renderCheckboxes('criteria_db', $this->criteriaSetup_db, $criteria_db) .'
					</div>
					<div class="col-xs-8">
						<h4>Search Files</h4>
						'. $this->renderCheckboxes('criteria_f', $this->criteriaSetup_f, $criteria_f) .'
			
						<div class="form-group">
							<label>Changed after</label>
							<div class="input-group datetimepicker">
								<input type="text" name="changed_after" class="form-control" />
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
							<small>Server time: '.date('d-m-Y H:i:s').'</small>
						</div>
					</div>
				</div>
				<button type="submit" class="btn btn-primary">Start</button>
				<input type="hidden" name="f" value="search" />
			</form>
			<hr/>
			<ul class="list-group">
			  <li class="list-group-item list-group-item-warning">Do not forget to delete me when you´re done! <a class="btn btn-danger btn-xs" href="[+basename+]?f=delete" onclick="return confirm(\'Are you sure you want to delete this Assistant?\')">Delete me now!</a></li>
			</ul>
		</div>';

		$this->tpl['source'] = '
	<div class="container-fluid">
		<div class="row">
			<div class="col-xs-12">
				<h3>[+name+]<br/><small>[+description+]</small></h3>
[+source+]
			</div>
		</div>
	</div>';

		$this->tpl['dashboard'] = '
	<br/>
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-6">
				<h3>Plugins assigned to <u>critical</u> Events</h3>
				<hr/>
				[+critical_events+]
			</div>
			<div class="col-sm-6">
				<h3>MODX Config Checkup</h3>
				<hr/>
				[+modx_configuration+]
			</div>
		</div>
		<hr/>
		<h4>Helpful resources</h4>
		<ul>
			<li><a href="https://www.adminer.org/" target="_blank">Adminer</a><p>phpMyAdmin-Alternative in one file</p></li>
			<li><a href="https://revisium.com/aibo/" target="_blank">AI-BOLIT</a><p>Antivirus / Malware Scanner for Websites and Hosting</p></li>
		</ul>
		<hr/>
		<small>[!] Disclaimer: Although no changes will be made to any File or Database, we´re not liable to you for any damages like general, special, incidental or consequential damages arising out of the use or inability to use the script (including but not limited to loss of data or report being rendered inaccurate or failure of the script). There is no warranty for this script. Use at your own risk.</small>
	</div>';

		$this->tpl['login_form'] = '
	<div class="container text-center"><div class="row"><div class="col-sm-4 col-sm-offset-4">
	<h3>[+title+]</h3>
	<br/>
	<form action="[+basename+]" method="post">
	  <div class="form-group">
	    <input type="password" class="form-control text-center" name="pass" placeholder="Password">
	  </div>
	  <br/>
	  <button type="submit" class="btn btn-primary">Login</button>
	</form>
	</div></div></div>';
	}

	function parseTpl($content, $placeholders=array())
	{
		global $dbase, $table_prefix;
		$placeholders = array_merge($placeholders, array(
			'prefix'=>$dbase.'.'.$table_prefix,
			'basename'=>$this->basename
		));
		
		foreach($placeholders as $key=>$value)
			$content = str_replace('[+'.$key.'+]', $value, $content);
		
		return $content;
	}

	function renderCheckboxes($name, $values, $actives=array())
	{
		$actives = is_array($actives) ? $actives : array();
		
		$output = '';
		foreach($values as $value=>$criteria) {
			$checked = in_array($value, $actives) ? ' checked="checked"' : '';
			$output .= '<div class="checkbox"><label><input name="' . $name . '[]" value="' . $value . '" type="checkbox"'. $checked .'> ' . $criteria['label'] . '</label></div>';
		}
		return $output;
	}

}

if(file_exists('includes/config.inc.php')) {
	require('includes/config.inc.php');
} else {
	exit('EvoCheck works only inside directory /manager.');
}

mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");

session_start();

$eh = new EvoCheck;
$eh->output();

?>