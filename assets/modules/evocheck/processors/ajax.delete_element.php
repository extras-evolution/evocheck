<?php
if (IN_MANAGER_MODE != 'true') die('<h1>ERROR:</h1><p>Please use the MODx Content Manager instead of accessing this file directly.</p>');

global $modx;
if ($modx->getLoginUserID() != 1) {	die('<h1>ERROR:</h1>No permission.'); }

$type = $_POST['ec_type'];
$file = $_POST['ec_id'];
$id = intval($_POST['ec_id']);

$return = array();
$error = array();


	$cssId = 'res_'.$type.'_'.$id;
	switch ($type) {
		case 'plugin':
			$this->db->query($this->parseSqlStatement("DELETE FROM [+prefix+]site_plugins WHERE id='{$id}';"));
			if($this->db->error) $error[] = $this->db->error;
			$this->db->query($this->parseSqlStatement("DELETE FROM [+prefix+]site_plugin_events WHERE pluginid='{$id}';"));
			if($this->db->error) $error[] = $this->db->error;
			$return['removeResultID'] = $cssId;
			break;
		case 'snippet':
			$this->db->query($this->parseSqlStatement("DELETE FROM [+prefix+]site_snippets WHERE id='{$id}';"));
			if($this->db->error) $error[] = $this->db->error;
			$return['removeResultID'] = $cssId;
			break;
		case 'template':
		case 'chunk':
		case 'module':
		case 'content':
			$return['alert'] = 'Deletion not supported yet.';
			break;
		case 'file':
			if(is_readable($this->basedir.$file) && unlink($this->basedir.$file)) {
				$return['removeResultID'] = 'file_'.md5($file);
			} else {
				$error[] = 'File could not be deleted: '.$file;
			}
			break;
		default:
			return 'Wrong type or ID';
	}


// Send 
if(empty($error)) {
	$return['success'] = 1;
} else {
	$return['alert'] = 'Error(s): '. implode("\n", $error);
};

exit(json_encode($return));