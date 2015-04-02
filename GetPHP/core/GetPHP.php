<?php 

class GetPHP {
	private static $_instance = array(); //实例化对象集合
	
	//利用类的魔法方法__get来将所需类自动加载,实现$this->View->display()的方式
	function __get($name) {
		if(isset(self::$_instance[$name]))
			if(is_object(self::$_instance[$name])) return self::$_instance[$name];
		$class = ucfirst(strtolower($name));

		self::$_instance[$class] = load_class($class);
		return self::$_instance[$class];
	}
	//静态类随时获取以实例化的对象
	public static function load($name) {
		if(isset(self::$_instance[$name]))
			if(is_object(self::$_instance[$name])) 
				return self::$_instance[$name];
			else
				return NULL;
		else
			return NULL;
	}
}


?>