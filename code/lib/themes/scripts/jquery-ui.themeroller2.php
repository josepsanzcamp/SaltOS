<?php
if($argc!=12) die("Unknown arguments!!!\n");
$name=$argv[1];
$color1=$argv[2];
$color2=$argv[3];
$color3=$argv[4];
$color4=$argv[5];
$color5=$argv[6];
$color6=$argv[7];
$color7=$argv[8];
$color8=$argv[9];
$color9=$argv[10];
$colora=$argv[11];
// FUNCTIONS
function __rgb2rgba($color) {
	return hexdec(substr($color,0,2)).",".hexdec(substr($color,2,2)).",".hexdec(substr($color,4,2));
}
// FOR JQUERY-UI
$source="scripts/jquery-ui.custom2.template";
$dest="${name}/jquery-ui";
mkdir($dest,0777,true);
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
	$image=str_replace("789abc",$color7,$image);
	$image=str_replace("89abcd",$color8,$image);
	$image=str_replace("9abcde",$color9,$image);
	$image=str_replace("abcdef",$colora,$image);
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
$buffer=str_replace("789abc",$color7,$buffer);
$buffer=str_replace("89abcd",$color8,$buffer);
$buffer=str_replace("9abcde",$color9,$buffer);
$buffer=str_replace("abcdef",$colora,$buffer);
$buffer=str_replace(__rgb2rgba("123456"),__rgb2rgba($color1),$buffer);
$buffer=str_replace(__rgb2rgba("234567"),__rgb2rgba($color2),$buffer);
$buffer=str_replace(__rgb2rgba("345678"),__rgb2rgba($color3),$buffer);
$buffer=str_replace(__rgb2rgba("456789"),__rgb2rgba($color4),$buffer);
$buffer=str_replace(__rgb2rgba("56789a"),__rgb2rgba($color5),$buffer);
$buffer=str_replace(__rgb2rgba("6789ab"),__rgb2rgba($color6),$buffer);
$buffer=str_replace(__rgb2rgba("789abc"),__rgb2rgba($color7),$buffer);
$buffer=str_replace(__rgb2rgba("89abcd"),__rgb2rgba($color8),$buffer);
$buffer=str_replace(__rgb2rgba("9abcde"),__rgb2rgba($color9),$buffer);
$buffer=str_replace(__rgb2rgba("abcdef"),__rgb2rgba($colora),$buffer);
file_put_contents($dest."/".$file,$buffer);
?>