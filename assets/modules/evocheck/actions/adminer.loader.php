<?php

define('MODX_API_MODE', true);
include_once("../../../../index.php");

$modx->db->connect();
if (empty ($modx->config)) {
	$modx->getSettings();
}
if(!isset($_SESSION['mgrValidated'])){
	die();
}

function adminer_object() {

	class AdminerSoftware extends Adminer {

		function name() {
			// custom name in title and heading
			return 'Adminer';
		}

		function permanentLogin() {
			// key used for permanent login
			return "0IFLzgoctTgpS6WaCsGOKisWkwmXoFMA";
		}

		function credentials() {
			global $database_user, $database_password, $database_server;
			// server, username and password for connecting to database
			return array($database_server, $database_user, $database_password);
		}

		function database() {
			global $dbase;
			// database name, will be escaped by Adminer
			return str_replace('`', '', $dbase);
		}
		
		function headers() {
			header("X-Frame-Options: SameOrigin");
		}
	}

	return new AdminerSoftware;
}

include('../inc/adminer.php');