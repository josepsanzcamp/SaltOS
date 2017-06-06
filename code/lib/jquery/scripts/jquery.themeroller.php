<?php
if($argc!=4) die("Unknown arguments!!!\n");
$name=$argv[1];
$color1=$argv[2];
$color2=$argv[3];
// FUNCTIONS
function __rgb2rgba($color) {
	return hexdec(substr($color,0,2)).",".hexdec(substr($color,2,2)).",".hexdec(substr($color,4,2));
}
// FOR JQUERY-UI
$source="jquery-ui.custom.template";
$dest="jquery-ui.custom.${name}";
mkdir($dest);
mkdir($dest."/images");
$images=glob($source."/images/*");
foreach($images as $key=>$val) $images[$key]=basename($val);
$images=array_diff($images,array("ui-icons_123456_256x240.png"));
foreach($images as $image) copy($source."/images/".$image,$dest."/images/".$image);
$image="ui-icons_${color1}_256x240.png";
$buffer=file_get_contents("http://download.jqueryui.com/themeroller/images/".$image);
file_put_contents($dest."/images/".$image,$buffer);
$file="jquery-ui.min.css";
$buffer=file_get_contents($source."/".$file);
$buffer=str_replace("123456",$color1,$buffer);
$buffer=str_replace("234567",$color2,$buffer);
$buffer=str_replace(__rgb2rgba("123456"),__rgb2rgba($color1),$buffer);
$buffer=str_replace(__rgb2rgba("234567"),__rgb2rgba($color2),$buffer);
file_put_contents($dest."/".$file,$buffer);
// FOR JQUERY-MOBILE
$source="jquery.mobile.custom.template";
$dest="jquery.mobile.custom.${name}";
mkdir($dest);
mkdir($dest."/images");
mkdir($dest."/images/icons-png");
$images=glob($source."/images/*");
foreach($images as $key=>$val) $images[$key]=basename($val);
$images=array_diff($images,array("icons-png"));
foreach($images as $image) copy($source."/images/".$image,$dest."/images/".$image);
$images=glob($source."/images/icons-png/*");
foreach($images as $key=>$val) $images[$key]=basename($val);
foreach($images as $image) copy($source."/images/icons-png/".$image,$dest."/images/icons-png/".$image);
$file="jquery.mobile.min.css";
$buffer=file_get_contents($source."/".$file);
$buffer=str_replace("123456",$color1,$buffer);
$buffer=str_replace("234567",$color2,$buffer);
$buffer=str_replace(__rgb2rgba("123456"),__rgb2rgba($color1),$buffer);
$buffer=str_replace(__rgb2rgba("234567"),__rgb2rgba($color2),$buffer);
file_put_contents($dest."/".$file,$buffer);
?>