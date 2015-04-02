<?php

class Model {
	private static $_modes = array(); //实例化对象集合
	protected $db = NULL;//数据库
	function _init() {  //模型初始化
		static $dblink = NULL; //记录数据库连接,防止数据库多次连接
		if($dblink) $this->db = $dblink;  
		
		if(!$this->db) {
			//连接数据库
			$this->db = load_class("Db");
			$this->db->dbcharset = load_config('DBcharset');
			$this->db->pconnect = load_config('DBpconnect');
			$this->db->tablepre = load_config('DBprefix');
			$this->db->tablesuf = load_config('DBsuffix');
			$this->db->connect(load_config('Dbhost'), load_config('Dbuser'), load_config('Dbpass'), load_config('Dbname')); 
			$dblink = $this->db;
		}
	}
	function __get($name) {
		//修饰$name
		if(!empty($name)) {
			$name .= "Model";
		} 
		if(isset(self::$_modes[$name]))
			if(is_object(self::$_modes[$name])) return self::$_modes[$name];
		$class = ucfirst($name);
		
		self::$_modes[$class] = load_class($class);
		return self::$_modes[$class];
	}
	function __call($method,$args) {
		//默认为当前控制器名
		$name =  GetPHP::load('Dispatcher')->getControllName();
		$name_model = $name."Model";  //模型的名字
		if(isset(self::$_modes[$name_model])) {
			if(is_object(self::$_modes[$name_model])) $M = self::$_modes[$name_model];
		} else	
			$M = $this->$name; //进入__get方法
		return call_user_func_array(array($M, $method), $args);
	}

}