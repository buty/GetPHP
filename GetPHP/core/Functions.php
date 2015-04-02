<?php 

function load_class($class, $prefix = ''){
	static $_classes = array();

	// Does the class exist?  If so, we're done...
	if (isset($_classes[$class])) {
		return $_classes[$class];
	}

	$name = FALSE;
	// Look for the class first in the native GetPHP/core folder
	// then in the local app/core folder
	foreach(array(GETPHP_CORE_PATH, GETPHP_LIB_PATH, APP_LIB_PATH, APP_MODEL_PATH, APP_CONTROLL_PATH) as $path) {
		if (file_exists($path.$class.EXT)) {
			$name = $prefix.$class;

			if (class_exists($name) === FALSE) {
				require($path.$class.EXT);
			}
			
			break;
		}
	}

	// Did we find the class?
	if ($name === FALSE){
		exit('Unable to locate the specified class: '.$class.EXT);
	}

	// Keep track of what we just loaded
	is_loaded($class);

	$_classes[$class] = new $name();
	return $_classes[$class];
}
// 获取配置值
function load_config($name=null, $value=null) {
    static $_config = array();
    // 无参数时获取所有
    if (empty($name))
        return $_config;
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtolower($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : null;
            $_config[$name] = $value;
            return;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0] = strtolower($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // 批量设置
    if (is_array($name))
        return $_config = array_merge($_config, array_change_key_case($name));
    return null; // 避免非法参数
}
function is_loaded($class = '') {
	static $_is_loaded = array();

	if ($class != '') {
		$_is_loaded[strtolower($class)] = $class;
	}
	
	return $_is_loaded;
}
//获取文件内容
function sreadfile($filename) {
	$content = '';
	if(function_exists('file_get_contents')) {
		@$content = file_get_contents($filename);
	} else {
		if(@$fp = fopen($filename, 'r')) {
			@$content = fread($fp, filesize($filename));
			@fclose($fp);
		}
	}
	return $content;
}
//写入文件
function swritefile($filename, $writetext, $openmod='w') {
	if(@$fp = fopen($filename, $openmod)) {
		flock($fp, 2);
		fwrite($fp, $writetext);
		fclose($fp);
		return true;
	} else {
		die("File: $filename write error.");
		return false;
	}
}
//子模板更新检查
function subtplcheck($subfiles, $mktime, $tpl) {
	if(mt_rand(0, 2) == 1) {

		$subfiles = explode('|', $subfiles);
		
		foreach ($subfiles as $subfile) {
			$tplfile = APP_PATH . $subfile . HTML_EXT;

			@$submktime = filemtime($tplfile);
			if($submktime > $mktime) {
				$view = load_class('View');
				$view->parseTemplate($tpl);
				break;
			}
		}
	}
}
//处理语言
function langpreg($file, $str, $vars = array()) {
	$lang = load_class('Lang');
	if(!$lang || $lang->getFile() != $file) {
		$lang->setFile($file);
	}
	return $lang->getLanguage($str, $vars);
}
//判断字符串是否存在
function strexists($haystack, $needle) {
	return !(strpos($haystack, $needle) === FALSE);
}
//表前后缀
function tname($name) {
	return load_config('DBprefix').$name.load_config('DBsuffix');
}
//字符串解密加密
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

    $ckey_length = 4;    // 随机密钥长度 取值 0-32;
                // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
                // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
                // 当此值为 0 时，则不产生随机密钥

    $key = md5($key ? $key : AUTHCODE);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}
function daddslashes($string, $force = 0, $strip = FALSE) {
	if(!get_magic_quotes_gpc() || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = daddslashes($val, $force);
			}
		} else {
			$string = addslashes($strip ? stripslashes($string) : $string);
		}
	}
	return $string;
}
//取消HTML代码
function shtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = shtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
			str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}
//去掉slassh
function sstripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = sstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}
//连接字符
function simplode($ids) {
	return "'".implode("','", $ids)."'";
}

//获取在线IP
function getonlineip($format=0) {
	$global_ip = '';
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$onlineip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$onlineip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
	$global_ip = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';

	if($format) {
		$ips = explode('.', $global_ip);
		for($i=0;$i<3;$i++) {
			$ips[$i] = intval($ips[$i]);
		}
		return sprintf('%03d%03d%03d', $ips[0], $ips[1], $ips[2]);
	} else {
		return $global_ip;
	}
}
function getstr($string, $length, $in_slashes=0, $out_slashes=0, $html=0) {
	$string = trim($string);

	if($in_slashes) {
		//傳入的字符有slashes
		$string = sstripslashes($string);
	}
	if($html < 0) {
		//去掉html標籤
		$string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", ' ', $string);
		$string = shtmlspecialchars($string);
	} elseif ($html == 0) {
		//轉換html標籤
		$string = shtmlspecialchars($string);
	}

	if($length && strlen($string) > $length) {
		//截斷字符
		$wordscut = '';

		if(strtolower(load_config('default_charset')) == 'utf-8') {
			//utf8編碼
			$n = 0;
			$tn = 0;
			$noc = 0;
			while ($n < strlen($string)) {
				$t = ord($string[$n]);
				if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$tn = 1;
					$n++;
					$noc++;
				} elseif(194 <= $t && $t <= 223) {
					$tn = 2;
					$n += 2;
					$noc += 2;
				} elseif(224 <= $t && $t < 239) {
					$tn = 3;
					$n += 3;
					$noc += 2;
				} elseif(240 <= $t && $t <= 247) {
					$tn = 4;
					$n += 4;
					$noc += 2;
				} elseif(248 <= $t && $t <= 251) {
					$tn = 5;
					$n += 5;
					$noc += 2;
				} elseif($t == 252 || $t == 253) {
					$tn = 6;
					$n += 6;
					$noc += 2;
				} else {
					$n++;
				}
				if ($noc >= $length) {
					break;
				}
			}
			if ($noc > $length) {
				$n -= $tn;
			}
			$wordscut = substr($string, 0, $n);
		} else {
			for($i = 0; $i < $length - 1; $i++) {
				if(ord($string[$i]) > 127) {
					$wordscut .= $string[$i].$string[$i + 1];
					$i++;
				} else {
					$wordscut .= $string[$i];
				}
			}
		}
		$string = $wordscut;
	}
	
	if($out_slashes) {
		$string = daddslashes($string);
	}
	return trim($string);
}
?>