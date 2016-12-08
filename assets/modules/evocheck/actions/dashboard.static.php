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
		$insideUl           = 0;
		$preEvt             = '';
		$criticalEventsList = '';
		while ($plugins = $rs->fetch_assoc()) {
			if ($preEvt !== $plugins['evtid']) {
				$criticalEventsList .= $insideUl ? '</ul>' : '';
				$criticalEventsList .= '<h4>' . $plugins['evtname'] . ' [' . $plugins['evtid'] . ']</h4><ul>';
				$insideUl = 1;
			}
			$link = $ec->createViewSourceLink($plugins['name'] . ' [' . $plugins['pluginid'] . ']' . ($plugins['disabled'] ? ' <small>'.$ec->lang['element_disabled'].'</small>' : ''), 'plugin', $plugins['pluginid'], $plugins['disabled']);
			$buttons = $ec->renderElementButtons('plugin', $plugins['pluginid']);
			$criticalEventsList .= '<li id="res_plugin_'.$plugins['pluginid'].'">' . $link . '&nbsp;'. $buttons.'</li>';
			$preEvt = $plugins['evtid'];
		}
		if ($insideUl) $criticalEventsList .= '</ul>';
		$rs->free_result();
	}
	else {
		$criticalEventsList = 'No results';
	}
	return $ec->parsePlaceholders($criticalEventsList);
}

/////////////////////////////////////////
// modx_configuration
function renderModxConfigCheck($ec) {
	$query  = $ec->parseSqlStatement("SELECT setting_name, setting_value FROM [+prefix+]system_settings WHERE setting_name IN ('check_files_onlogin','sys_files_checksum');");
	$rs     = $ec->db->query($query);
	$config = array();
	while ($c = $rs->fetch_assoc()) {
		$config[$c['setting_name']] = $c['setting_value'];
	}
	$rs->free_result();

	$_           = array();
	$check_files = trim($config['check_files_onlogin']);
	$check_files = explode("\n", $check_files);
	$checksum    = unserialize($config['sys_files_checksum']);
	foreach ($check_files as $file) {
		$file     = trim($file);
		$filePath = MODX_BASE_PATH . $file;
		if (!is_file($filePath)) continue;
		if (md5_file($filePath) != $checksum[$filePath]) $_[] = $file;
	}

	$modxConfig = '<h4>Check Files on Login</h4>';
	if (!empty($check_files)) {
		$modxConfig .= '<ul>';
		foreach ($check_files as $file) $modxConfig .= '<li>' . $ec->createViewSourceLink($file, 'file', $file) . '</li>';
		$modxConfig .= '</ul>';
	}
	else {
		$modxConfig .= '<strong class="text-warning">No files set to be checked on login.</strong>';
	}

	$modxConfig .= '<h4>Changes found in</h4>';
	if (!empty($_)) {
		$modxConfig .= '<ul class="class="text-warning">';
		foreach ($_ as $file) $modxConfig .= '<li>' . $ec->createViewSourceLink($file, 'file', $file) . '</li>';
		$modxConfig .= '</ul>';
	}
	else {
		$modxConfig .= '<span class="text-success">No changes found.</span>';
	}
	return $ec->parsePlaceholders($modxConfig);
}

/////////////////////////////////////////
echo $this->parseTpl('dashboard', array(
	'critical_events'=>renderCriticalEvents($this),
	'modx_configuration'=>renderModxConfigCheck($this)
));