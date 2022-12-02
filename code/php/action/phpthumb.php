<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2022 by Josep Sanz Campderrós
More information in https://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

// INCLUDE HELPER LIBRARIES
if (!function_exists("imagebmp")) {
    require_once "lib/phpclasses/bmpphp/BMP.php";
}
require_once "php/libaction.php";
// FIND THE REAL FILE
$src = getParam("src", getParam("amp;src"));
if (!file_exists($src)) {
    $src = getcwd() . "/" . getParam("src", getParam("amp;src"));
}
if (!file_exists($src)) {
    $src = get_directory("dirs/filesdir") . getParam("src", getParam("amp;src"));
}
if (!file_exists($src)) {
    action_denied();
}
// PARSE PARAMETERS
$width = null;
if (getParam("w", getParam("amp;w"))) {
    $width = intval(getParam("w", getParam("amp;w")));
    if ($width < 1 || $width > 2000) {
        action_denied();
    }
}
$height = null;
if (getParam("h", getParam("amp;h"))) {
    $height = intval(getParam("h", getParam("amp;h")));
    if ($height < 1 || $height > 2000) {
        action_denied();
    }
}
// SECURITY CHECK
$type = saltos_content_type($src);
$type0 = saltos_content_type0($type);
if ($type0 != "image") {
    action_denied();
}
// CONTINUE
$format_input = saltos_content_type1($type);
$format_output = getParam("f", getParam("amp;f", "png"));
// PREPARE CACHE FILENAME
$temp = get_directory("dirs/cachedir");
$hash = md5(serialize(array($src,$width,$height)));
$cache = "{$temp}{$hash}.{$format_output}";
// FOR DEBUG PURPOSES
//~ if(file_exists($cache)) unlink($cache);
// CREATE IF NOT EXISTS
if (!cache_exists($cache, $src)) {
    // LOAD IMAGE
    switch ($format_input) {
        case "png":
            $im = imagecreatefrompng($src);
            break;
        case "jpeg":
            $im = imagecreatefromjpeg($src);
            break;
        case "gif":
            $im = imagecreatefromgif($src);
            break;
        case "bmp":
            $im = imagecreatefrombmp($src);
            break;
        case "tiff":
            $im = __phpthumb_imagecreatefromtiff($src);
            break;
        case "webp":
            $im = imagecreatefromwebp($src);
            break;
        default:
            show_php_error(array("phperror" => "Unsupported input format: {$format_input}"));
    }
    // CALCULATE SIZE
    if ($width !== null && $height !== null && (imagesx($im) > $width || imagesy($im) > $height)) {
        $width2 = (int)(imagesx($im) * $height / imagesy($im));
        $height2 = (int)(imagesy($im) * $width / imagesx($im));
        if ($width2 > $width) {
            $height = $height2;
        }
        if ($height2 > $height) {
            $width = $width2;
        }
    } elseif ($width === null && $height !== null && imagesy($im) > $height) {
        $width = (int)(imagesx($im) * $height / imagesy($im));
    } elseif ($width !== null && $height === null && imagesx($im) > $width) {
        $height = (int)(imagesy($im) * $width / imagesx($im));
    } else {
        $width = imagesx($im);
        $height = imagesy($im);
    }
    // SECURITY CHECK
    if ($width < 1 || $width > 2000) {
        action_denied();
    }
    if ($height < 1 || $height > 2000) {
        action_denied();
    }
    // DO RESIZE
    $im2 = imagecreatetruecolor($width, $height);
    $tr = imagecolortransparent($im);
    if ($tr >= 0) {
        $tr = imagecolorsforindex($im, $tr);
        $tr = imagecolorallocate($im2, $tr["red"], $tr["green"], $tr["blue"]);
        imagecolortransparent($im2, $tr);
        imagefilledrectangle($im2, 0, 0, $width, $height, $tr);
    } else {
        imagealphablending($im2, false);
        imagesavealpha($im2, true);
        $tr = imagecolorallocatealpha($im2, 0, 0, 0, 127);
        imagefilledrectangle($im2, 0, 0, $width, $height, $tr);
    }
    imagecopyresampled($im2, $im, 0, 0, 0, 0, $width, $height, imagesx($im), imagesy($im));
    imagedestroy($im);
    // WRITE
    switch ($format_output) {
        case "png":
            imagepng($im2, $cache);
            break;
        case "jpeg":
            imagejpeg($im2, $cache);
            break;
        case "gif":
            imagegif($im2, $cache);
            break;
        case "bmp":
            imagebmp($im2, $cache);
            break;
        case "webp":
            imagewebp($im2, $cache);
            break;
        default:
            show_php_error(array("phperror" => "Unsupported output format: {$format_output}"));
    }
    imagedestroy($im2);
    chmod($cache, 0666);
}
output_handler(array(
    "file" => $cache,
    "cache" => true
));
