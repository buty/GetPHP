<?php

Class Input {
	
	/* never allowed, string replacement */
	private $never_allowed_str = array(
									'document.cookie'	=> '[removed]',
									'document.write'	=> '[removed]',
									'.parentNode'		=> '[removed]',
									'.innerHTML'		=> '[removed]',
									'window.location'	=> '[removed]',
									'-moz-binding'		=> '[removed]',
									'<!--'				=> '&lt;!--',
									'-->'				=> '--&gt;',
									'<![CDATA['			=> '&lt;![CDATA['
									);
	/* never allowed, regex replacement */
	private $never_allowed_regex = array(
										"javascript\s*:"			=> '[removed]',
										"expression\s*(\(|&\#40;)"	=> '[removed]', // CSS and IE
										"vbscript\s*:"				=> '[removed]', // IE, surprise!
										"Redirect\s+302"			=> '[removed]'
									);
	
	function __construct() {
		if ($_GET) {
		    $_GET = daddslashes($_GET);
		}
		if ($_POST) {
			$_POST = daddslashes($_POST);
		}
		if ($_COOKIE) {
			$_COOKIE = daddslashes($_COOKIE);
		}

		//url危险字符过滤
		$dangstr=array('<','>','\'','"',' ','+','\\');
		if ($_SERVER) {
		    foreach($_SERVER as $key=>$val) {
		        if ($val) {
		            $_SERVER[$key]=str_replace($dangstr,'',$val);
		        }
		    }
		}
		
		if ($_GET) {  //URL中不能带空格
		    foreach($_GET as $key=>$val) {
		        if ($val) {
		            $_GET[$key]=str_replace($dangstr,'',$val);
		        }
		    }
		}
		
		$_POST = $this->safeword($_POST);

		
	}
	
	function safeword($str) {
		if (is_array($str)){
			while (list($key) = each($str)) {
				$str[$key] = $this->safeword($str[$key]);
			}
			return $str;
		}
		foreach ($this->never_allowed_str as $key => $val) {
			$str = str_replace($key, $val, $str);
		}

		foreach ($this->never_allowed_regex as $key => $val) {
			$str = preg_replace("#".$key."#i", $val, $str);
		}
		return $str;
	}
	
	function getdata($name) {
		if(isset($_POST[$name]))	return daddslashes($_POST[$name]);
		if(isset($_GET[$name]))		return daddslashes($_GET[$name]);
		if(isset($_COOKIE[$name]))	return daddslashes($_COOKIE[$name]);
		return NULL;
	}
	
	function checksubmit($name) {
		return isset($_POST[$name]) && $this->getdata(FORMHASH_NAME) == FORMHASH ? true : false;
	}
}