<?php
class Lang {
	private $file = '';
	private $type = LANG;
	
	public function setFile($file) {
		$this->file = $file;  
	}
	public function getFile() {
		return $this->file;
	}
	public function setType($type) {
		$this->type = $type;
	}
	public function getLanguage($str, $vars) {
		global $Lang;
		include_once APP_LANG_PATH . $this->type . DIRECTORY_SEPARATOR . $this->file . EXT;
		
		if(!empty($vars) && is_array($vars)) {
			return $this->pregLanguage($Lang[$this->file][$str], $vars);
		} else
			return $Lang[$this->file][$str];
	}
	private function pregLanguage($text, $vars = array()) {
		if($vars) {
			foreach ($vars as $k => $v) {
				$rk = $k + 1;
				$text = str_replace('\\'.$rk, $v, $text);
			}
		}
		return $text;
	}
}