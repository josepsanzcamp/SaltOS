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
// FOR JQUERY-UI
$source="jquery-ui.custom.template";
$dest="jquery-ui.custom.${name}";
mkdir($dest);
mkdir($dest."/images");
$images=glob($source."/images/*");
foreach($images as $image) {
	$image=basename($image);
	$image=str_replace("123456",$color1,$image);
	$image=str_replace("234567",$color2,$image);
	$image=str_replace("345678",$color3,$image);
	$image=str_replace("456789",$color4,$image);
	$image=str_replace("56789a",$color5,$image);
	$image=str_replace("6789ab",$color6,$image);
	if(file_exists($source."/images/".$image)) {
		copy($source."/images/".$image,$dest."/images/".$image);
	} else {
		$buffer=file_get_contents("http://127.0.0.1:8088/themeroller/images/".$image);
		file_put_contents($dest."/images/".$image,$buffer);
	}
}
$file="jquery-ui.min.css";
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