<?php

class IndexController extends Controll {
	public function _init() {
		
	}
	public function index() {
        $arr = array('a' => array('b' => 'c'));
        $this->View->assign('arr', $arr);
        $this->View->assign('gogo', 'bb');
        $this->View->assign('bb', 'eeee');
		$this->View->display('Index/index');
	}
	public function captcha() {
		echo $this->Lib->Captcha->captcha_code();
	}
}
