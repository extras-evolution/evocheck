<?php
if (IN_MANAGER_MODE != 'true') die('<h1>ERROR:</h1><p>Please use the MODx Content Manager instead of accessing this file directly.</p>');

class ecIndexHtm {
	var $totalMissing = 0;
	var $totalFound = 0;
	var $excludedDirectories = array('/', '/manager');
	var $create = 0;
	var $content = '';
	var $option = '';
	var $filter = 1;
	var $filterSize = 200;
	var $results = array();
	
	function __construct() {
		$this->create  = isset($_GET['ec_create'])  ? $_GET['ec_create']  : 0;
		$this->content = isset($_GET['ec_content']) ? $_GET['ec_content'] : '';
		$this->option  = isset($_GET['ec_option'])  ? $_GET['ec_option']  : '';
		$this->filter  = isset($_GET['ec_filter'])  ? 1 : 0;
		$this->filterSize = isset($_GET['ec_size']) ? $_GET['ec_size']  : 200;
		
		$this->results = array(
			'files'=>array(
				'added'=>0,
				'altered'=>0,
				'removed'=>0,
				'duplicates_removed'=>0,
				'error'=>0,
				'skipped'=>0,
			)
		);
	}

	function search($startdir) {
		$dir = new DirectoryIterator($startdir);
		foreach ($dir as $file) {
			if ($file->isDot()) continue;
			
			$path = $file->getPathname();
			$path = str_replace('\\','/',$path);
			
			$relativePath = str_replace(MODX_BASE_PATH, '', $path);
			if (in_array($relativePath, $this->excludedDirectories)) continue;
			
			if (is_dir($path)) {
				$fileExists = file_exists($path . '/index.htm') || file_exists($path . '/index.html');
				$this->createIndexHtm($path, $fileExists);
				$this->search($path);
			}
		}
	}
	
	function createIndexHtm($path, $fileExists) {
		if($this->option && !$this->permissionToAlterIndexHtml($path)) {
			// Filesize exceeds limit
			$this->results['files']['skipped'] += 1;
			return;
		}
		switch($this->option) {
			case 'add':
				if (!$fileExists) {
					if(file_put_contents($path . '/index.html', $this->content)) {
						$this->totalFound += 1;
						$this->results['files']['added'] += 1;
						// Remove duplicate
						if (file_exists($path . '/index.htm')) {
							unlink($path . '/index.htm');
							$this->results['files']['duplicates_removed'] += 1;
						}
					} 
					else {
						$this->results['files']['error'] += 1;
						$this->totalMissing += 1;
					}
				}
				break;
			case 'overwrite':
				if(file_put_contents($path . '/index.html', $this->content)) {
					$this->results['files']['added'] += 1;
					// Remove duplicate
					if (file_exists($path . '/index.htm')) {
						unlink($path . '/index.htm');
						$this->results['files']['duplicates_removed'] += 1;
					}
					$this->totalFound += 1;
				} else {
					$this->results['files']['error'] += 1;
					$this->totalMissing += 1;
				};
				break;
			case 'remove':
				if (file_exists($path . '/index.htm'))  {
					unlink($path . '/index.htm');
					$this->results['files']['removed'] += 1;
				}
				if (file_exists($path . '/index.html')) {
					unlink($path . '/index.html');
					$this->results['files']['removed'] += 1;
				}
				$this->totalMissing += 1;
				break;
			default:
				if ($fileExists) $this->totalFound += 1;
				else $this->totalMissing += 1;
		}
	}
	
	function permissionToAlterIndexHtml($path) {
		if($this->filter) {
			if (file_exists($path . '/index.html')) {
				if(filesize($path . '/index.html') > $this->filterSize) {
					return false;
				}
			} else {
				return true;
			}
		}
		return true;
	}
	
	function renderExcludedDirList() {
		$output = '<br/><ul>';
		foreach($this->excludedDirectories as $dir) {
			$output .= '<li>'.$dir.'</li>';
		}
		$output .= '</ul>';
		return $output;
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

$ecIndexHtm = new ecIndexHtm;
$ecIndexHtm->search(MODX_BASE_PATH);

echo $this->parseTpl('server.indexhtm', array(
	'totalFound'=>$ecIndexHtm->totalFound,
	'totalMissing'=>$ecIndexHtm->totalMissing,
	'excluded_directories'=>$ecIndexHtm->renderExcludedDirList(),
	'create_results'=>$ecIndexHtm->renderCreateIndexHtmResults($this)
));