<?php 

class Dispatcher {
	private $c = '';
	private $a = '';
	private $p = '';
	
	//将URI构造为三个参数:控制器, 方法, 参数
	public function dispatch() {
		$uri = $this->_detectUri();

		$rewrite = $this->_explodeSegments($uri);
		if(isset($rewrite[0]) && !empty($rewrite[0]))  $this->c = ucfirst($rewrite[0]);
		else	$this->c = 'Index';
		if(isset($rewrite[1]) && !empty($rewrite[1]))  $this->a = $rewrite[1];
		else	$this->a = 'index';
		if(isset($rewrite[2])) { 
			unset($rewrite[0], $rewrite[1]);
			$this->p = $rewrite;
		}
	}

	public function getControllName() {
		return $this->c;
	}

	public function getActionName() {
		return $this->a;
	}

	public function getParamName() {
		return $this->p;
	}

	public function getBaseUrl() {
		$host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
		$port_suffix = $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT'];
		$path_suffix = $this->_getUrlPrefixPath();

		return $host . $port_suffix . $path_suffix;
	}

	private function _getUrlPrefixPath() {
		$uri = dirname($_SERVER['SCRIPT_NAME']);
		$uri = str_replace('\\', '/', $uri);
		if($uri == '/') {
			$uri = '';
		}
		
		return $uri;
	}
	
	//处理uri
	private function _detectUri() {
		if (!isset($_SERVER['REQUEST_URI'])) {
			return '';
		}
	
		$uri = $_SERVER['REQUEST_URI'];
		
		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
			$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
		} elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
			$uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}

		// This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
		// URI is found, and also fixes the QUERY_STRING server var and $_GET array.
		if (strncmp($uri, '?/', 2) === 0){
			$uri = substr($uri, 2);
		}
		if (strncmp($uri, '?s=', 3) === 0){
			$uri = substr($uri, 3);
		}
		$parts = preg_split('#\?#i', $uri, 2);
		$uri = $parts[0];

		if (isset($parts[1])){
			$_SERVER['QUERY_STRING'] = $parts[1];
			parse_str($_SERVER['QUERY_STRING'], $_GET);
		} else {
			$_SERVER['QUERY_STRING'] = '';
			$_GET = array();
		}

		if ($uri == '/' || empty($uri)) {
			return '/';
		}
		
		$uri = parse_url($uri, PHP_URL_PATH);
		// Do some final cleaning of the URI and return it
		return str_replace(array('//', '../'), '/', trim($uri, '/'));
	}

	private function _explodeSegments($uri) {
		$segments = array();	
		foreach (explode("/", $uri) as $val) {
			// Filter segments for security
			$val = trim($this->_filterUri($val));
			if ($val != '') {
				$segments[] = $val;
			}
		}
		return $segments;
	}

	private function _filterUri($str) {
		// Convert programatic characters to entities
		$bad	= array('$',		'(',		')',		'%28',		'%29');
		$good	= array('&#36;',	'&#40;',	'&#41;',	'&#40;',	'&#41;');
	
		return str_replace($bad, $good, $str);
	}
}


?>