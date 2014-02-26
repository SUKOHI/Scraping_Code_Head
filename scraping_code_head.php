<?php

/*  Dependency: Absolute_Path  */

require_once 'absolute_path.php';

class Scraping_Code_Head extends Scraping_Code {
	
	private $_absolute_path;
	
	public function __construct($subject='') {
	
		parent::__construct($subject);
	
	}
	
	public function getHeadData($base_url) {
		
		$head_data = array();
		$favicon_url = '';
		$feed_urls = array();
		$this->_absolute_path = new Absolute_Path($base_url);
		
		$tag_data = array(
				
				'meta' => array('name', 'content'), 
				'link' => array('rel', 'href'),
				'script' => array('type', 'src')
				
		);

		foreach ($tag_data as $tag_name => $values) {
			
			if($this->specifiedMatchAll('|<'. $tag_name .'([^>]*)>|i', '|<head>(.*?)</head>|i', $matches)) {
				
				$matches_count = count($matches[0]);
				
				for ($i = 0; $i < $matches_count; $i++) {
					
					$tag = $matches[1][$i];
					$tag_key = $values[0];
					$tag_value = $values[1];
					
					if(preg_match('|'. $tag_key .'=["\']([^"\']+)["\']|i', $tag, $matches_2)
							&& preg_match('|'. $tag_value .'=["\']([^"\']+)["\']|i', $tag, $matches_3)) {

						$target_key = $matches_2[1];
						$target_value = $matches_3[1];
						
						if($tag_name == 'link') {
							
							$target_value = $this->getAbsolutePath($target_value);
							
						}
						
						if(is_array($head_data[$tag_name][$target_key])) {
							
							$head_data[$tag_name][$target_key][] = $target_value;
							
						} else if($head_data[$tag_name][$target_key] != '') {
							
							$prev_value = $head_data[$tag_name][$target_key];
							
							$head_data[$tag_name][$target_key] = array(
									$prev_value, 
									$target_value
							);
							
						} else {
							
							$head_data[$tag_name][$target_key] = $target_value;
							
						}
						
						if($tag_name == 'link' 
								&& ($target_key == 'shortcut icon' || $target_key == 'icon')) {
							
							if($favicon_url != '' && $target_key == 'shortcut icon') {
								
								$favicon_url = $target_value;
								
							} else if($favicon_url == '') {
								
								$favicon_url = $target_value;
								
							}
							
						} else if($tag_name == 'link' 
								&& $target_key == 'alternate'
								&& preg_match('!type="application/(rss|atom)\+xml"!i', $tag)) {
							
							$feed_urls[] = $target_value;
							
						}
						
					}
					
				}
				
			}
			
		}
		
		$head_data['favicon_url'] = $favicon_url;
		$head_data['feed_urls'] = $feed_urls;
		
		return $head_data;
		
	}
	
	private function getAbsolutePath($target_path) {

		return $this->_absolute_path->getResult($target_path);
		
	}
	
}
