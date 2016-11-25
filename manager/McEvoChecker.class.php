<?php
/*
Class for per-file and overall checksum generation and compare
Has to run from within manager - uses constant MODX_BASE_PATH
*/

class McEvoChecker{
	public $etalonHashArray=array();
	public $etalonSiteHash='';
	public $resultHashArray=array();
	public $resultSiteHash='';
	
	public function __construct(){

	}
	
	/*
	Fills per-file checksums array and overall checksum
			
	$params=array(
		'startFolder' - root folder to scan
		'excludeExtensions' - array of file types NOT TO SCAN
		'excludeFolders' - array of folder NOT TO SCAN, relatively to MODX_BASE_PATH
	)
	
	*/
	public function check($params=null){
		if(!is_array($params))
			$params=array(
				'startFolder'=>MODX_BASE_PATH,
				'excludeExtensions'=>array('jpg','jpeg','png','gif','flv'),
				'excludeFolders'=>array()
			);
		$params['startFolder']=rtrim($params['startFolder'],'/');
		$z='';
		if(file_exists($params['startFolder'])&&is_dir($params['startFolder'])){
			$contents=scandir($params['startFolder']);
			$z=implode('',$contents);
			foreach($contents as $item)if($item!='.'&&$item!='..'){
				$item=$params['startFolder'].'/'.$item;
				$hash='';
				$itemRel=str_replace(MODX_BASE_PATH,'',$item);
				if(is_dir($item)){
					if(!in_array($itemRel),$params['excludeFolders'])){
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
				if($hash!='')$this->resultHashArray[$itemRel]=$hash;
				$z.=$hash;
			}
		}
		$z=md5($z);
		$this->resultSiteHash=$z;
		return $z;
	}
	
	/*
	returns true if generated overall checksum equals to etalon
	or otherwise an array of added,removed and changed files with their checksums
	
	*/
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
