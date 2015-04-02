<?php


class Cookie {
	public $pre = '';//cookie前缀
	public $cookieName = array(); //cookie名称
	public $cookieValue = array(); //cookie值
	public $expires = 0;//有效时间
	public $domain = '';//有效域
	public $path = '/';//有效路径
	
	function __construct() {
		//加载配置文件
		if(file_exists(APP_CONFIG_PATH . 'cookie.php') && !load_config('Pre')) {
			$config = array();
			$config = load_config(include APP_CONFIG_PATH . 'cookie.php');

			$classVarArr = get_class_vars(__CLASS__);
			if(is_array($classVarArr) && !empty($classVarArr)) {
				foreach($classVarArr as $k => $v) {
					if(in_array($k, array_keys($config))) {
						$this->$k = $config[$k];
					}
				}
			}
		}
	}
	
	//设置cookie
	public function set($name, $value = '', $expires = 0, $path = '', $domain = '') {
		if($expires) $this->expires = $expires;
		$this->expires 	= !empty($this->expires) ? time()+$this->expires : 0;
		$value 			= base64_encode(serialize($value));
		setcookie($this->pre . $name, $value, $this->expires, $this->path, $this->domain);
		$_COOKIE[$this->pre . $name] = $value;
	}
	
	//获取cookie
    public function get($name) {
    	if(isset($_COOKIE[$this->pre . $name])) {
	    	$value   = $_COOKIE[$this->pre . $name];
	        $value   =  unserialize(base64_decode($value));
	        return $value;
    	} else {
    		return NULL;
    	}
    }
    // 删除某个Cookie值
    public function delete($name) {
        $this->set($name,'',-3600);
        unset($_COOKIE[$this->pre . $name]);
    }

    // 清空Cookie值
    static function clear() {
        unset($_COOKIE);
    }
    
}