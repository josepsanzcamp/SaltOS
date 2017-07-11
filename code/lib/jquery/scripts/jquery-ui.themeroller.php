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
// FOR JQUERY-UI
$source="jquery-ui.custom.template.${type}";
$dest="jquery-ui.custom.${name}.${type}";
mkdir($dest);
mkdir($dest."/images");
$images=glob($source."/images/*");
foreach($images as $image) {
	$image=basename($image);
	$image=str_replace("123456",$color1,$image);
	$image=str_replace("234567",$color2,$image);
	if(file_exists($source."/images/".$image)) {
		copy($source."/images/".$image,$dest."/images/".$image);
	} else {
		$buffer=file_get_contents("http://download.jqueryui.com/themeroller/images/".$image);
		file_put_contents($dest."/images/".$image,$buffer);
	}
}
$file="jquery-ui.min.css";
$buffer=file_get_contents($source."/".$file);
$buffer=str_replace("123456",$color1,$buffer);
$buffer=str_replace("234567",$color2,$buffer);
$buffer=str_replace(__rgb2rgba("123456"),__rgb2rgba($color1),$buffer);
$buffer=str_replace(__rgb2rgba("234567"),__rgb2rgba($color2),$buffer);
file_put_contents($dest."/".$file,$buffer);
?>