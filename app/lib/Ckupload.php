<?php


require APP_PATH . "plugin/ckfinder/ckfinder.php";
class Ckupload extends CKFinder {

	function __construct() {
		parent::__construct();
		$this->BasePath = '/' . APPNAME . '/' . PLUGIN_DIR_NAME . '/ckfinder/'; //非HTTP开头
		
	}

}