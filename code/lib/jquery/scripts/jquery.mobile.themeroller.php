<?php
if($argc!=5) die("Unknown arguments!!!\n");
$name=$argv[1];
$type=$argv[2];
$color1=$argv[3];
$color2=$argv[4];
// FUNCTIONS
function __rgb2rgba($color) {
	return hexdec(substr($color,0,2)).",".hexdec(substr($color,2,2)).",".hexdec(substr($color,4,2));
}
// FOR JQUERY-MOBILE
$source="jquery.mobile.custom.template.${type}";
$dest="jquery.mobile.custom.${name}.${type}";
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