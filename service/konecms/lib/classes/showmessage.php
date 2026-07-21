<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  
  <meta http-equiv="imagetoolbar" content="no"> 
  <meta name="robots" content="noindex,nofollow">
  <title><?php echo L("showmessage_webtitle");?></title> 
		<style>
		body {background: #f9fee8;margin: 0; padding: 20px; text-align:center; font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#666666;}
		.error_page {width: 600px; padding: 50px; margin: auto;}
		.error_page h1 {margin: 20px 0 0;}
		.error_page p {margin: 10px 0; padding: 0;}		
		a {color: #9caa6d; text-decoration:none;}
		a:hover {color: #9caa6d; text-decoration:underline;}
		</style>
    <script src="<?php echo STATIC_PATH?>js/jquery-1.8.3.min.js"></script>
	
	<?php if(!$ifDialog):?>	
<SCRIPT LANGUAGE="JavaScript"> 
    <!-- Begin
    var start=new Date();
    start=Date.parse(start)/1000;
    var counts=<?php echo $intime;?>;
    function timer(){
        var now=new Date();
        now=Date.parse(now)/1000;
        var x=parseInt(counts-(now-start),10);
        if(document.getElementById("count")){document.getElementById("count").innerHTML = x;}
        if(x>0){
            setTimeout("timer()",1000)
        }else{
            location.href="<?php echo $tourl;?>"
        }
    }
	
     window.timer();

    //  End -->
</script>
		<?php endif;?>

</head>

<body class="login" >
 <?php if($fresh_parent): 
	    echo "<script language=javascript>parent.$('#left').load('?m=admin&a=load_left_menu&menuid=2')</script>";
		endif;
 ?>
  <div class="error_page">
    <h1><?php echo $msg;?></h1> 
	<?php if($ifDialog){?>
	
    <p><a href="<?php echo $tourl;?>">返回上一页 </a></p>
	<?php }else{?>
    <p>系统<span id="count"><?php echo $intime;?></span>秒后自动跳转或 </p> 
    <p><a href="<?php echo $tourl;?>">如果您的浏览器没有跳转，请点击这里。</a></p>
	<?php } ?>
  </div>
 
<?php if($ifDialog){?> 
<SCRIPT LANGUAGE=JavaScript>
$(function(){ 
 parent.artDialog(
		     {
				 content:"<h1><?php echo $msg;?></h1><p><a href=javascript:"+parent.$("[name='sysiframe']").attr("src","<?php echo $tourl;?>") +">继续添加</a></p>", fixed:true}
		);
});

</SCRIPT>
<?php }?>
</body></html>
