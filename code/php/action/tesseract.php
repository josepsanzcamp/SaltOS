<?php
include("php/unoconv.php");
$txt=__unoconv_pdf2ocr("files/201404231157.pdf");
//~ $txt=__unoconv_pdf2ocr("files/11941051.pdf");
//~ $txt=htmlentities($txt,ENT_COMPAT,"UTF-8");

echo "<pre>";
echo $txt;
echo "</pre>";
die();
?>