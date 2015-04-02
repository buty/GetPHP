<?php


class Lib {
	private static $_modes = array(); //实例化对象集合
	function __get($name) {
		//修饰$name
		if(empty($name)) return $_modes;
		if(isset(self::$_modes[$name]))
			if(is_object(self::$_modes[$name])) return self::$_modes[$name];
		$class = ucfirst(strtolower($name));
		
		self::$_modes[$class] = load_class($class);
		return self::$_modes[$class];
	}
}