<?php 
session_start();
$rand=NULL;
for($i=0;$i<4;$i++){
           $rand=$rand.dechex(rand(1,15));
}
$_SESSION['randNum']=$rand;
$im = imagecreatetruecolor(110,45);
$bg = imagecolorallocate($im,200,200,255);
$textcolor = imagecolorallocate($im,rand(100,200),rand(100,200),rand(100,200));
 $linecolor = imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));
imagefilledrectangle($im, 0, 0, 110, 45, $bg);

imageline($im, 0, 0, rand(50,110), 45, $linecolor);
imageline($im, 0,  rand(0,45),110, rand(0,45), $linecolor);
 
$font = 'simkai.ttf';
imagettftext($im, 23, 0, rand(10,20), rand(30,35), $textcolor, $font, $rand); 
header('Content-Type: image/pjpeg');

imagejpeg($im);
?>