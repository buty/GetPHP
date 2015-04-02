<?php
/*
* @auth: Buty
* @功能: 1.添加文字,2.添加水印,3.缩略图,4.等比缩放
* @时间: 2011.1.3
*/

class Img {
    public $srcfile;//源图地址
    public $s_width;//源图宽
    public $s_height;//源图高
    public $s_type;//源类型
    public $percent = 0.5; //默认缩放比例 
    public $src;//源
    public $to;//目标
    public $fontfile = 'arial.ttf';//目标
    public $fontsize = '13';//字体 
    
    //实例化一个图片对象,获取它的信息长,宽,类型
    function init($srcfile) {
        if(!is_file($srcfile)) return false;        
        $this->srcfile = $srcfile;
        //获取信息
        $imageinfo = getimagesize($srcfile);
       
        if(!$imageinfo) return false;
        $this->s_width = $imageinfo[0];
        $this->s_height = $imageinfo[1];
        $this->s_type = $imageinfo[2];	
    }
    
    //缩略图   先按宽度来缩图,然后才是高度
    function resizeImg($w, $h, $newfile = '') {
        if($this->s_width > $w) {
            $h = $w * ($this->s_height / $this->s_width);    
        } elseif($this->s_height > $h) {
            $w = $h * ($this->s_width / $this->s_height);     
        } 

        $this->src = $this->createFormImg($this->srcfile,$this->s_type); //创建源图片资源

        $this->to = imagecreatetruecolor($w, $h);  //目标图片资源 
        @imagecopyresized($this->to, $this->src, 0, 0, 0, 0, $w, $h, $this->s_width, $this->s_height); 
        $this->productImg($this->to, $newfile);   
    }
    //缩略图 按百分比来缩放
    function resizeImgByPercent($percent, $newfile = '') {
        $pct = '';
        $pct = $percent;
        
        if(empty($pct)) $pct = $this->percent; 
        $w = $pct * $this->s_width;
        $h = $pct * $this->s_height;
       
        $this->src = $this->createFormImg($this->srcfile,$this->s_type); //创建源图片资源
        $this->to = imagecreatetruecolor($w, $h);  //目标图片资源 
        @imagecopyresized($this->to, $this->src, 0, 0, 0, 0, $w, $h, $this->s_width, $this->s_height); 
         
        $this->productImg($this->to, $newfile);   
    }
    
