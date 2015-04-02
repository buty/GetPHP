<?php 


class View extends GetPHP {
	private $data = array();
	//内部模板处理变量
	private $block = array(
		'i'		=> 0,
		'block_search'	=>	array(),
		'block_replace'	=>	array(),
		'sub_tpls'		=>	array()
	);
	//显示模板
	public function display($tpl = '') {
		if(empty($tpl)) {
			$tpl = $this->Dispatcher->getControllName() . '/' . $this->Dispatcher->getActionName();
		}

		$tpl = TEMPLATE . '/'. THEME . '/' . $tpl;
		$obj_file = $this->_getObjFilePath($tpl);

		//if(!file_exists($obj_file)) {
		if(1) {	
			$this->parseTemplate($tpl);
		}
		//释放变量
		extract($this->data, EXTR_OVERWRITE); //重写之前的变量
		include $obj_file;
	}

	public function assign($name, $params) {
		if(is_string($name))
			$this->data[$name] = $params;
	}

	//跳转页面
	public function showMessage($message, $url_forward='', $second=1) {
		if($url_forward) {
			$url_forward = SITE_URL . $url_forward;
			$message = "<a href=\"$url_forward\">$message</a><script>setTimeout(\"window.location.href ='$url_forward';\", ".($second*1000).");</script>";
		}
		$this->assign('url_forward',$url_forward);
		$this->assign('message',$message);
		$this->display('showmessage');
		exit();
	}

	private function _handleSubTemplate($file_cont) {
		$file_cont = preg_replace_callback(
			"/\<\!\-\-\{template\s+([a-z0-9_\/]+)\}\-\-\>/i", 
			function($matches) {
				return $this->_readTemplate($matches[1]);
			},
			$file_cont);
		return $file_cont;
	}

	private function _createFormhash($file_cont) {
		$file_cont = preg_replace("/<\/form>/s", "<input type='hidden' name='".FORMHASH_NAME."' value='{FORMHASH}' /></form>", $file_cont);
		return $file_cont;
	}

	private function _handeInternalFunc($file_cont) {
		//语言更换, 将不带参数的语言先替换
		$file_cont = preg_replace_callback("/\<\!\-\-\{([^(]+)\((.+?)\)\}\-\-\>/i", 
			function($matches) {
				$method = "_" . $matches[1] . "Tags";
				return $this->$method($matches[2]);
			}, 
			$file_cont);

		return $file_cont;
	}

	private function _handleVariables($file_cont) {
		//简单变量处理
		$file_cont = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $file_cont);
		//去掉多余的换行和缩进
		$file_cont = preg_replace("/([\n\r]+)\t+/s", "\\1", $file_cont);
		//$a.b形式转换为$a['b']
		$file_cont = preg_replace("/(\\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\.([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/s", "\\1['\\2']", $file_cont);
		//对于{$a}形式的变量输出
		$file_cont = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?php echo \\1?>", $file_cont);

