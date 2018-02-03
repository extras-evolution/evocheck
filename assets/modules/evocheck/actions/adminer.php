<?php
if (IN_MANAGER_MODE != 'true') die('<h1>ERROR:</h1><p>Please use the EVO Content Manager instead of accessing this file directly.</p>');

global $database_user;

echo $this->parseTpl('adminer', array(
	'adminer_src'=>$this->base_path.'/actions/adminer.loader.php',
	'username'=>$database_user
));