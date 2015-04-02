<?php 


class Upload {
	private $allowtype = array('image/gif','image/jpeg','image/png','image/bmp','image/pjpeg','text/plain','image/x-png');
	private $allowext = array('jpg','jpeg','gif','png','txt');
	private $ext;
	private $maxsize = 2048000; //不能超过服务器的最大上传限制
	private $path = 'upload/';
	private $return_file_paths = array();
	//选择性的初始化配置参数
	function init($config = array()) {
		//初始化路径
		if(isset($config['path']) && is_dir($config['path'])) $this->path = $config['path'];
		//初始化最大上传容量
		if(isset($config['maxsize']) && is_int($config['maxsize'])) $this->maxsize = $config['maxsize'];
		//初始化允许上传文件的后缀
		if(!empty($config['allowext']) && is_array($config['allowext'])) $this->allowext = $config['allowext'];
		//初始化允许上传文件的类型
		if(!empty($config['allowtype']) && is_array($config['allowtype'])) $this->allowtype = $config['allowtype'];
	}
	/**
	 * 处理文件上传组数
	 * @param array $post 如$_FILES['name']
	 * @param string $path , 指定上传的路径
	 * @return array $this->return_file_paths, 以数组的形式返回上传文件后的文件路径 
	 */
	public function upload_file($post, $path = '') {
		$return_file = array();
		if(is_array($post)) {
			if(key_exists('name', $post)) {
				if(!is_array($post['name'])) {  //单文件上传
					$return_file[] = $this->_upload($post, $path);
				} else {  //多文件上传
					foreach($post['name'] as $k => $v) {
						//组合单个传文件数组
						$merge_upfiles = array(
									 'name' 	=> $v, 
									 'size' 	=> $post['size'][$k],
									 'type'		=> $post['type'][$k],
									 'tmp_name' => $post['tmp_name'][$k],
									 'error' 	=> $post['error'][$k]	
									);	
						$return_file[] = $this->_upload($merge_upfiles);
						unset($merge_upfiles);
					}	
				}
				return $return_file;
			} else
				return false;
				
		} else
			return false;		
	}
	/**
	 * 开始上传
	 * @param $post 确定的一维数组
	 * @param $path 上传的确定路径
	 */
	private function _upload( $post, $path = '' ) {
		if($this->checkRule($post)) {	//检查
			$filepath = $this->getFileNewName($this->ext, true);
			$new_name = $this->path.'./'.$filepath;
			$tmp_name = $post['tmp_name'];
			if(@copy($tmp_name, $new_name)) {
				@unlink($tmp_name);
			} elseif((function_exists('move_uploaded_file') && @move_uploaded_file($tmp_name, $new_name))) {
				//忽略
			} elseif(@rename($tmp_name, $new_name)) {
				//忽略
			} else {
				return false;
			}	

			return $filepath;
		}	
	}
	private function checkRule($post) {
		//第一轮简单检测
		if(empty($post['size']) || empty($post['tmp_name']) || !empty($post['error'])) {
			return false;
		}
		//第二轮严格检测
		if($this->checkSize($post['size']) && $this->checkType($post['type']) && $this->checkExt($post['name'])) {
			return true;	
		} else
			return false;
	}
	/**
	 * 检测文件大小是否受限制
	 * @param $size
	 */
	private function checkSize($size) {
		$size = intval($size);
		if(empty($size) || $size > $this->maxsize) {
			return false;
		} else 
			return true;	
	}
	/**
	 * 检测文件类型是否受限制
	 * @param string $type
	 */
	private function checkType($type) {
		$type = strval($type);
		if(empty($type) || !in_array($type, $this->allowtype)) {
			return false;
		} else 
			return true;
	}
	/**
	 * 检测文件后缀是否受限制
	 * @param string $name
	 */
	private function checkExt($name) {
		$this->ext = $this->getFileExt($name);
		if(empty($this->ext) || !in_array($this->ext, $this->allowext)) {
			return false;
		} else 
			return true;
	}
	/**
	 * 获取文件后缀
	 * @param string $name
	 */
	private function getFileExt($name) {
		return strtolower(trim(substr(strrchr($name, '.'), 1)));
	} 
	/**
	 * 有规律的生成新的文件路径
	 * @param string $fileext
	 * @param string $mkdir
	 */
	private function getFileNewName($fileext, $mkdir=false) {
		//$uid = checklogin();
		$filepath = rand(1, 1000)."_".time().$this->random(4).".$fileext";
		$name1 = gmdate('Ym');
		$name2 = gmdate('j');
		if($mkdir) {
			$newfilename = $this->path . $name1;
			
			if(!is_dir($newfilename)) {
				if(!mkdir($newfilename)) {
					return false;
				}
			}
			$newfilename .= '/'.$name2;
			if(!is_dir($newfilename)) {
				if(!@mkdir($newfilename, 0777, true)) {
					return false;
				}
			}
		}
		return $name1.'/'.$name2.'/'.$filepath;	
	}
	/**
	 * 产生随机字符
	 * @param $length
	 * @param $numeric
	 */
	private function random($length, $numeric = 0) {
		PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
		$seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, $numeric ? 10 : 35);
		$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
		$hash = '';
		$max = strlen($seed) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $seed[mt_rand(0, $max)];
		}
		return $hash;
	}
	
}

