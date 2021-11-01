<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2021 by Josep Sanz Campderrós
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

if (!check_user()) {
    action_denied();
}

// OBTAIN THE MSG
if (getParam("msg")) {
    $msg = getParam("msg");
} else {
    action_denied();
}
// DEFAULT PARAMETERS
$w = intval(getParam("w", 1));
$h = intval(getParam("h", 30));
$m = intval(getParam("m", 10));
$s = intval(getParam("s", 8));
$t = getParam("t", "C39");
// BEGIN THE BARCODE WRAPPER
$cache = get_cache_file(array($msg,$w,$h,$m,$s,$t), ".png");
//~ if(file_exists($cache)) unlink($cache);
if (!file_exists($cache)) {
    require_once "lib/tcpdf/tcpdf_barcodes_1d.php";
    $barcode = new TCPDFBarcode($msg, $t);
    $array = $barcode->getBarcodeArray();
    if (!isset($array["maxw"])) {
        action_denied();
    }
    $width = $array["maxw"] * $w;
    $height = $h;
    $extra = $s;
    if ($s) {
        $font = getcwd() . "/lib/fonts/DejaVuSans.ttf";
        $bbox = imagettfbbox($s, 0, $font, $msg);
        $extra = abs($bbox[5] - $bbox[1]) + $m;
    }
    $im = imagecreatetruecolor($width + 2 * $m, $height + 2 * $m + $extra);
    $bgcol = imagecolorallocate($im, 255, 255, 255);
    imagefilledrectangle($im, 0, 0, $width + 2 * $m, $height + 2 * $m + $extra, $bgcol);
    $fgcol = imagecolorallocate($im, 0, 0, 0);
    $x = 0;
    foreach ($array["bcode"] as $key => $val) {
        $bw = round(($val["w"] * $w), 3);
        $bh = round(($val["h"] * $h / $array["maxh"]), 3);
        if ($val["t"]) {
            $y = round(($val["p"] * $h / $array["maxh"]), 3);
            imagefilledrectangle($im, $x + $m, $y + $m, ($x + $bw - 1) + $m, ($y + $bh - 1) + $m, $fgcol);
        }
        $x += $bw;
    }
    if ($s) {
        // ADD MSG TO THE IMAGE FOOTER
        $px = ($width + 2 * $m) / 2 - ($bbox[4] - $bbox[0]) / 2;
        $py = $m + $h + 1 + $m + $s;
        imagettftext($im, $s, 0, $px, $py, $fgcol, $font, $msg);
    }
    // CONTINUE
    imagepng($im, $cache);
    imagedestroy($im);
    chmod_protected($cache, 0666);
}
if (!defined("__CANCEL_DIE__")) {
    output_handler(array(
        "file" => $cache,
        "cache" => false
    ));
} else {
    readfile($cache);
}
