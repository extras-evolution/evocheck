<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
class McEvoChecker{
	public $etalonHashArray=array();
	public $etalonSiteHash='';
	public $resultHashArray=array();
	public $resultSiteHash='';
	
	public function __construct(){

	}
	
	public function check($params=null){
		if(!is_array($params))
			$params=array(
				'startFolder'=>MODX_BASE_PATH,
				'excludeExtensions'=>array('jpg','jpeg','png','gif','flv'),
				'excludeFolders'=>array()
			);
		$params['startFolder'] = rtrim($params['startFolder'], '/');
		$z='';
		if(file_exists($params['startFolder'])&&is_dir($params['startFolder'])){
			$contents=scandir($params['startFolder']);
			$z=implode('',$contents);
			foreach($contents as $item)if($item!='.'&&$item!='..'){
				$item=$params['startFolder'].'/'.$item;
				$hash='';
				if(is_dir($item)){
					if(!in_array($item,$params['excludeFolders'])){
						$hash=$this->check(array(
							'startFolder'=>$item,
							'excludeExtensions'=>$params['excludeExtensions'],
							'excludeFolders'=>$params['excludeFolders']
						));
					}
				}
				else{
					if(!in_array(pathinfo($item,PATHINFO_EXTENSION),$params['excludeExtensions'])){
						$hash=md5(file_get_contents($item));
					}
				}
				if($hash!='')$this->resultHashArray[$item]=$hash;
				$z.=$hash;
			}
		}
		$z=md5($z);
		$this->resultSiteHash=$z;
		return $z;
	}
	
	public function compare(){
		$res=$this->resultSiteHash==$this->etalonSiteHash;
		if($res)return $res;
		$res=array('added'=>array(),'removed'=>array(),'changed'=>array());
		foreach($this->resultHashArray as $k=>$v){
			if(!isset($this->etalonHashArray[$k]))$res['added'][$k]=array('',$v);
			else if($v!==$this->etalonHashArray[$k])$res['changed'][$k]=array($this->etalonHashArray[$k],$v);
		}
		foreach($this->etalonHashArray as $k=>$v)
			if(!isset($this->resultHashArray[$k]))$res['removed'][$k]=array($v,'');

		return $res;
	}
}


$checker=new McEvoChecker();


/*
	First run / run on clean system to generate etalon data:
	$checker->check();
	$data=array($checker->resultSiteHash,$checker->resultHashArray);
	file_put_contents('etalonData',json_encode($data));

*/

/*
	Non-first run:
	$data=json_decode(file_get_contents('etalonData'));
	$checker->etalonSiteHash=$data[0];
	$checker->etalonHashArray=$data[1];

*/

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