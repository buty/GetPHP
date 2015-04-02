<?php


require APP_PATH . "plugin/ckeditor/ckeditor.php";
class Ckedit extends CKEditor {

	function __construct() {
		parent::__construct();
		$this->basePath = PLUGIN_URL . 'ckeditor/';

		$this->returnOutput = true;
		$this->config['width'] = 690;
		$this->config['height'] = 400;
		$this->config['language'] = LANG;  //语言种类
	}

}