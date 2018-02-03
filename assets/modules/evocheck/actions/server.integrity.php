<?php
if (IN_MANAGER_MODE != 'true') die('<h1>ERROR:</h1><p>Please use the EVO Content Manager instead of accessing this file directly.</p>');

class integrityCheck {
	
	var $totalMissing = 0;
	var $template = '';
	var $imageName = '';
	var $fileSet = array();
	var $excludedDirectories = array();
	var $placeholders = array();
	var $ec = NULL;
	
	function __construct(&$ec) {
		$this->ec = $ec;
	}

	function init() {
		
		$action = isset($_GET['ec_subaction']) ? $_GET['ec_subaction'] : 'main';
		
		switch($action) {
			case 'main':
				$this->template = 'server.integrity.main';
				$this->placeholders['images_directory'] = $this->ec->base_path.'integrity/';
				$this->placeholders['images_list'] = $this->createImagesList();
				$this->placeholders['image_name'] = date('Y-m-d_H-i-s');
				$this->placeholders['inputs_excluded'] = $this->createExcludedInputs();
				$this->placeholders['alert_choose_image'] = addslashes($this->ec->lang['integrity_alert_choose_image']);
				break;
				
			case 'create':
				$this->template = 'server.integrity.create';
				
				$this->excludedDirectories = $this->prepareExcludedDirs($_GET['excluded']);
				$this->imageName = $_GET['ec_imagename'];
				$imageFilename = $this->ec->integrity_dir . $this->imageName .'.json';
				
				if(file_exists($imageFilename)) {
					$this->placeholders['message'] = '[%integrity_file_exists%]';
					break;
				}
				
				$this->createSHA1(MODX_BASE_PATH);
				
				$imageContent = array(
					'excludedDirs'=>$this->excludedDirectories,
					'files'=>$this->fileSet
				);
				
				if(file_put_contents($imageFilename, json_encode($imageContent))) {
					$this->placeholders['message'] = '[%integrity_create_success%]';
					$this->placeholders['result']  = '<a href="'.$this->ec->base_path.'integrity/'.$this->imageName.'.json'.'" target="_blank">'.$this->imageName.'.json</a>';
				} else {
					$this->placeholders['message'] = '[%integrity_create_error%]';
				}
				break;
				
			case 'compare':
				$this->template = 'server.integrity.compare';
				$this->imageName = $_GET['ec_imagecompare'];
				
				$imageFilename = $this->ec->integrity_dir . $this->imageName;
				
				if(!is_readable($imageFilename)) {
					$this->placeholders['message'] = '[%integrity_compare_result_error%]';
					break;
				}
				
				$imageFileset = $this->ec->isJson(file_get_contents($imageFilename), true);
				
				if($imageFileset === false) {
					$this->placeholders['message'] = '[%integrity_compare_result_error%]';
				} else {
					$this->placeholders['message'] = '[%integrity_compare_result_intro%]';
					$this->excludedDirectories = isset($imageFileset['excludedDirs']) ? $imageFileset['excludedDirs'] : array();
					$this->createSHA1(MODX_BASE_PATH);
					$this->placeholders['result'] = $this->compareImage($imageFileset['files']);
				}
				break;
		}
	}

	function compareImage($imageFileset) {
		$result = array(
			'integer'=>0,
			'changed'=>0,
			'notfound'=>0,
			'new'=>0,
			'notreadable'=>0,
		);

		$resultFiles = array(
			'changed'=>array(),
			'notfound'=>array(),
			'new'=>array(),
			'notreadable'=>array()
		);
		
		foreach($this->fileSet as $file=>$checksum) {
			if(!isset($imageFileset[$file])) {
				$result['new'] += 1;
				$resultFiles['new'][] = $file;
			} else if(!file_exists(MODX_BASE_PATH.$file)) {
				$result['notfound'] += 1;
				$resultFiles['notfound'][] = $file;
			} else if(!is_readable(MODX_BASE_PATH.$file)) {
				$result['notreadable'] += 1;
				$resultFiles['notreadable'][] = $file;
			} else if($imageFileset[$file] != $checksum) {
				$result['changed'] += 1;
				$resultFiles['changed'][] = $file;
			} else {
				$result['integer'] += 1;
			}
		}

		$result['dirs_excluded'] = '<li>'.join($this->excludedDirectories, '</li><li>').'</li>';
		
		foreach($resultFiles as $type=>$files) {
			if(!empty($files)) {
				$result['files_' . $type] = join($files, "\r\n");
			} else {
				$result['files_' . $type] = '[%integrity_compare_no_result%]';
			}
		}
		
		return $this->ec->parseTpl('server.integrity.compare.result', $result); 
	}
	
