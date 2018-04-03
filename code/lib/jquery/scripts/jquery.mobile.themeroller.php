<?php
if($argc!=8) die("Unknown arguments!!!\n");
$name=$argv[1];
$color1=$argv[2];
$color2=$argv[3];
$color3=$argv[4];
$color4=$argv[5];
$color5=$argv[6];
$color6=$argv[7];
// FUNCTIONS
function __rgb2rgba($color) {
	return hexdec(substr($color,0,2)).",".hexdec(substr($color,2,2)).",".hexdec(substr($color,4,2));
}
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
$buffer=str_replace("345678",$color3,$buffer);
$buffer=str_replace("456789",$color4,$buffer);
$buffer=str_replace("56789a",$color5,$buffer);
$buffer=str_replace("6789ab",$color6,$buffer);
$buffer=str_replace(__rgb2rgba("123456"),__rgb2rgba($color1),$buffer);
$buffer=str_replace(__rgb2rgba("234567"),__rgb2rgba($color2),$buffer);
$buffer=str_replace(__rgb2rgba("345678"),__rgb2rgba($color3),$buffer);
$buffer=str_replace(__rgb2rgba("456789"),__rgb2rgba($color4),$buffer);
$buffer=str_replace(__rgb2rgba("56789a"),__rgb2rgba($color5),$buffer);
$buffer=str_replace(__rgb2rgba("6789ab"),__rgb2rgba($color6),$buffer);
file_put_contents($dest."/".$file,$buffer);
?>