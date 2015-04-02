<?php
/****
 * @auth: Buty
 * @data: 2012-1-12
 * @Func: 生成随机变形的验证码
 * 准备工作: 
  	    1.创建字模数组	$captcha = new Captcha;
 		       			$captcha->createZiMo();
        生成验证码:
   						$captcha->captcha_code();
 */
class Captcha {
	private $words = '123456789ABCDEFGHIJKLNPQRSTUVXYZ';
	private $width = 25;  //字模宽度
	private $height = 25; //字模高度
	private $img;
	private $text_color;  //文字颜色
	public  $zimo_dir = 'zimopics/'; //字模生成文件目录
	private $suffix = '.gif'; //字模图片
	public  $zimo_file = 'zimo.php'; //字模数组文件
	private $zimo = NULL;
	private $font_file = 'arial.ttf';//ttf字体文件, 生成字模图片时必须的,在系统中随便找一个
	//初始化画布
	public function Captcha() {
		$width = 140;
		$height = 40;
		if(!extension_loaded('gd')) { //需要gd扩展的支持
			exit('GD extension is supported,please load GD extension and go on.');
		}
		$this->img = imagecreatetruecolor($width, $height);
		$this->text_color = imagecolorallocate($this->img, 0, 0, 255);
		$bg_color = imagecolorallocate($this->img, 255, 255, 255);
		ImageFilledRectangle($this->img, 0, 0, $width, $height, $bg_color);
	}
	//保存验证码到session, 对于不同存储验证码的手段,此方法直接重写
	function saveCode($code) {
		$cookie = load_class('Cookie');
		$cookie->set('captcha', implode('',$code));
	}
	//产生验证码
	private function productNum($len = '4') {
		$num = array();
		$total_len = strlen($this->words);
		for($i = 0; $i < $len; $i++) {
			$index = mt_rand(0, ($total_len - 1));
			$num[]= $this->words[$index];
		} 
		return $num;
	}
	//显示验证码
	function captcha_code($len = '') {
		if(empty($len)) $len = 5;
		$code = $this->productNum($len);
		foreach($code as $k => $v) {
			$this->addZiMo($v, $k * $this->width);
		}
		//保存验证码到session
		$this->saveCode($code);
		
		$this->showNewPic();
	}
	//加载字模数据
	function loadZiMo() {
		if(is_file($this->zimo_dir . $this->zimo_file)) {
			$this->zimo = include $this->zimo_dir . $this->zimo_file;
			//解析字模点阵
			$this->zimo = $this->strToArray($this->zimo);
		} else {
			exit('Zimo file not exists, please use method `createZiMo` create Zimo file.');
		}
	}
	//添加新字模到图片
	function addZiMo($str, $width=0) {
		if(!$this->zimo) {
			$this->loadZiMo();
		}
		$str = strtoupper($str);
		if(isset($this->zimo[$str])) {
			$this->rotateZiMo($this->zimo[$str],$width);
		}
	}
	//旋转字模打点
	function rotateZiMo($data, $width=0) {
		$angle = $this->randAngle(); //角度随机
		$width = $this->randDistance($width);//距离随机
		
		foreach($data as $i => $v) {
			foreach($v as $j => $vs) {
				if(!$vs) {
					$jindex = $j+$width;
					$res = $this->circlecoord($width,0,$jindex,-$i, $angle);
					$new_i = floor(abs($res['y']));
					$new_j = floor(abs($res['x']));
					imagesetpixel($this->img, $new_j, ($new_i + 10), $this->text_color); //加10让其居中
				}
			}
		}
		return $data;
	}
	//角度变换
	function randAngle() {
		return mt_rand(-15,15); //角度随机
	}
	//距离变换
	function randDistance($width) {
		return $width + mt_rand(15,20); //距离随机
	}
	//根据圆心坐标,圆上一点坐标,角度,计算另一坐标
	function circlecoord($x0,$y0,$x1,$y1,$angle) {
		$r = sqrt(($x1 - $x0)*($x1 - $x0) + ($y1 - $y0)*($y1 - $y0));
		$arc = ($x1 - $x0) / $r;
		$angle0_rad = acos($arc);
		$angle0_rad = rad2deg($angle0_rad);
		$angle_new = $angle0_rad + $angle;
		$new_x = $x0 + $r * cos(deg2rad($angle_new));
		$new_y = $y0 + $r * sin(deg2rad($angle_new));
		return array('x' => $new_x, 'y' => $new_y);
	}
	
/*****************************************自行创建字模图片与字模数组文件********************************/	
	//创建字模图片
	function createZiMoPic($words = '') {
		if(!file_exists($this->font_file)) {  //字体需要存在
			exit('Font file no exists.');
		}
		$this->checkDoc(); //如果目录不存在,自动生成
		if(empty($words)) $words = $this->words;
		for($i = 0; $i < strlen($words); $i++) {
			$num = $words[$i];
			$height = 25; //高度
			$width = 25; //一个字符宽度
			$font_size = 25; //字体大小
			$font = $this->font_file; //ttf字体
			$x = 0;
			$y = 0;
			$angle = 0;
			$img = imagecreatetruecolor($width, $height);
			$text_color = imagecolorallocate($img, 0, 0, 255);
			$bg_color = imagecolorallocate($img, 255, 255, 255);
			//其左上角坐标为 x，y
			ImageFilledRectangle($img, $x, $y, $width, $height, $bg_color);
			//由 x，y 所表示的坐标定义了第一个字符的基本点（大概是字符的左下角）
			imagettftext($img, $font_size, $angle, $x, $height, $text_color, $font, $num);
			imagejpeg($img, $this->zimo_dir . strtoupper($num) . $this->suffix);
			imagedestroy($img);
		}
	}
	//检测目录是否存在,不存在创建
	function checkDoc() {
		if(!is_dir($this->zimo_dir)) {
			mkdir($this->zimo_dir,0777,true);
		}
	}
	//创建字模
	function createZiMo($words = '') {
		if(empty($words)) $words = $this->words;
		$this->createZiMoPic($words);
		$data = array();
		if(is_dir($this->zimo_dir)) {
			if($dp = opendir($this->zimo_dir)) {
				while (($file = readdir($dp)) !== false) {
					if($file != '.' && $file != '..' && $this->suffix == strrchr($file, '.')) {
						$key = substr($file, 0, -strlen($this->suffix));
			            $value = $this->createZiMoDot($this->zimo_dir . $file);
						$data[$key] = $value;
					}
		        }
		        closedir($dp);
			}
		}
		$data = $this->arrayToStr($data);
		$this->writeZiMoFile($data);
	}
	//写入字模文件
	function writeZiMoFile($data) {
		$fp = fopen($this->zimo_dir . $this->zimo_file, 'w');
		$data = $this->stripWhiteEnter(var_export($data, true));
		fwrite($fp, "<?php\r\nreturn ".$data."\r\n?>");
		fclose($fp);
	}
	//将三维数组改写成字符串
	function arrayToStr($data) {
		foreach($data as $k => $v) {
			$str = '';
			foreach($v as $sk => $sv) {
				foreach($sv as $ssk => $ssv) {
					$str .= $ssv;
				}
			}
			unset($data[$k]);
			$data[$k] = $str;
		}
		return $data;
	}
	//将一维数组按规律改写成三维数组
	function strToArray($data) {
		$cut_words = 25; //将字符串切成25个小数组
		foreach($data as $k => $v) {
			unset($data[$k]);
			$strlens = strlen($v);
			for($i = 0; $i < $strlens; $i++) {
				$secindex = floor($i/$cut_words);
				$data[$k][$secindex][] = $v[$i];
			}
		}

		return $data;
	}
	// 去除代码中的空白和换行
	function stripWhiteEnter($content) {
	    $stripword = array(' ','\t',"\r","\n","\r\n");
		$content = str_replace($stripword,"",$content);
	    return $content;
	}
	//生成字模点阵
	function createZiMoDot($picfile) {
		$res = imagecreatefromjpeg($picfile);
		$size = getimagesize($picfile);
		$data = array();
		for($i=0; $i < $size[1]; ++$i) {
			for($j=0; $j < $size[0]; ++$j) {
				$rgb = imagecolorat($res,$j,$i);
				$rgbarray = imagecolorsforindex($res, $rgb);
				if($rgbarray['red'] > 125 || $rgbarray['green']>125) {
					$data[$i][$j]=1;
				} else {
					$data[$i][$j]=0;
				}
			}
		}
		return $data;
	}
	//显示生成的图片
	function showNewPic() {
		header('Content-type: image/jpeg');
		imagejpeg($this->img);
		imagedestroy($this->img);
	}
}