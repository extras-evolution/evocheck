<?php
/*
Non first run, check current checksums, compare them to etalon and echo difference
*/

require_once('McEvoChecker.class.php');
$checker=new McEvoChecker();

$data=json_decode(file_get_contents('etalonData'));
$checker->etalonSiteHash=$data[0];
$checker->etalonHashArray=$data[1];

$checker->check();
$res=$checker->compare();
$content='<h1>Check passed. Checked '.count($checker->resultHashArray).' files and dirs</h1>';
if($res!==true){
	if(count($res['added'])>0){
		$content.='<h2>Added files list</h2><table><thead><tr><th>Filename</th><th>Etalon file hash</th><th>Checked file hash</th></tr></thead><tbody>';
		foreach($res['added'] as $k=>$v)
			$content.='<tr><th>'.$k.'</th><td>'.$v[0].'</td><td>'.$v[1].'</td></tr>';
		$content.='</tbody></table>';
	}
	if(count($res['changed'])>0){
		$content.='<h2>Changed files list</h2><table><thead><tr><th>Filename</th><th>Etalon file hash</th><th>Checked file hash</th></tr></thead><tbody>';
		foreach($res['added'] as $k=>$v)
			$content.='<tr><th>'.$k.'</th><td>'.$v[0].'</td><td>'.$v[1].'</td></tr>';
		$content.='</tbody></table>';
	}
	if(count($res['removed'])>0){
		$content.='<h2>Removed files list</h2><table><thead><tr><th>Filename</th><th>Etalon file hash</th><th>Checked file hash</th></tr></thead><tbody>';
		foreach($res['added'] as $k=>$v)
			$content.='<tr><th>'.$k.'</th><td>'.$v[0].'</td><td>'.$v[1].'</td></tr>';
		$content.='</tbody></table>';
	}
}
else $content.='<h2>All OK!</h2>';

echo $content;