		return $file_cont;
	}

	private function _handleLogic($file_cont) {
		$file_cont = preg_replace("/\{elseif\s+(.+?)\}/is", "<?php } elseif(\\1) { ?>", $file_cont);
		$file_cont = preg_replace("/\{else\}/is", "<?php } else { ?>", $file_cont);

		return $file_cont;
	}

	private function _handleLoop($file_cont) {
		for($i = 0; $i < 6; $i++) {
			$file_cont = preg_replace("/\{loop\s+(\S+)\s+(\S+)\}(.+?)\{\/loop\}/is", "<?php if(is_array(\\1)) { foreach(\\1 as \\2) { ?>\\3<?php } } ?>", $file_cont);
			$file_cont = preg_replace("/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}(.+?)\{\/loop\}/is", "<?php if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>\\4<?php } } ?>", $file_cont);
			$file_cont = preg_replace("/\{if\s+(.+?)\}(.+?)\{\/if\}/is", "<?php if(\\1) { ?>\\2<?php } ?>", $file_cont);
		}

		return $file_cont;
	}

	private function _handleConstant($file_cont) {
		$file_cont = preg_replace("/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/s", "<?php echo \\1?>", $file_cont);

		return $file_cont;
	}

	private function _handleBlockAndTidy($tpl, $file_cont) {
		//替换
		if(!empty($this->block['block_search'])) {
			$file_cont = str_replace($this->block['block_search'], $this->block['block_replace'], $file_cont);
		}
		//换行
		$file_cont = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $file_cont);
		
		//附加处理
		$file_cont = "<?php if(!defined('APPNAME')) exit('Access Denied');?><?php subtplcheck('".implode('|', $this->block['sub_tpls'])."', '".time()."', '$tpl');?>".$file_cont;

		return $file_cont;
	}

	private function _writeCacheFile($tpl, $file_cont) {
		$obj_file = $this->_getObjFilePath($tpl);
		//附加处理
		$file_cont = "<?php if(!defined('APPNAME')) exit('Access Denied');?><?php subtplcheck('".implode('|', $this->block['sub_tpls'])."', '".time()."', '$tpl');?>".$file_cont;
		
		//write
		if(!swritefile($obj_file, $file_cont)) {
			exit("File: $objfile can not be write!");
		}		
	}

	private function _getObjFilePath($tpl) {
		return APP_TEMPLATE_CACHE_PATH . str_replace('/','_',$tpl) . EXT;
	}

	private function _getTemplateCont($tpl) {
		$tpl_file = APP_PATH . $tpl . HTML_EXT;

		$obj_file = $this->_getObjFilePath($tpl);

		$template = sreadfile($tpl_file);
		if(empty($template)) {
			exit("Template file : $tpl_file Not found or have no access!");
		}

		return $template;
	}

	//解析
	public function parseTemplate($tpl) {
		//包含模板
		$this->block['sub_tpls'] = array($tpl);

		//读取模板内容
		$template = $this->_getTemplateCont($tpl);
		
		//模板
		$template = $this->_handleSubTemplate($template);
		//处理子页面中的代码,也就是说最多套二套
		$template = $this->_handleSubTemplate($template);
		
		//对于form表单的验证处理
		$template = $this->_createFormhash($template);
		//处理内部函数
		$template = $this->_handeInternalFunc($template);
		
		//变量
		$template = $this->_handleVariables($template);
		
		//逻辑
		$template = $this->_handleLogic($template);

		//循环
		$template = $this->_handleLoop($template);

		//常量
		$template = $this->_handleConstant($template);
		
		//处理block
		$template = $this->_handleBlockAndTidy($tpl, $template);
		
		//写入文件
		$this->_writeCacheFile($tpl, $template);
	}

	private function _dateTags($parameter) {
		$this->block['i']++;
		$search = "<!--DATE_TAG_{$this->block['i']}-->";
		$this->block['block_search'][$this->block['i']] = $search;
		$this->block['block_replace'][$this->block['i']] = "<?php echo date($parameter); ?>";
		return $search;
	}

	private function _trimSpaceAndColm(&$item, $key) {
		$item = str_replace(array('"', "'", ' '), '', $item);
	}

	private function _handleStrParamsToArray($params) {
		$params = trim($params);
		$arr = @explode(',', $params);
		if(!empty($arr) && is_array($arr)) {
			array_walk($arr, array($this, '_trimSpaceAndColm')); //去除字符之间的空格
		}

		return $arr;
	}

	private function _langTags($parameter) {
		$search = call_user_func_array('langpreg', $this->_handleStrParamsToArray($parameter));
		//$search = langpreg($parameter);
		return $search;
	}
	//对于带参数的语言包解析
	private function _langpregTags($parameter) {
		$this->block['i']++;
		$search = "<!--LANG_TAG_{$this->block['i']}-->";
		$this->block['block_search'][$this->block['i']] = $search;
		$this->block['block_replace'][$this->block['i']] = "<?php echo langpreg($parameter); ?>";
		return $search;
	}

	private function _readTemplate($name) {
		$tpl = substr($name, 0, 1) == '/' ? $name : TEMPLATE . '/'. THEME . '/' . $name;
		$tplfile = APP_PATH . $tpl . HTML_EXT;
		$this->block['sub_tpls'][] = $tpl;
		$content = sreadfile($tplfile);
		return $content;
	}
	
}


?>