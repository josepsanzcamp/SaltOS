<?php
if($argc!=3) die("Unknown arguments!!!\n");
$color=$argv[1];
$factor=$argv[2];
// https://www.rapidtables.com/convert/color/rgb-to-hsl.html
$color=str_split($color,2);
foreach($color as $key=>$val) {
	$val=hexdec($val);
	$val=$val/255;
	$color[$key]=$val;
}
$cmax=max($color);
$cmin=min($color);
$cdel=$cmax-$cmin;
if($cdel==0) {
	$h=0;
} elseif($cmax==$color[0]) {
	$h=60*(fmod((($color[1]-$color[2])/$cdel),6));
} elseif($cmax==$color[1]) {
	$h=60*((($color[2]-$color[0])/$cdel)+2);
} elseif($cmax==$color[2]) {
	$h=60*((($color[0]-$color[1])/$cdel)+4);
}
if($h<0) $h+=360;
if($h>=360) $h-=360;
$l=($cmax+$cmin)/2;
if($cdel==0) {
	$s=0;
} else {
	$s=($cdel/(1-abs(2*$l-1)));
}
// compute the lightness
$l=$factor;
if($l<0) $l=0;
if($l>1) $l=1;
// https://www.rapidtables.com/convert/color/hsl-to-rgb.html
$c=(1-abs(2*$l-1))*$s;
$x=$c*(1-abs(fmod(($h/60),2)-1));
$m=$l-$c/2;
if(0<=$h && $h<60) {
	$color=array($c,$x,0);
} elseif(60<=$h && $h<120) {
	$color=array($x,$c,0);
} elseif(120<=$h && $h<180) {
	$color=array(0,$c,$x);
} elseif(180<=$h && $h<240) {
	$color=array(0,$x,$c);
} elseif(240<=$h && $h<300) {
	$color=array($x,0,$c);
} elseif(300<=$h && $h<360) {
	$color=array($c,0,$x);
}
foreach($color as $key=>$val) {
	$val=($val+$m);
	$val=$val*255;
	$val=intval($val);
	$val=min($val,255);
	$val=max($val,0);
	$val=sprintf("%02x",$val);
	$color[$key]=$val;
}
$color=implode("",$color);
echo $color."\n";
?>