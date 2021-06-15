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

// phpcs:disable PSR1.Files.SideEffects

if (getParam("action") == "qrcode") {
    // OBTAIN THE MSG
    if (getParam("msg")) {
        $msg = getParam("msg");
    } elseif (getParam("page") && getParam("id")) {
        ob_start();
        if (!defined("__CANCEL_DIE__")) {
            define("__CANCEL_DIE__", 1);
        }
        require "php/action/vcard.php";
        $msg = ob_get_clean();
    } else {
        action_denied();
    }
    // DEFAULT PARAMETERS
    $s = intval(getParam("s", 6));
    $m = intval(getParam("m", 10));
    // BEGIN THE QRCODE WRAPPER
    $cache = get_cache_file($msg, ".png");
    //~ if(file_exists($cache)) unlink($cache);
    if (!file_exists($cache)) {
        require_once "lib/tcpdf/tcpdf_barcodes_2d.php";
        $levels = array("L","M","Q","H");
        $factors = array(0.07,0.15,0.25,0.30);
        for ($i = 0; $i < 4; $i++) {
            $barcode = new TCPDF2DBarcode($msg, "QRCODE," . $levels[$i]);
            $array = $barcode->getBarcodeArray();
            if (!isset($array["num_cols"]) || !isset($array["num_rows"])) {
                action_denied();
            }
            $total = $array["num_cols"] * $array["num_rows"];
            if ($total * $factors[$i] > 100 + $factors[$i] * 100) {
                break;
            }
        }
        $width = ($array["num_cols"] * $s);
        $height = ($array["num_rows"] * $s);
        $im = imagecreatetruecolor($width + 2 * $m, $height + 2 * $m);
        $bgcol = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, $width + 2 * $m, $height + 2 * $m, $bgcol);
        $fgcol = imagecolorallocate($im, 0, 0, 0);
        foreach ($array["bcode"] as $key => $val) {
            foreach ($val as $key2 => $val2) {
                if ($val2) {
                    imagefilledrectangle(
                        $im,
                        $key2 * $s + $m,
                        $key * $s + $m,
                        ($key2 + 1) * $s + $m - 1,
                        ($key + 1) * $s + $m - 1,
                        $fgcol
                    );
                }
            }
        }
        // ADD SALTOS LOGO
        $matrix = array(
            array(0,0,0,0,2,2,2,0,0,0),
            array(0,0,0,0,2,1,2,2,2,2),
            array(0,2,2,2,2,2,2,2,1,2),
            array(0,2,1,1,1,1,1,1,2,2),
            array(0,2,2,1,1,1,1,2,2,0),
            array(0,0,2,2,1,1,1,1,2,2),
            array(0,0,2,2,1,2,2,2,1,2),
            array(0,2,2,1,2,2,0,2,2,2),
            array(0,2,1,2,2,0,0,0,0,0),
            array(0,2,2,2,0,0,0,0,0,0),
        );
        $ww = intval(count($matrix[0]) / 2) * 2;
        $hh = intval(count($matrix) / 2) * 2;
        $xx = imagesx($im) / 2 - $ww * $s / 2 + $s / 2;
        $yy = imagesy($im) / 2 - $hh * $s / 2 - $s / 2;
        $cc = array(0,imagecolorallocate($im, 0xb8, 0x14, 0x15),imagecolorallocate($im, 0x00, 0x00, 0x00));
        foreach ($matrix as $y => $xz) {
            foreach ($xz as $x => $z) {
                if ($z) {
                    imagefilledrectangle(
                        $im,
                        $xx + $x * $s,
                        $yy + $y * $s,
                        $xx + ($x + 1) * $s - 1,
                        $yy + ($y + 1) * $s - 1,
                        $cc[$z]
                    );
                }
            }
        }
        // CONTINUE
        imagepng($im, $cache);
        imagedestroy($im);
        chmod_protected($cache, 0666);
    }
    output_handler(array(
        "file" => $cache,
        "cache" => false
    ));
}