    //水印文字
    /**
    * @desc
    * $txt 文字内容
    * $fontfile 所用的字体  (目录下面的字体文件)
    * $pos 文字的位置  1: 顶左 2:底左 3:顶右 4:底右 其它值:随机位置
    * $color 文字着色  只是前景色,即文字的着色
    * $angle 角度
    */
    function writeWaterText($txt,$fontsize = '',$fontfile = '',$pos = 0,$color = 0,$angle = 0) {
        if(empty($txt)) return false;
        if(empty($fontfile))  $fontfile = $this->fontfile;
        if(!$this->judgeGifType($this->srcfile)) return false;//不为GIF动画加入文字
        if(empty($fontsize)) $fontsize = $this->fontsize;
        if(!file_exists($fontfile)) return false;
        $this->src = $this->createFormImg($this->srcfile,$this->s_type); //创建源图片资源 
        if(strpos($color, ',') !== FALSE) {
            list($red, $green, $blue) = @explode(',',$color);
            $fontcolor = imagecolorallocate($this->src, $red, $green, $blue);      
        } else {
            $fontcolor = imagecolorallocate($this->src, 0, 0, 0); //默认黑字  
        }
        //字体的长与高 imageloadfont 不能加载ttf,fon等字体,它需要 gdf , pfb格式的字体包
        /*
        $fontinfo = imagepsloadfont($fontfile);
        $f_width = imagefontwidth($fontinfo);  //宽度
        $f_height = imagefontheight($fontinfo); //高度
        */
        $f_width = 8;
        $f_height = 8;
        $pos_w = $pos_h = 0;
        switch($pos) {
            case 1: //顶左
                $pos_w = 0;
                $pos_h = $f_height * 2;    
                break;
            case 2: //底左
                $pos_w = 0;
                $pos_h = $this->s_height - $f_height;    
                break; 
            case 3: //顶右
               $pos_w = $this->s_width - strlen($txt) * $f_width;
               $pos_h = $f_height * 2; 
               break; 
            case 4: //底右
               $pos_w = $this->s_width - strlen($txt) * $f_width;
               $pos_h = $this->s_height - $f_height;
               break;   
            default:
               $pos_w = mt_rand(0, ($this->s_width - strlen($txt) * $f_width));
               $pos_h = mt_rand(0, ($this->s_height - $f_height));
               break;   
        }
		
        @imagettftext($this->src, $fontsize, $angle, $pos_w, $pos_h, $fontcolor, $fontfile, $txt);                    
        $this->productImg($this->src); 
    } 
    //水印图片
    /**
    * @desc
    * $file 图片文件
    * $pos 文字的位置 1: 顶左 2:底左 3:顶右 4:底右 其它值:随机位置
    * $transparent 透明度 0-100 透明效果与此值成反比
    */
    function writeWaterPic($file, $pos = 1, $transparent = 50) {
        if(!is_file($file)) return false; 
        if(!$this->judgeGifType($this->srcfile)) return false;//不为GIF动画加入水印图片
        $topicinfo = getimagesize($file);
        if(!$topicinfo) return false; 
        $this->src = $this->createFormImg($this->srcfile,$this->s_type); //创建源图片资源
        $t_width = $topicinfo[0];
        $t_height = $topicinfo[1];
        $t_type = $topicinfo[2];
        $this->to = $this->createFormImg($file, $t_type);//水印图片资源
        $pos_w = $pos_h = 0;
        switch($pos) {
            case 1: //顶左
                $pos_w = 0; 
                $pos_h = 0;
                break;
            case 2: //底左
                $post_w = 0;
                $pos_h = $this->s_height - $t_height; 
                break; 
            case 3: //顶右
                $pos_w = $this->s_width - $t_width;
                $pos_h = 0; 
                break; 
            case 4: //底右
                $pos_w = $this->s_width - $t_width;
                $pos_h = $this->s_height - $t_height; 
                break; 
            default:   
               $pos_w = mt_rand(0, ($this->s_width - $t_width));
               $pos_h = mt_rand(0, ($this->s_height - $t_height));
               break;                                  
        }
        
        @imagecopymerge($this->src, $this->to, $pos_w, $pos_h, 0, 0, $t_width, $t_height, $transparent); 
        $this->productImg($this->src);
    }
    
    //创建新图片源
    private function createFormImg($file = '', $type = '') {
        if(!$type) return false;
        $source = null;
        switch($type) {
            case 1: $source = @imagecreatefromgif($file); break;
            case 2: $source = imagecreatefromjpeg($file);break;
            case 3: $source = @imagecreatefrompng($file);break;
        }   
        return  $source;  
    }  
    //判断是否是动画gif
    function judgeGifType($file) {
        //判断是否为动画
        $fp = fopen($file, 'rb');
        $filecontent = fread($fp, filesize($file));
        fclose($fp);
        if(strpos($filecontent, 'NETSCAPE2.0') === FALSE) {//动画图不加水印
            return true;
        } 
        return false;   
    }
    
    //生成新图片
    private function productImg($src,$file = '') {
        $srcfile = '';
        if(!$this->s_type) return false; 
        if(!is_resource($src)) return false;
        $srcfile = $file;
        if(empty($srcfile)) $srcfile = $this->srcfile;
        switch($this->s_type) {
            case 1:@imagegif($src, $srcfile);break;
            case 2:@imagejpeg($src, $srcfile);break;
            case 3:@imagepng($src, $srcfile);break;
        }   
        if(isset($this->to)) @imagedestroy($this->to);
        if(isset($this->src)) @imagedestroy($this->src);
    }
}

