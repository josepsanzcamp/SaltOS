<?php
include("php/unoconv.php");

$test=getParam("test");

if($test==1) {
	$txt=__unoconv_pdf2ocr("files/201404231157.pdf");
}

if($test==2) {
	$txt=__unoconv_pdf2ocr("files/11941051.pdf");
}

if($test==3) {
	$txt=array();
	$txt[]=file_get_contents(__unoconv_img2ocr("files/todolinux-000.tif"));
	$txt[]=file_get_contents(__unoconv_img2ocr("files/todolinux-001.tif"));
	$txt[]=file_get_contents(__unoconv_img2ocr("files/todolinux-002.tif"));
	$txt[]=file_get_contents(__unoconv_img2ocr("files/todolinux-003.tif"));
	$txt=implode("\n",$txt);
}

$txt=htmlentities($txt,ENT_COMPAT,"UTF-8");
echo "<pre>";
echo $txt;
echo "</pre>";
die();
?>