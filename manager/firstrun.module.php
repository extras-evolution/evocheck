<?php
/*
	First run / run on clean system to generate etalon data:
*/
require_once('McEvoChecker.class.php');
$checker=new McEvoChecker();
$checker->check();
$data=array($checker->resultSiteHash,$checker->resultHashArray);
file_put_contents('etalonData',json_encode($data));
