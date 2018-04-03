<?php
if($argc!=3) die("Unknown arguments!!!\n");
$color=$argv[1];
$div=$argv[2];
$color=str_split($color,2);
foreach($color as $key=>$val) {
	$val=hexdec($val);
	$val=$val/$div;
	$val=min($val,255);
	$val=max($val,0);
	$val=sprintf("%02x",$val);
	$color[$key]=$val;
}
$color=implode("",$color);
echo $color."\n";
?>