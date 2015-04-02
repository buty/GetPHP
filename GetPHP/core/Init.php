<?php 

class Init {
	//系统启动入口
	public static function run() {
        //实例化GetPHP核心类
        $gp = load_class('GetPHP');
        //设置url常量
        self::defineUrl($gp);
		//初始化错误捕获
		set_error_handler(array('Init','appError'));
        set_exception_handler(array('Init','appException'));

        //处理url路径问题
        $gp->Dispatcher->dispatch();
        //设置控制器, 方法, 参数变量
		$c = $gp->Dispatcher->getControllName() . 'Controller';
		$a = $gp->Dispatcher->getActionName();
		$p = $gp->Dispatcher->getParamName();
		if(empty($p)) $p = array(); //5.3版本的 an E_STRICT warning
        if(!file_exists(APP_PATH . 'controller' . DS . $c . EXT)) {
            throw new Exception("The App's controller not exists");
        }

        //初始化控制器
        require(GETPHP_PATH . 'core' . DS . 'Controll.php');
        require(APP_PATH . 'controller' . DS . $c . EXT);
        $GP = new $c();

        //调用控制器方法
        call_user_func_array(array(&$GP, $a), $p);
	}

    public static function defineUrl($gp) {
        define('BASE_URL', $gp->Dispatcher->getBaseUrl());
        //对于URI的处理
        if(load_config('urlmethod') == 3) { 
            define('SITE_URL', BASE_URL . SELF . '?s=');
        } elseif(load_config('urlmethod') == 2) { //重写规则支持,注意nginx下面的处理
            define('SITE_URL', BASE_URL);
        } else { //正规
            define('SITE_URL', BASE_URL . SELF . '/');
        }
        //常量定义
        require(GETPHP_CORE_PATH . 'DefineConst.php');
    }

	//自定义错误处理
	public static function appError($errno, $errstr, $errfile, $errline) {
		switch ($errno) {
          case E_ERROR:
          case E_USER_ERROR:
            $error_str = "[$errno] $errstr ".basename($errfile)." 第 $errline 行.\r\n";
            break;
          case E_STRICT:
          case E_USER_WARNING:
          case E_USER_NOTICE:
          default:
            $error_str = "[$errno] $errstr ".basename($errfile)." 第 $errline 行.\r\n";
            break;
        }

        self::display($error_str, 'error');
	}

	//自定义异常处理
	public static function appException($e) {
        self::display($e->__toString(), 'exception');
	}

    public static function display($msg, $page) {
        $gp = load_class('GetPHP');
        $gp->View->assign('msg', $msg);
        $gp->View->display($page);
    }
}

