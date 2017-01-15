<?php
if (IN_MANAGER_MODE != 'true') die('<h1>ERROR:</h1><p>Please use the MODx Content Manager instead of accessing this file directly.</p>');

/////////////////////////////////////////
// critical_events
function renderCriticalEvents($ec) {
// Add more in future by experience
// 18 OnBeforeCacheUpdate
// 19 OnCacheUpdate
// 81 OnManagerAuthentication
// 89 OnManagerPageInit
// 90 OnWebPageInit
// 3  OnWebPagePrerender

	$criticalEvents = array(18, 19, 81, 89, 90, 3);
	$criticalEvents = implode(',', $criticalEvents);

	$select = "SELECT sysevt.name as evtname, sysevt.id as evtid, pe.pluginid, plugs.name, pe.priority, plugs.disabled
	FROM [+prefix+]system_eventnames sysevt
	INNER JOIN [+prefix+]site_plugin_events pe ON pe.evtid = sysevt.id
	INNER JOIN [+prefix+]site_plugins plugs ON plugs.id = pe.pluginid
	WHERE evtid IN ({$criticalEvents})
	ORDER BY sysevt.name,pe.priority";

	$query = $ec->parseSqlStatement($select);

	if ($rs = $ec->db->query($query)) {
		$output = '<table class="table table-bordered table-hover">';
		
		$preEvt             = '';
		while ($plugins = $rs->fetch_assoc()) {
			if ($preEvt !== $plugins['evtid']) {
				$output .= '<tr><td colspan="2"><h5><b>' . $plugins['evtname'] . ' [' . $plugins['evtid'] . ']</b></h5></td></tr>';
			}
			$link = $ec->createViewSourceLink($plugins['name'] . ' [' . $plugins['pluginid'] . ']' . ($plugins['disabled'] ? ' <small>'.$ec->lang['element_disabled'].'</small>' : ''), 'plugin', $plugins['pluginid'], $plugins['disabled']);
			$buttons = $ec->renderElementButtons('plugin', $plugins['pluginid']);
			
			$output .= '<tr id="res_plugin_'.$plugins['pluginid'].'">';
			$output .= '<td style="padding-left:20px">'. $link .'</td>';
			$output .= '<td>'. $buttons .'</td>';
			$output .= '</tr>';
			
			$preEvt = $plugins['evtid'];
		}
		$output .= '</table>';
		$rs->free_result();
	}
	else {
		$output = 'No results';
	}
	return $ec->parsePlaceholders($output);
}

/////////////////////////////////////////
// modx_configuration
function renderCheckFilesOnLogin($ec) {
	$query  = $ec->parseSqlStatement("SELECT setting_name, setting_value FROM [+prefix+]system_settings WHERE setting_name IN ('check_files_onlogin','sys_files_checksum');");
	$rs     = $ec->db->query($query);
	$config = array();
	while ($c = $rs->fetch_assoc()) {
		$config[$c['setting_name']] = $c['setting_value'];
	}
	$rs->free_result();

	$output = '<h4>Check Files on Login</h4>';
	if(!empty($config)) {
		$output .= '<table class="table table-bordered table-hover">';
		$output .= '<tr>';
		$output .= '<th>[%path%]</th>';
		$output .= '<th>[%status%]</th>';
		$output .= '</tr>';
		
		$changedFiles = array();
		$check_files  = trim($config['check_files_onlogin']);
		$check_files  = explode("\n", $check_files);
		$checksum     = unserialize($config['sys_files_checksum']);
		if (!empty($check_files)) {
			foreach ($check_files as $file) {
				$file     = trim($file);
				$filePath = MODX_BASE_PATH . $file;
				if (!is_file($filePath)) continue;

				if (!isset($checksum[$filePath])) {
					$msg = 'Checksum not found';
					$errorClass = 'danger';
				} else if (md5_file($filePath) != $checksum[$filePath]) {
					$msg = 'found changes';
					$errorClass = 'danger';
				} else {
					$msg = 'OK';
					$errorClass = 'success';
				}

				$output .= '<tr>';
				$output .= '<td>'. $ec->createViewSourceLink($file, 'file', $file) .'</td>';
				$output .= '<td>'. $ec->renderStatusLabel($errorClass, $msg) .'</td>';
				$output .= '</tr>';
			}
		} else {
			$output .= '<tr><td><strong class="text-warning">No files set to be checked on login.</strong></td></tr>';
		}
		$output .= '</table>';
	} else {
		$output .= '<p>Settings "check_files_onlogin" and "sys_files_checksum" not found in database (old MODX-version?).</p>';
	}
	
	return $ec->parsePlaceholders($output);
}

/////////////////////////////////////////
// check system settings paths
function renderCheckSystemSettingPaths($ec) {
	$pathSettings = $ec->getSystemSettings(array('filemanager_path','rb_base_dir'));
	
	$output = '<h4>Check System-Setting Paths</h4>';
	$output .= '<table class="table table-bordered table-hover">';
	$output .= '<tr>';
	$output .= '<th>[%system_setting%]</th>';
	$output .= '<th>[%path%]</th>';
	$output .= '<th>is_dir()</th>';
	$output .= '<th>is_readable()</th>';
	$output .= '<th>is_writeable()</th>';
	$output .= '</tr>';
	
	foreach($pathSettings as $setting=>$path) {
		
		$is_dir = is_dir($path) ? 1 : 0;
		$is_readable = is_readable($path) ? 1 : 0; 
		$is_writeable =	is_writeable($path) ? 1 : 0;
			
		$output .= '<tr>';
		$output .= '<td>'. $setting .'</td>';
		$output .= '<td>'. $path .'</td>';
		$output .= '<td>'. $ec->renderStatusLabel($is_dir) .'</td>';
		$output .= '<td>'. $ec->renderStatusLabel($is_readable) .'</td>';
		$output .= '<td>'. $ec->renderStatusLabel($is_writeable) .'</td>';
		$output .= '</tr>';
	}
	$output .= '</table>';
	return $output;
}

/////////////////////////////////////////
echo $this->parseTpl('dashboard', array(
	'critical_events'=>renderCriticalEvents($this),
	'check_files_on_login'=>renderCheckFilesOnLogin($this),
	'system_setting_paths'=>renderCheckSystemSettingPaths($this),
));