<?php if(!defined('APPNAME')) exit('Access Denied');?><?php subtplcheck('template/default/Index/index', '1427967844', 'template/default/Index/index');?><?php if(!defined('APPNAME')) exit('Access Denied');?><?php subtplcheck('template/default/Index/index', '1427967844', 'template/default/Index/index');?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>GetPHP个人框架</title>
</head>

<body>
GetPHP个人框架
<?php echo langpreg('common',  "sitename_preg", array('fewfwccc')); ?>
<?php if(is_array($arr)) { foreach($arr as $k => $v) { ?>
<?php echo $k?>:<?php echo $v['b']?> <br />
<?php } } ?>

<?php echo $gogo?>

<?php echo $$gogo?>


<?php if($gogo) { ?>
gogo
<?php } else { ?>
nono
<?php } ?>


{date('Y-m-d',"1548880000")}

<?php echo $arr['a']['b']?>
</body>
</html>