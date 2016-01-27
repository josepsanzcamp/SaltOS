<?php
declare(ticks=1000);
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2015 by Josep Sanz Campderrós
More information in http://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
if(getParam("action")=="captcha") {
	/*
	Idea original para programar este captcha obtenida de este post:
	- http://sentidoweb.com/2007/01/03/laboratorio-ejemplo-de-captcha.php
	Tambien aparece en otros posts buscando en google:
	- http://www.google.es/search?q=captcha+alto_linea
	*/
	require_once("php/libaction.php");
	$id=getDefault("captcha/id","captcha");
	$width=getDefault("captcha/width",90);
	$height=getDefault("captcha/height",45);
	$letter=getDefault("captcha/letter",8);
	$number=getDefault("captcha/number",16);
	$angle=getDefault("captcha/angle",10);
	$color=getDefault("captcha/color","5C8ED1");
	$bgcolor=getDefault("captcha/bgcolor","C8C8C8");
	$fgcolor=getDefault("captcha/fgcolor","B4B4B4");
	$type=getDefault("captcha/type","math");
	$length=getDefault("captcha/length",5);
	$period=getDefault("captcha/period",2);
	$amplitude=getDefault("captcha/amplitude",8);
	$blur=getDefault("captcha/blur","true");
	init_random();
	// DEFINE THE CODE AND REAL CAPTCHA
	if($type=="number") {
		$code=str_pad(rand(0,pow(10,$length)-1),$length,"0",STR_PAD_LEFT);
		sess_init();
		useSession($id,$code);
		sess_close();
	} elseif($type=="math") {
		$max=pow(10,round($length/2))-1;
		do {
			do {
				$num1=rand(0,$max);
			} while(!__captcha_isprime($num1));
			$oper=rand(0,1)?"+":"-";
			do {
				$num2=rand(0,$max);
				$code=$num1.$oper.$num2;
			} while(!__captcha_isprime($num2) || substr($num2,0,1)=="7" || strlen($code)!=$length);
		} while($oper=="-" && $num1<$num2);
		$real=eval("return $code;");
		sess_init();
		useSession($id,$real);
		sess_close();
	} else {
		action_denied();
	}
	// CREATE THE BACKGROUND IMAGE
	$im=imagecreatetruecolor($width,$height);
	$color2=imagecolorallocate($im,__captcha_color2dec($color,"R"),__captcha_color2dec($color,"G"),__captcha_color2dec($color,"B"));
	$bgcolor2=imagecolorallocate($im,__captcha_color2dec($bgcolor,"R"),__captcha_color2dec($bgcolor,"G"),__captcha_color2dec($bgcolor,"B"));
	$fgcolor2=imagecolorallocate($im,__captcha_color2dec($fgcolor,"R"),__captcha_color2dec($fgcolor,"G"),__captcha_color2dec($fgcolor,"B"));
	imagefill($im,0,0,$bgcolor2);
	$letters="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	$font=getcwd()."/lib/fonts/GorriSans.ttf";
	$bbox=imagettfbbox($letter,0,$font,$letters[0]);
	$heightline=abs($bbox[7]-$bbox[1]);
	$numlines=intval($height/$heightline)+1;
	$maxletters=strlen($letters);
	for($i=0;$i<$numlines;$i++) {
		$posx=0;
		$posy=($heightline/2)+($heightline+$letter/4)*$i;
		while($posx<$width) {
			$oneletter=$letters[rand(0,$maxletters-1)];
			$oneangle=rand(-$angle,$angle);
			$bbox=imagettfbbox($letter,$oneangle,$font,$oneletter);
			imagettftext($im,$letter,rand(-$angle,$angle),$posx,$posy,$fgcolor2,$font,$oneletter);
			$posx+=$bbox[2]-$bbox[0]+$letter/4;
		}
	}
	// CREATE THE CAPTCHA CODE
	$im2=imagecreatetruecolor($width,$height);
	$color2=imagecolorallocate($im2,__captcha_color2dec($color,"R"),__captcha_color2dec($color,"G"),__captcha_color2dec($color,"B"));
	$bgcolor2=imagecolorallocate($im2,__captcha_color2dec($bgcolor,"R"),__captcha_color2dec($bgcolor,"G"),__captcha_color2dec($bgcolor,"B"));
	$fgcolor2=imagecolorallocate($im2,__captcha_color2dec($fgcolor,"R"),__captcha_color2dec($fgcolor,"G"),__captcha_color2dec($fgcolor,"B"));
	imagefill($im2,0,0,$bgcolor2);
	imagecolortransparent($im2,$bgcolor2);
	$angles=array();
	$widths=array();
	$heights=array();
	$widthsum=0;
	for($i=0;$i<strlen($code);$i++) {
		$angles[$i]=rand(-$angle,$angle);
		$bbox=imagettfbbox($number,$angles[$i],$font,$code[$i]);
		$widths[$i]=abs($bbox[2]-$bbox[0]);
		$heights[$i]=abs($bbox[7]-$bbox[1]);
		$widthsum+=$widths[$i];
	}
	$widthmiddle=$width/2;
	$heightmiddle=$height/2;
	$posx=$widthmiddle-$widthsum/2;
	for($i=0;$i<strlen($code);$i++) {
		$posy=$heights[$i]/2+$heightmiddle;
		imagettftext($im2,$number,$angles[$i],$posx,$posy,$color2,$font,$code[$i]);
		$posx+=$widths[$i];
	}
	// COPY THE CODE TO BACKGROUND USING WAVE TRANSFORMATION
	$rel=3.1416/180;
	$inia=rand(0,360);
	$inib=rand(0,360);
	for($i=0;$i<$width;$i++) {
		$a=sin((($i*$period)+$inia)*$rel)*$amplitude;
		for($j=0;$j<$height;$j++) {
			$b=sin((($j*$period)+$inib)*$rel)*$amplitude;
			if($i+$b>=0 && $i+$b<$width && $j+$a>=0 && $j+$a<$height) imagecopymerge($im,$im2,$i,$j,$i+$b,$j+$a,1,1,100);
		}
	}
	// APPLY BLUR
	if(eval_bool($blur)) if(function_exists("imagefilter")) imagefilter($im,IMG_FILTER_GAUSSIAN_BLUR);
	// CONTINUE
	ob_start();
	imagepng($im);
	$buffer=ob_get_clean();
	imagedestroy($im);
	imagedestroy($im2);
	// OUTPUT IMAGE
	output_handler(array(
		"data"=>$buffer,
		"type"=>"image/png",
		"cache"=>false
	));
}
?>