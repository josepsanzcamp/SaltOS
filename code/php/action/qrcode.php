<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2011 by Josep Sanz Campderrós
More information in http://www.saltos.net or info@saltos.net

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
// OBTAIN THE VCARD
if(getParam("msg")) {
	$vcard=getParam("msg");
} elseif(getParam("page") && getParam("id")) {
	ob_start();
	define("__CANCEL_DIE__",1);
	define("__CANCEL_HEADER__",1);
	define("__CANCEL_FULL__",1);
	include("php/action/vcard.php");
	$vcard=ob_get_clean();
} else {
	action_denied();
}
$vcard=str_pad($vcard,78);
// BEGIN THE QRCODE WRAPPER
$cache=get_cache_file($vcard,getDefault("exts/pngext",".png"));
//if(file_exists($cache)) unlink($cache);
if(!file_exists($cache)) {
	require_once("lib/qrcode/qrcode.class.php");
	$count=0;
	while($count<10) {
		capture_next_error();
		$qrcode=new QRcode($vcard.str_repeat(" ",$count),"L"); // L, M, Q or H
		if(!get_clear_error()) break;
		$count++;
	}
	//$qrcode->disableBorder();
	$size=($qrcode->getQrSize())*6; // SIZE OF POINT = 6
	ob_start();
	$qrcode->displayPNG($size);
	$image=ob_get_clean();
	// ADD SALTOS LOGO
	$ps=6;
	$im=imagecreatefromstring($image);
	$xx=imagesx($im)/2-10*$ps/2+$ps/2;
	$yy=imagesy($im)/2-10*$ps/2-$ps/2;
	$cc=array(0,imagecolorallocate($im,0xb8,0x14,0x15),imagecolorallocate($im,0x00,0x00,0x00));
	$matrix=array(array(0,0,0,0,2,2,2,0,0,0),array(0,0,0,0,2,1,2,2,2,2),array(0,2,2,2,2,2,2,2,1,2),array(0,2,1,1,1,1,1,1,2,2),array(0,2,2,1,1,1,1,2,2,0),
		array(0,0,2,2,1,1,1,1,2,2),array(0,0,2,2,1,2,2,2,1,2),array(0,2,2,1,2,2,0,2,2,2),array(0,2,1,2,2,0,0,0,0,0),array(0,2,2,2,0,0,0,0,0,0));
	foreach($matrix as $y=>$xz) foreach($xz as $x=>$z) if($z) imagefilledrectangle($im,$xx+$x*$ps,$yy+$y*$ps,$xx+($x+1)*$ps-1,$yy+($y+1)*$ps-1,$cc[$z]);
	imagepng($im,$cache);
	chmod_protected($cache,0666);
}
ob_start(getDefault("obhandler"));
header_powered();
header_expires(false);
$type=content_type_from_extension($cache);
header("Content-Type: $type");
readfile($cache);
ob_end_flush();
die();
?>