	function createImagesList() {
		$imageFiles = array();
		$dir = new DirectoryIterator($this->ec->integrity_dir);
		foreach ($dir as $file) {
			if ($file->isDot()) continue;

			$path = $file->getPathname();
			$path = str_replace('\\','/',$path);

			if (is_dir($path)) {
				continue;
			} else if(is_file($path)){
				$imageFiles[] = $file->getFilename();
			}
		}
		
		rsort($imageFiles);
		
		$output = '';
		foreach($imageFiles as $file) {
			$output .= '
				<div class="radio">
                    <label><input name="ec_imagecompare" value="'.$file.'" type="radio"> '.$file.'</label>
                </div>
				';
		}
		return $output;
	}
	
	function createExcludedInputs($dirs=NULL) {
		if($dirs === NULL) {
			// Default excluded directories
			$dirs = array(
				'assets/cache',
				'assets/.thumbs',
				'assets/backup'
			);
			// Add additional directories on localhost by default for development/testing
			if(in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', "::1"))){
				$dirs[] = '.git';
				$dirs[] = '.idea';
			}
		}
		
		$output = '';
		$first = true;
		foreach($dirs as $dir) {
			$output .= '
			<div class="input-group">
                <input type="text" name="excluded[]" value="'.$dir.'" class="form-control" />
                <span class="input-group-btn"><button class="btn btn-primary add_field">+</button></span>
                '. ($first != true ? '<span class="input-group-btn"><button class="btn btn-primary remove_field">-</button></span>' : '' ).'
            </div>';
			$first = false;
		}
		return $output;
	}
	
	function createSHA1($startDir) {
		$dir = new DirectoryIterator($startDir);
		foreach ($dir as $file) {
			if ($file->isDot()) continue;
			
			$path = $file->getPathname();
			$path = str_replace('\\','/',$path);
			
			$relativePath = str_replace(MODX_BASE_PATH, '', $path);
			if (in_array($relativePath, $this->excludedDirectories)) continue;

			if (is_dir($path)) {
				$this->createSHA1($path);
			} else if(is_file($path)){
				$this->fileSet[$relativePath] = sha1_file($path);
			}
		}
	}
	
	/* Remove slashes from beginning and end, replace \ by / */
	function prepareExcludedDirs($dirs) {
		foreach($dirs as $key=>$dir) {
			$dir = str_replace(array('\\'), array('/'), $dir);
			$dir = trim($dir, '/');
			$dirs[$key] = $dir;
		}
		return $dirs;
	}
	
	function renderCreateIndexHtmResults($ec) {
		if(!$this->option) return '';
		
		$output = '<table class="table table-condensed table-hover">';
		$output .= '<tr><td>[%indexhtm_files_added%]</td><td>'.$this->results['files']['added'].'</td></tr>';
		$output .= '<tr><td>[%indexhtm_files_altered%]</td><td>'.$this->results['files']['altered'].'</td></tr>';
		$output .= '<tr><td>[%indexhtm_files_removed%]</td><td>'.$this->results['files']['removed'].'</td></tr>';
		$output .= '<tr><td>[%indexhtm_files_duplicates_removed%]</td><td>'.$this->results['files']['duplicates_removed'].'</td></tr>';
		$output .= '<tr><td>[%indexhtm_files_error%]</td><td>'.$this->results['files']['error'].'</td></tr>';
		$output .= '<tr><td>[%indexhtm_files_skipped%] &gt; '.$this->filterSize.' Bytes</td><td>'.$this->results['files']['skipped'].'</td></tr>';
		$output .= '</table>';
		
		return $ec->parsePlaceholders($output);
	}
}

$integrityCheck = new integrityCheck($this);
$integrityCheck->init();

echo $this->parseTpl($integrityCheck->template, $integrityCheck->placeholders);