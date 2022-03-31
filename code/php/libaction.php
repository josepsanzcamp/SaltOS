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

// phpcs:disable Generic.Files.LineLength

function __cache_resolve_path($buffer, $file)
{
    // RESOLVE FULL PATH FOR ALL BACKGROUNDS IMAGES
    $dirname_file = dirname($file) . "/";
    $pos = strpos($buffer, "url(");
    while ($pos !== false) {
        $pos2 = strpos($buffer, ")", $pos + 4);
        $img = substr($buffer, $pos + 4, $pos2 - $pos - 4);
        if (in_array(substr($img, 0, 1), array("'",'"'))) {
            $img = substr($img, 1);
        }
        if (in_array(substr($img, -1, 1), array("'",'"'))) {
            $img = substr($img, 0, -1);
        }
        $newimg = semi_realpath($dirname_file . strtok(strtok($img, "?"), "#"));
        if (file_exists($newimg)) {
            $buffer = substr_replace($buffer, $newimg, $pos + 4, $pos2 - $pos - 4);
            $pos2 = $pos2 - strlen($img) + strlen($newimg);
        }
        $pos = strpos($buffer, "url(", $pos2 + 1);
    }
    return $buffer;
}

function __captcha_color2dec($color, $component)
{
    $offset = array("R" => 0,"G" => 2,"B" => 4);
    if (!isset($offset[$component])) {
        show_php_error(array("phperror" => "Unknown component"));
    }
    return hexdec(substr($color, $offset[$component], 2));
}

function __captcha_isprime($num)
{
    // SEE www.polprimos.com FOR UNDERSTAND IT
    if ($num < 2) {
        return false;
    }
    if ($num % 2 == 0 && $num != 2) {
        return false;
    }
    if ($num % 3 == 0 && $num != 3) {
        return false;
    }
    if ($num % 5 == 0 && $num != 5) {
        return false;
    }
    // PRIMER NUMBERS ARE DISTRIBUTED IN 8 COLUMNS
    $div = 7;
    $max = intval(sqrt($num));
    while (1) {
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 4;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 2;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 4;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 2;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 4;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 6;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 2;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 6;
    }
    return true;
}

function __captcha_image($code, $args = array())
{
    // Idea original para programar este captcha obtenida de este post:
    // - http://sentidoweb.com/2007/01/03/laboratorio-ejemplo-de-captcha.php
    // Tambien aparece en otros posts buscando en google:
    // - http://www.google.es/search?q=captcha+alto_linea
    if (!is_string($code)) {
        $code = strval($code);
    }
    $width = isset($args["width"]) ? $args["width"] : getDefault("captcha/width", 90);
    $height = isset($args["height"]) ? $args["height"] : getDefault("captcha/height", 45);
    $letter = isset($args["letter"]) ? $args["letter"] : getDefault("captcha/letter", 8);
    $number = isset($args["number"]) ? $args["number"] : getDefault("captcha/number", 16);
    $angle = isset($args["angle"]) ? $args["angle"] : getDefault("captcha/angle", 10);
    $color = isset($args["color"]) ? $args["color"] : getDefault("captcha/color", "5C8ED1");
    $bgcolor = isset($args["bgcolor"]) ? $args["bgcolor"] : getDefault("captcha/bgcolor", "C8C8C8");
    $fgcolor = isset($args["fgcolor"]) ? $args["fgcolor"] : getDefault("captcha/fgcolor", "B4B4B4");
    $period = isset($args["period"]) ? $args["period"] : getDefault("captcha/period", 2);
    $amplitude = isset($args["amplitude"]) ? $args["amplitude"] : getDefault("captcha/amplitude", 8);
    $blur = isset($args["blur"]) ? $args["blur"] : getDefault("captcha/blur", "true");
    // CREATE THE BACKGROUND IMAGE
    $im = imagecreatetruecolor($width, $height);
    $color2 = imagecolorallocate(
        $im,
        __captcha_color2dec($color, "R"),
        __captcha_color2dec($color, "G"),
        __captcha_color2dec($color, "B")
    );
    $bgcolor2 = imagecolorallocate(
        $im,
        __captcha_color2dec($bgcolor, "R"),
        __captcha_color2dec($bgcolor, "G"),
        __captcha_color2dec($bgcolor, "B")
    );
    $fgcolor2 = imagecolorallocate(
        $im,
        __captcha_color2dec($fgcolor, "R"),
        __captcha_color2dec($fgcolor, "G"),
        __captcha_color2dec($fgcolor, "B")
    );
    imagefill($im, 0, 0, $bgcolor2);
    $letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    $font = getcwd() . "/lib/fonts/GorriSans.ttf";
    $bbox = imagettfbbox($letter, 0, $font, $letters[0]);
    $heightline = abs($bbox[7] - $bbox[1]);
    $numlines = intval($height / $heightline) + 1;
    $maxletters = strlen($letters);
    for ($i = 0; $i < $numlines; $i++) {
        $posx = 0;
        $posy = ($heightline / 2) + ($heightline + $letter / 4) * $i;
        while ($posx < $width) {
            $oneletter = $letters[rand(0, $maxletters - 1)];
            $oneangle = rand(-$angle, $angle);
            $bbox = imagettfbbox($letter, $oneangle, $font, $oneletter);
            imagettftext($im, $letter, rand(-$angle, $angle), (int)$posx, (int)$posy, $fgcolor2, $font, $oneletter);
            $posx += $bbox[2] - $bbox[0] + $letter / 4;
        }
    }
    // CREATE THE CAPTCHA CODE
    $im2 = imagecreatetruecolor($width, $height);
    $color2 = imagecolorallocate(
        $im2,
        __captcha_color2dec($color, "R"),
        __captcha_color2dec($color, "G"),
        __captcha_color2dec($color, "B")
    );
    $bgcolor2 = imagecolorallocate(
        $im2,
        __captcha_color2dec($bgcolor, "R"),
        __captcha_color2dec($bgcolor, "G"),
        __captcha_color2dec($bgcolor, "B")
    );
    $fgcolor2 = imagecolorallocate(
        $im2,
        __captcha_color2dec($fgcolor, "R"),
        __captcha_color2dec($fgcolor, "G"),
        __captcha_color2dec($fgcolor, "B")
    );
    imagefill($im2, 0, 0, $bgcolor2);
    imagecolortransparent($im2, $bgcolor2);
    $angles = array();
    $widths = array();
    $heights = array();
    $widthsum = 0;
    for ($i = 0; $i < strlen($code); $i++) {
        $angles[$i] = rand(-$angle, $angle);
        $bbox = imagettfbbox($number, $angles[$i], $font, $code[$i]);
        $widths[$i] = abs($bbox[2] - $bbox[0]);
        $heights[$i] = abs($bbox[7] - $bbox[1]);
        $widthsum += $widths[$i];
    }
    $widthmiddle = $width / 2;
    $heightmiddle = $height / 2;
    $posx = $widthmiddle - $widthsum / 2;
    for ($i = 0; $i < strlen($code); $i++) {
        $posy = $heights[$i] / 2 + $heightmiddle;
        imagettftext($im2, $number, $angles[$i], (int)$posx, (int)$posy, $color2, $font, $code[$i]);
        $posx += $widths[$i];
    }
    // COPY THE CODE TO BACKGROUND USING WAVE TRANSFORMATION
    $rel = M_PI / 180;
    $inia = rand(0, 360);
    $inib = rand(0, 360);
    for ($i = 0; $i < $width; $i++) {
        $a = sin((($i * $period) + $inia) * $rel) * $amplitude;
        for ($j = 0; $j < $height; $j++) {
            $b = sin((($j * $period) + $inib) * $rel) * $amplitude;
            if ($i + $b >= 0 && $i + $b < $width && $j + $a >= 0 && $j + $a < $height) {
                imagecopymerge($im, $im2, $i, $j, (int)($i + $b), (int)($j + $a), 1, 1, 100);
            }
        }
    }
    // APPLY BLUR
    if (eval_bool($blur)) {
        if (function_exists("imagefilter")) {
            imagefilter($im, IMG_FILTER_GAUSSIAN_BLUR);
        }
    }
    // CONTINUE
    ob_start();
    imagepng($im);
    $buffer = ob_get_clean();
    imagedestroy($im);
    imagedestroy($im2);
    return $buffer;
}

function __excel_dump($matrix, $file, $title = "")
{
    require_once "php/export.php";
    $buffer = export_file(array(
        "type" => "xlsx",
        "data" => $matrix,
        "title" => $title,
    ));
    output_handler(array(
        "data" => $buffer,
        "type" => "application/x-excel",
        "cache" => false,
        "name" => $file
    ));
}

function __csv_dump($matrix, $file)
{
    require_once "php/export.php";
    $buffer = export_file(array(
        "type" => "csv",
        "data" => $matrix,
    ));
    output_handler(array(
        "data" => $buffer,
        "type" => "text/csv",
        "cache" => false,
        "name" => $file
    ));
}

function __matrix2dump($matrix, $file, $title)
{
    if (substr($file, -4, 4) == ".csv") {
        $file = substr($file, 0, -4);
    }
    if (substr($file, -4, 4) == ".xls") {
        $file = substr($file, 0, -4);
    }
    if (substr($file, -5, 5) == ".xlsx") {
        $file = substr($file, 0, -5);
    }
    if (count($matrix) <= 10000) {
        __excel_dump($matrix, $file . ".xlsx", $title);
    } else {
        __csv_dump($matrix, $file . ".csv");
    }
}

function __query2matrix($query)
{
    $result = db_query($query);
    $matrix = array(array());
    for ($i = 0; $i < db_num_fields($result); $i++) {
        $matrix[0][] = db_field_name($result, $i);
    }
    while ($row = db_fetch_row($result)) {
        $matrix[] = array_values($row);
    }
    db_free($result);
    return $matrix;
}

function __query2dump($query, $file)
{
    require_once "php/export.php";
    $offset = 0;
    $limit = 100000;
    $buffer = "";
    while (1) {
        $result = db_query("$query LIMIT $offset,$limit");
        if (!db_num_rows($result)) {
            db_free($result);
            break;
        }
        $matrix = array(array());
        if (!$offset) {
            for ($i = 0; $i < db_num_fields($result); $i++) {
                $matrix[0][] = db_field_name($result, $i);
            }
        }
        while ($row = db_fetch_row($result)) {
            $matrix[] = array_values($row);
        }
        db_free($result);
        $buffer .= export_file(array(
            "type" => "csv",
            "data" => $matrix,
        ));
        $offset += $limit;
    }
    output_handler(array(
        "data" => $buffer,
        "type" => "text/csv",
        "cache" => false,
        "name" => $file
    ));
}

// FUNCTION THAT RETURNS THE META ATTRIBUTES
function __favoritos_explode_meta($html)
{
    $result = array();
    $len = strlen($html);
    $pos1 = strpos($html, "=");
    while ($pos1 !== false) {
        for ($i = $pos1 - 1; $i >= 0; $i--) {
            if ($html[$i] != " ") {
                break;
            }
        }
        for ($j = $i; $j >= 0; $j--) {
            if ($html[$j] == " ") {
                break;
            }
        }
        $pos2 = $j;
        for ($i = $pos1 + 1; $i < $len; $i++) {
            if ($html[$i] != " ") {
                break;
            }
        }
        for ($j = $i; $j < $len; $j++) {
            if ($html[$j] == '"' || $html[$j] == "'") {
                break;
            }
        }
        $pos3 = $j;
        for ($k = $j + 1; $k < $len; $k++) {
            if ($html[$j] == $html[$k]) {
                break;
            }
        }
        $pos4 = $k;
        $key = substr($html, $pos2 + 1, $pos1 - $pos2 - 1);
        $val = substr($html, $pos3 + 1, $pos4 - $pos3 - 1);
        $result[$key] = $val;
        $pos1 = strpos($html, "=", $pos1 + 1);
    }
    return $result;
}

// FUNCTION THAT RETURNS ALL META TAGS
function __favoritos_get_metas($html)
{
    $result = array();
    $pos1 = stripos($html, "<meta");
    while ($pos1 !== false) {
        $pos2 = stripos($html, ">", $pos1);
        if ($pos2 === false) {
            break;
        }
        $result[] = __favoritos_explode_meta(substr($html, $pos1, $pos2 - $pos1 + 1));
        $pos1 = stripos($html, "<meta", $pos2);
    }
    return $result;
}

function __feeds_getnode($path, $array)
{
    if (!is_array($path)) {
        $path = explode("/", $path);
    }
    $elem = array_shift($path);
    if (!is_array($array) || !isset($array[$elem])) {
        return null;
    }
    if (count($path) == 0) {
        return $array[$elem];
    }
    return __feeds_getnode($path, __feeds_getvalue($array[$elem]));
}

function __feeds_getvalue($array)
{
    return (is_array($array) && isset($array["value"]) && isset($array["#attr"])) ? $array["value"] : $array;
}

function __feeds_xml2array_helper($xml)
{
    require_once "php/import.php";
    $data = xml2struct($xml);
    $data = array_reverse($data);
    $array = __import_struct2array($data);
    return $array;
}

function __feeds_xml2array($xml)
{
    capture_next_error();
    $array = __feeds_xml2array_helper($xml);
    $error = get_clear_error();
    if (strpos($error, "Reserved XML Name") !== false) {
        $xml = trim($xml);
        capture_next_error();
        $array = __feeds_xml2array_helper($xml);
        $error = get_clear_error();
    }
    if (strpos($error, "Invalid document") !== false) {
        $xml = remove_script_tag($xml);
        capture_next_error();
        $array = __feeds_xml2array_helper($xml);
        $error = get_clear_error();
    }
    if (strpos($error, "XML_ERR_NAME_REQUIRED") !== false) {
        $xml = str_replace("&", "&amp;", $xml);
        capture_next_error();
        $array = __feeds_xml2array_helper($xml);
        $error = get_clear_error();
    }
    if (strpos($error, "EntityRef") !== false) {
        $xml = str_replace("&", "&amp;", $xml);
        capture_next_error();
        $array = __feeds_xml2array_helper($xml);
        $error = get_clear_error();
    }
    if (strpos($error, "Invalid character") !== false) {
        $xml = remove_bad_chars($xml);
        capture_next_error();
        $array = __feeds_xml2array_helper($xml);
        $error = get_clear_error();
    }
    if (strpos($error, "Invalid document end") !== false) {
        $error = ""; // KNOWN ISSUE
    }
    return array($array,$error);
}

function __feeds_detect($array)
{
    $keys = array_keys($array);
    if (isset($keys[0])) {
        if ($keys[0] == "rdf:RDF") {
            return "rdf";
        }
        if ($keys[0] == "rss") {
            return "rss2";
        }
        if ($keys[0] == "feed") {
            return "atom";
        }
    }
    return "unknown";
}

function __feeds_fetchmain($array)
{
    $type = __feeds_detect($array);
    $title = "";
    $link = "";
    $description = "";
    $image = "img/deffeed.png";
    if ($type == "rdf") {
        $title = getutf8(__feeds_getnode("rdf:RDF/channel/title", $array));
        $link = __feeds_getnode("rdf:RDF/channel/link", $array);
        $description = getutf8(__feeds_getnode("rdf:RDF/channel/description", $array));
        $image = __feeds_getnode("rdf:RDF/image/url", $array);
    } elseif ($type == "rss2") {
        $title = getutf8(__feeds_getnode("rss/channel/title", $array));
        $link = __feeds_getnode("rss/channel/link", $array);
        $description = getutf8(__feeds_getnode("rss/channel/description", $array));
        $image = __feeds_getnode("rss/channel/image/url", $array);
    } elseif ($type == "atom") {
        $title = getutf8(__feeds_getvalue(__feeds_getnode("feed/title", $array)));
        $link = __feeds_getnode("feed/link", $array);
        $count = 0;
        while ($link !== null) {
            $rel = __feeds_getnode("#attr/rel", $link);
            $type = __feeds_getnode("#attr/type", $link);
            if ($rel == "alternate" && $type == "text/html") {
                $link = __feeds_getnode("#attr/href", $link);
                break;
            }
            if ($rel == "alternate" && !isset($alternate)) {
                $alternate = __feeds_getnode("#attr/href", $link);
            }
            $count++;
            $link = __feeds_getnode("feed/link#${count}", $array);
        }
        if (!$link && isset($alternate)) {
            $link = $alternate;
        }
        // FIX FOR GOOGLE GROUPS
        if (!$link) {
            $link = __feeds_getnode("feed/id", $array);
        }
        $description = getutf8(__feeds_getvalue(__feeds_getnode("feed/subtitle", $array)));
    }
    $array = array("title" => $title,"link" => $link,"description" => $description,"image" => $image);
    foreach ($array as $key => $val) {
        $array[$key] = trim(null2string($val));
    }
    return $array;
}

function __feeds_fetchitems($array)
{
    require_once "php/getmail.php";
    $type = __feeds_detect($array);
    $items = array();
    if ($type == "rdf") {
        $item = __feeds_getvalue(__feeds_getnode("rdf:RDF/item", $array));
        $count = 0;
        while ($item !== null) {
            $title = __feeds_getnode("title", $item);
            if (is_array($title)) {
                $title = __array2xml_write_nodes($title);
                $title = getutf8($title);
                $title = html2text($title);
            } else {
                $title = getutf8($title);
            }
            $link = __feeds_getnode("link", $item);
            if (is_array($link)) {
                $link = array_shift($link);
            }
            $description = __feeds_getnode("description", $item);
            if (is_array($description)) {
                $description = __array2xml_write_nodes($description);
            }
            $description = getutf8($description);
            $pubdate = __feeds_getnode("dc:date", $item);
            if (is_array($pubdate)) {
                $pubdate = array_shift($pubdate);
            }
            if ($pubdate) {
                $pubdate = date("Y-m-d H:i:s", strtotime($pubdate));
            }
            $hash = md5(serialize(array($title,$pubdate,$description,$link)));
            if (!$pubdate) {
                $pubdate = current_datetime();
            }
            if ($title != "" && $link != "") {
                $items[] = array(
                    "title" => $title,
                    "link" => $link,
                    "description" => $description,
                    "pubdate" => $pubdate,
                    "hash" => $hash
                );
            }
            $count++;
            $item = __feeds_getvalue(__feeds_getnode("rdf:RDF/item#${count}", $array));
        }
    } elseif ($type == "rss2") {
        $item = __feeds_getvalue(__feeds_getnode("rss/channel/item", $array));
        $count = 0;
        while ($item !== null) {
            $title = __feeds_getnode("title", $item);
            if (is_array($title)) {
                $title = __array2xml_write_nodes($title);
                $title = getutf8($title);
                $title = html2text($title);
            } else {
                $title = getutf8($title);
            }
            $link = __feeds_getnode("link", $item);
            if (is_array($link)) {
                $link = array_shift($link);
            }
            $description = __feeds_getnode("description", $item);
            if (is_array($description)) {
                $description = __array2xml_write_nodes($description);
            }
            $description = getutf8($description);
            $pubdate = __feeds_getnode("pubDate", $item);
            if (is_array($pubdate)) {
                $pubdate = array_shift($pubdate);
            }
            if ($pubdate) {
                $pubdate = date("Y-m-d H:i:s", strtotime($pubdate));
            }
            $hash = md5(serialize(array($title,$pubdate,$description,$link)));
            if (!$pubdate) {
                $pubdate = current_datetime();
            }
            if ($title != "" && $link != "") {
                $items[] = array(
                    "title" => $title,
                    "link" => $link,
                    "description" => $description,
                    "pubdate" => $pubdate,
                    "hash" => $hash
                );
            }
            $count++;
            $item = __feeds_getvalue(__feeds_getnode("rss/channel/item#${count}", $array));
        }
    } elseif ($type == "atom") {
        $item = __feeds_getvalue(__feeds_getnode("feed/entry", $array));
        $count = 0;
        while ($item !== null) {
            $title = __feeds_getvalue(__feeds_getnode("title", $item));
            if (is_array($title)) {
                $title = __array2xml_write_nodes($title);
                $title = getutf8($title);
                $title = html2text($title);
            } else {
                $title = getutf8($title);
            }
            $link = __feeds_getnode("link", $item);
            $count2 = 0;
            while ($link !== null) {
                $rel = __feeds_getnode("#attr/rel", $link);
                $type = __feeds_getnode("#attr/type", $link);
                if ($rel == "alternate" && $type == "text/html") {
                    $link = __feeds_getnode("#attr/href", $link);
                    break;
                }
                if ($rel == "alternate" && !isset($alternate)) {
                    $alternate = __feeds_getnode("#attr/href", $link);
                }
                $count2++;
                $link = __feeds_getnode("link#${count2}", $item);
            }
            if (!$link && isset($alternate)) {
                $link = $alternate;
            }
            // FIX FOR GOOGLE GROUPS
            if (!$link) {
                $link = __feeds_getnode("#attr/href", __feeds_getnode("link", $item));
            }
            if (is_array($link)) {
                $link = array_shift($link);
            }
            // GET CONTENT (AND SUMMARY IS OPTIONAL IN SOME FEEDS)
            $summary = __feeds_getvalue(__feeds_getnode("summary", $item));
            if (is_array($summary)) {
                $summary = __array2xml_write_nodes($summary);
            }
            $summary = trim(null2string(getutf8($summary)));
            $content = __feeds_getvalue(__feeds_getnode("content", $item));
            if (is_array($content)) {
                $content = __array2xml_write_nodes($content);
            }
            $content = trim(null2string(getutf8($content)));
            // FIX SOME ISSUES ABOUT SOME HTML WITH NO TEXT CONTENT
            $summary_plain = trim(strip_tags($summary));
            if (!$summary_plain) {
                $summary = "";
            }
            $content_plain = trim(strip_tags($content));
            if (!$content_plain) {
                $content = "";
            }
            // IF THE SUMMARY IS A PREVIEW OF THE CONTENT, REMOVE IT
            if (
                $summary_plain && $content_plain &&
                strncmp($summary_plain, $content_plain, min(strlen($summary_plain), strlen($content_plain))) == 0
            ) {
                if (strlen($summary_plain) < strlen($content_plain)) {
                    $summary = "";
                } else {
                    $content = "";
                }
            }
            // TRUE, PREPARE THE DESCRIPTION TO USE IN APPLICATION
            $description = "";
            if ($summary && $content) {
                $description = $summary . __HTML_SEPARATOR__ . $content;
            }
            if ($summary && !$content) {
                $description = $summary;
            }
            if (!$summary && $content) {
                $description = $content;
            }
            // CONTINUE
            $pubdate = __feeds_getnode("updated", $item);
            if (is_array($pubdate)) {
                $pubdate = array_shift($pubdate);
            }
            if ($pubdate) {
                $pubdate = date("Y-m-d H:i:s", strtotime($pubdate));
            }
            $hash = md5(serialize(array($title,$pubdate,$description,$link)));
            if (!$pubdate) {
                $pubdate = current_datetime();
            }
            if ($title != "" && $link != "") {
                $items[] = array(
                    "title" => $title,
                    "link" => $link,
                    "description" => $description,
                    "pubdate" => $pubdate,
                    "hash" => $hash
                );
            }
            $count++;
            $item = __feeds_getvalue(__feeds_getnode("feed/entry#${count}", $array));
        }
    }
    foreach ($items as $key => $val) {
        foreach ($val as $key2 => $val2) {
            $items[$key][$key2] = trim($val2);
        }
    }
    return $items;
}

function __folders_update_tree($id_usuario, $id_parent = 0, &$pos = 0, $depth = 0)
{
    $query = "SELECT id
        FROM tbl_folders
        WHERE id_usuario='${id_usuario}'
            AND id_parent='${id_parent}'
        ORDER BY name ASC";
    $result = db_query($query);
    while ($row = db_fetch_row($result)) {
        $id = $row["id"];
        $query = make_update_query("tbl_folders", array(
            "pos" => $pos,
            "depth" => $depth
        ), "id_usuario='${id_usuario}' AND id=${id}");
        db_query($query);
        $pos++;
        __folders_update_tree($id_usuario, $row["id"], $pos, $depth + 1);
    }
    db_free($result);
}

// FUNCTIONS FOR THE NEW API V3
function __gcalendar_requesttoken($client)
{
    session_alert("<a href='" . $client->createAuthUrl() . "' target='_blank'>" . LANG("requestgcalendartoken", "agenda") . "</a>");
}

function __gcalendar_invalidtoken()
{
    session_error(LANG("invalidgcalendartoken", "agenda"));
}

function __gcalendar_updatetokens($token, $token2)
{
    $query = make_update_query("tbl_gcalendar", array(
        "token" => $token,
        "token2" => $token2
    ), make_where_query(array(
        "id_usuario" => current_user()
    )));
    db_query($query);
}

function __gcalendar_format($datetime)
{
    return date("Y-m-d\TH:i:sP", strtotime($datetime));
}

function __gcalendar_unformat($datetime)
{
    return date("Y-m-d H:i:s", strtotime($datetime));
}

function __gcalendar_insert($client, $title, $content, $where, $dstart, $dstop)
{
    if ($client === null) {
        return false;
    }
    $service = new Google_Service_Calendar($client);
    $event = new Google_Service_Calendar_Event();
    $event->setSummary($title);
    $event->setDescription($content);
    $event->setLocation($where);
    $start = new Google_Service_Calendar_EventDateTime();
    $start->setDateTime(__gcalendar_format($dstart));
    $event->setStart($start);
    $end = new Google_Service_Calendar_EventDateTime();
    $end->setDateTime(__gcalendar_format($dstop));
    $event->setEnd($end);
    $createdEvent = $service->events->insert("primary", $event);
    return $createdEvent->getId();
}

function __gcalendar_update($client, $id, $title, $content, $where, $dstart, $dstop)
{
    if ($client === null) {
        return false;
    }
    $service = new Google_Service_Calendar($client);
    $event = $service->events->get("primary", $id);
    $event->setSummary($title);
    $event->setDescription($content);
    $event->setLocation($where);
    $start = new Google_Service_Calendar_EventDateTime();
    $start->setDateTime(__gcalendar_format($dstart));
    $event->setStart($start);
    $end = new Google_Service_Calendar_EventDateTime();
    $end->setDateTime(__gcalendar_format($dstop));
    $event->setEnd($end);
    $updatedEvent = $service->events->update("primary", $id, $event);
    return true;
}

function __gcalendar_feed($client)
{
    // CHECK FOR A VALID SERVICE
    if ($client === null) {
        return false;
    }
    // CONTINUE
    $service = new Google_Service_Calendar($client);
    $events = $service->events->listEvents("primary");
    $result = array();
    while (true) {
        foreach ($events->getItems() as $event) {
            $temp = array(
                "id" => $event->getId(),
                "title" => $event->getSummary(),
                "content" => $event->getDescription(),
                "where" => $event->getLocation(),
                "dstart" => $event->getStart()->getDateTime(),
                "dstop" => $event->getEnd()->getDateTime()
            );
            foreach ($temp as $key => $val) {
                $temp[$key] = str_replace("\r", "", trim(null2string($val)));
            }
            $temp["dstart"] = __gcalendar_unformat($temp["dstart"]);
            $temp["dstop"] = __gcalendar_unformat($temp["dstop"]);
            $temp["hash"] = md5(serialize(array(
                $temp["title"],
                $temp["content"],
                $temp["where"],
                $temp["dstart"],
                $temp["dstop"]
            )));
            $result[] = $temp;
        }
        $pageToken = $events->getNextPageToken();
        if ($pageToken) {
            $optParams = array("pageToken" => $pageToken);
            $events = $service->events->listEvents("primary", $optParams);
        } else {
            break;
        }
    }
    return $result;
}

function __incidencias_packreport($campos, $tipos, $row)
{
    $body = "";
    $count = count($campos);
    for ($i = 0; $i < $count; $i++) {
        $campo = $campos[$i];
        $tipo = $tipos[$i];
        $label = LANG($campo);
        $value = $row[$campo];
        switch ($tipo) {
            case "text":
                $body .= __report_text($label, $value);
                break;
            case "textarea":
                $body .= __report_textarea($label, $value);
                break;
        }
    }
    return $body;
}

function __incidencias_codigo($id)
{
    return substr(
        str_repeat("0", CONFIG("zero_padding_digits")) . $id,
        -CONFIG("zero_padding_digits"),
        CONFIG("zero_padding_digits")
    );
}

function __phpthumb_imagecreatefromtiff($src)
{
    if (extension_loaded('imagick')) {
        $im2 = new Imagick();
        $im2->readImage($src);
        $im2->setImageFormat('png');
        $im = imagecreatefromstring($im2->getImageBlob());
        $im2->destroy();
    } else {
        $file = get_temp_file(".png");
        ob_passthru("convert ${src} ${file}");
        if (!file_exists($file)) {
            show_php_error(array("phperror" => "ImageMagick failed using convert command line"));
        }
        $im = imagecreatefrompng($file);
        unlink($file);
    }
    return $im;
}

function __signature_getfile($id)
{
    if (!$id) {
        return null;
    }
    $query = "SELECT * FROM tbl_usuarios_c WHERE id='$id'";
    $row = execute_query($query);
    if (!$row) {
        return null;
    }
    if (!$row["email_signature_file"]) {
        return null;
    }
    $id = $row["id"];
    $name = $row["email_signature"];
    $file = $row["email_signature_file"];
    $type = $row["email_signature_type"];
    $size = $row["email_signature_size"];
    $data = file_get_contents(get_directory("dirs/filesdir") . $file);
    $alt = $row["email_name"] . " (" . $row["email_from"] . ")";
    return array(
        "id" => $id,
        "name" => $name,
        "file" => $file,
        "type" => $type,
        "size" => $size,
        "data" => $data,
        "alt" => $alt
    );
}

function __signature_getauto($file)
{
    if (!$file) {
        return null;
    }
    if (!$file["file"]) {
        return null;
    }
    if ($file["type"] == "text/plain") {
        $file["auto"] = trim($file["data"]);
        $file["auto"] = htmlentities($file["auto"], ENT_COMPAT, "UTF-8");
        $file["auto"] = str_replace(
            array(" ","\t","\n"),
            array("&nbsp;",str_repeat("&nbsp;", 8),"<br/>"),
            $file["auto"]
        );
    } elseif ($file["type"] == "text/html") {
        $file["auto"] = trim($file["data"]);
    } elseif (substr($file["type"], 0, 6) == "image/") {
        if (eval_bool(getDefault("cache/useimginline"))) {
            $data = base64_encode($file["data"]);
            $file["src"] = "data:image/png;base64,${data}";
        } else {
            $file["src"] = "?action=signature&id=${file["id"]}";
        }
        $file["auto"] = "<img alt=\"${file["alt"]}\" border=\"0\" src=\"${file["src"]}\" />";
    } else {
        $file["auto"] = "Name: ${file["name"]}<br/>Type: ${file["type"]}<br/>Size: ${file["size"]}";
    }
    require_once "php/getmail.php";
    $file["auto"] = __SIGNATURE_OPEN__ . "--" . __HTML_NEWLINE__ . $file["auto"] . __SIGNATURE_CLOSE__;
    return $file;
}

function __default_eval_querytag($array)
{
    foreach ($array as $key => $val) {
        if (is_array($val)) {
            $array[$key] = __default_eval_querytag($val);
        } elseif ($key == "query") {
            $result = db_query($val);
            $count = 0;
            while ($row = db_fetch_row($result)) {
                $row["__ROW_NUMBER__"] = ++$count;
                set_array($array["rows"], "row", $row);
            }
            db_free($result);
            unset($array[$key]);
        }
    }
    return $array;
}

function __default_process_querytag($query, &$go, &$commit)
{
    $rows = array();
    foreach ($query as $key => $val) {
        if (is_array($val)) {
            set_array($rows, $key, __default_process_querytag($val, $go, $commit));
        } else {
            $val = trim($val);
            if ($commit) {
                $result = db_query($val);
                $count = 0;
                while ($row = db_fetch_row($result)) {
                    $row["__ROW_NUMBER__"] = ++$count;
                    $is_action = 0;
                    if (isset($row["action_error"])) {
                        $error = $row["action_error"];
                        session_error($error);
                        $is_action = 1;
                    }
                    if (isset($row["action_alert"])) {
                        $alert = $row["action_alert"];
                        session_alert($alert);
                        $is_action = 1;
                    }
                    if (isset($row["action_commit"])) {
                        $commit = $row["action_commit"];
                        $is_action = 1;
                    }
                    if (isset($row["action_go"])) {
                        $go = $row["action_go"];
                        $is_action = 1;
                    }
                    if (isset($row["action_include"])) {
                        $include = $row["action_include"];
                        $include = explode(",", $include);
                        foreach ($include as $file) {
                            if (!file_exists($file)) {
                                show_php_error(array("xmlerror" => "Include '$file' not found"));
                            }
                            require $file;
                        }
                        $is_action = 1;
                    }
                    if (!$is_action) {
                        set_array($rows[$key], "row", $row);
                    }
                }
                db_free($result);
            }
        }
    }
    return $rows;
}

function __remove_temp_nodes($array)
{
    if (is_array($array)) {
        foreach ($array as $key => $val) {
            if (limpiar_key($key) != "temp") {
                $array[$key] = __remove_temp_nodes($val);
            } else {
                unset($array[$key]);
            }
        }
    }
    return $array;
}

function __pdfview_output_handler($_RESULT)
{
    // CHECK FOR SHORTCUTS
    if (getParam("download")) {
        output_handler(array(
            "data" => $_RESULT["data"],
            "name" => $_RESULT["title"],
            "type" => "application/pdf",
            "cache" => false
        ));
    }
    if (getParam("print")) {
        output_handler(array(
            "data" => $_RESULT["data"],
            "type" => "application/pdf",
            "cache" => false
        ));
    }
    // PREPARE THE OUTPUT
    $_RESULT["data"] = base64_encode($_RESULT["data"]);
    $buffer = json_encode($_RESULT);
    // CONTINUE
    output_handler(array(
        "data" => $buffer,
        "type" => "application/json",
        "cache" => false
    ));
}

/*
    Name:
        __barcode
    Abstract:
        This function generates a barcode image
    Input:
        - msg: Contents of the barcode
        - w: width of each unit's bar of the barcode
        - h: height of the barcode (without margins and text footer)
        - m: margin of the barcode (white area that surround the barcode)
        - s: size of the footer text, not used if zero
        - t: type of the barcode, C128 is the most common type used
    Output:
        - The png contents of the generated barcode image
        - Otherwise, an empty string if something was wrong
*/
function __barcode($msg, $w, $h, $m, $s, $t)
{
    require_once "lib/tcpdf/tcpdf_barcodes_1d.php";
    $barcode = new TCPDFBarcode($msg, $t);
    $array = $barcode->getBarcodeArray();
    if (!isset($array["maxw"])) {
        return "";
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
        imagettftext($im, $s, 0, (int)$px, (int)$py, $fgcol, $font, $msg);
    }
    // CONTINUE
    ob_start();
    imagepng($im);
    $buffer = ob_get_clean();
    imagedestroy($im);
    return $buffer;
}

/*
    Name:
        __qrcode
    Abstract:
        This function generates a qrcode image
    Input:
        - msg: Contents of the qrcode
        - s: size of each pixel used in the qrcode
        - m: margin of the qrcode (white area that that surround the qrcode)
    Output:
        - The png contents of the generated qrcode image
        - Otherwise, an empty string if something was wrong
*/
function __qrcode($msg, $s, $m)
{
    require_once "lib/tcpdf/tcpdf_barcodes_2d.php";
    $levels = array("L","M","Q","H");
    $factors = array(0.07,0.15,0.25,0.30);
    for ($i = 0; $i < 4; $i++) {
        $barcode = new TCPDF2DBarcode($msg, "QRCODE," . $levels[$i]);
        $array = $barcode->getBarcodeArray();
        if (!isset($array["num_cols"]) || !isset($array["num_rows"])) {
            return "";
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
    ob_start();
    imagepng($im);
    $buffer = ob_get_clean();
    imagedestroy($im);
    return $buffer;
}

function __score_image($score, $width, $height, $size)
{
    $im = imagecreatetruecolor($width, $height);
    $incr = ($score * 512 / 100) / $width;
    $posx = 0;
    for ($i = 0; $i <= 255; $i = $i + $incr) {
        if ($posx > $width) {
            break;
        }
        $color = imagecolorallocate($im, 255, (int)$i, 0);
        imageline($im, $posx, 0, $posx, $height, $color);
        $posx++;
    }
    for ($i = 255; $i >= 0; $i = $i - $incr) {
        if ($posx > $width) {
            break;
        }
        $color = imagecolorallocate($im, (int)$i, 255, 0);
        imageline($im, $posx, 0, $posx, $height, $color);
        $posx++;
    }
    $font = getcwd() . "/lib/fonts/DejaVuSans.ttf";
    $bbox = imagettfbbox($size, 0, $font, $score . "%");
    $sx = $bbox[4] - $bbox[0];
    $sy = $bbox[5] - $bbox[1];
    $color = imagecolorallocate($im, 0, 0, 0);
    imagettftext($im, $size, 0, (int)($width / 2 - $sx / 2), (int)($height / 2 - $sy / 2), $color, $font, $score . "%");
    // CONTINUE
    ob_start();
    imagepng($im);
    $buffer = ob_get_clean();
    imagedestroy($im);
    return $buffer;
}

function __download($id_aplicacion, $id_registro, $cid)
{
    if (!$id_aplicacion) {
        show_php_error(array("phperror" => "Unknown page"));
    }
    if (!$id_registro) {
        show_php_error(array("phperror" => "Unknown content"));
    }
    if (!$cid) {
        show_php_error(array("phperror" => "Unknown file"));
    }
    if ($id_aplicacion == page2id("correo")) {
        if ($id_registro == "session") {
            sess_init();
            $session = $_SESSION["correo"];
            sess_close();
            if (!isset($session["files"][$cid])) {
                show_php_error(array("phperror" => "Session not found"));
            }
            $result = $session["files"][$cid];
            $file = $result["file"];
            $name = $result["name"];
            $type = $result["mime"];
            $size = $result["size"];
        } else {
            require_once "php/getmail.php";
            $decoded = __getmail_getmime($id_registro);
            if (!$decoded) {
                show_php_error(array("phperror" => "Email not found"));
            }
            if (strlen($cid) != 32) {
                $query = "SELECT fichero_hash
                    FROM tbl_ficheros
                    WHERE id='${cid}'
                        AND id_aplicacion='${id_aplicacion}'
                        AND id_registro='${id_registro}'";
                $cid = execute_query($query);
                if (!$cid) {
                    show_php_error(array("phperror" => "Unknown file"));
                }
            }
            $result = __getmail_getcid(__getmail_getnode("0", $decoded), $cid);
            if (!$result) {
                show_php_error(array("phperror" => "Attachment not found"));
            }
            $ext = strtolower(extension($result["cname"]));
            if (!$ext) {
                $ext = strtolower(extension2($result["ctype"]));
            }
            $file = get_cache_file($cid, $ext);
            file_put_contents($file, $result["body"]);
            $name = $result["cname"];
            $type = $result["ctype"];
            $size = $result["csize"];
        }
    } else {
        $query = "SELECT *
            FROM tbl_ficheros
            WHERE id='${cid}'
                AND id_aplicacion='${id_aplicacion}'
                AND id_registro='${id_registro}'";
        $result = execute_query($query);
        if (!$result) {
            show_php_error(array("phperror" => "File not found"));
        }
        $file = get_directory("dirs/filesdir") . $result["fichero_file"];
        if (!file_exists($file)) {
            show_php_error(array("phperror" => "Local file not found"));
        }
        $name = $result["fichero"];
        $type = $result["fichero_type"];
        $size = $result["fichero_size"];
    }
    return array(
        "file" => $file,
        "name" => $name,
        "type" => $type,
        "size" => $size
    );
}

function __vcard($page, $id, $type)
{
    if (!in_array($page, array("contactos","clientes","proveedores","empleados","posiblescli"))) {
        return "";
    }
    if (!in_array($type, array("full","small"))) {
        return "";
    }
    $where = "WHERE id='${id}'";
    $id_aplicacion = page2id($page);
    if ($page == "contactos") {
        $query = "SELECT * FROM tbl_contactos $where";
        $result = db_query($query);
        $row = db_fetch_row($result);
        db_free($result);
        $nombre = $row["nombre"];
        $nombre1 = $row["nombre1"];
        $nombre2 = $row["nombre2"];
        $cargo = $row["cargo"];
        $comentarios = $row["comentarios"];
        // BUSCAR DATOS CLIENTE O PROVEEDOR
        $id_cliente = ($id_aplicacion == page2id("clientes")) ? $row["id_cliente"] : 0;
        $id_proveedor = ($id_aplicacion == page2id("proveedores")) ? $row["id_proveedor"] : 0;
        $id_empleado = ($id_aplicacion == page2id("empleados")) ? $row["id_empleado"] : 0;
        if ($id_cliente) {
            $query = "SELECT * FROM tbl_clientes WHERE id='$id_cliente'";
        }
        if ($id_proveedor) {
            $query = "SELECT * FROM tbl_proveedores WHERE id='$id_proveedor'";
        }
        if ($id_empleado) {
            $query = "SELECT * FROM tbl_empleados WHERE id='$id_empleado'";
        }
        $result = db_query($query);
        $row2 = db_fetch_row($result);
        db_free($result);
        $organizacion = $row2["nombre"];
        // CONTINUAR
        $direccion = $row["direccion"];
        $pais = $row["nombre_pais"];
        $provincia = $row["nombre_provincia"];
        $poblacion = $row["nombre_poblacion"];
        $codpostal = $row["nombre_codpostal"];
        $tel_fijo = $row["tel_fijo"];
        $tel_casa = "";
        $tel_movil = $row["tel_movil"];
        $fax = $row["fax"];
        $web = $row["web"];
        $email = $row["email"];
        $email2 = "";
    }
    if ($page == "clientes" || $page == "proveedores" || $page == "empleados") {
        if ($page == "clientes") {
            $query = "SELECT * FROM tbl_clientes $where";
        }
        if ($page == "proveedores") {
            $query = "SELECT * FROM tbl_proveedores $where";
        }
        if ($page == "empleados") {
            $query = "SELECT * FROM tbl_empleados $where";
        }
        $result = db_query($query);
        $row = db_fetch_row($result);
        db_free($result);
        $nombre = $row["nombre"];
        $nombre1 = "";
        $nombre2 = "";
        $cargo = "";
        $comentarios = $row["comentarios"];
        $organizacion = $row["nombre"];
        $direccion = $row["direccion"];
        $pais = $row["nombre_pais"];
        $provincia = $row["nombre_provincia"];
        $poblacion = $row["nombre_poblacion"];
        $codpostal = $row["nombre_codpostal"];
        $tel_fijo = $row["tel_fijo"];
        $tel_casa = "";
        $tel_movil = $row["tel_movil"];
        $fax = $row["fax"];
        $web = $row["web"];
        $email = $row["email"];
        $email2 = "";
    }
    if ($page == "posiblescli") {
        $query = "SELECT * FROM tbl_posiblescli $where";
        $result = db_query($query);
        $row = db_fetch_row($result);
        db_free($result);
        $nombre = $row["contacto"];
        $nombre1 = "";
        $nombre2 = "";
        $cargo = $row["cargo"];
        $comentarios = $row["comentarios"];
        $organizacion = $row["nombre"];
        $direccion = $row["direccion"];
        $pais = $row["nombre_pais"];
        $provincia = $row["nombre_provincia"];
        $poblacion = $row["nombre_poblacion"];
        $codpostal = $row["nombre_codpostal"];
        $tel_fijo = $row["tel_fijo"];
        $tel_casa = "";
        $tel_movil = $row["tel_movil"];
        $fax = $row["fax"];
        $web = $row["web"];
        $email = $row["email"];
        $email2 = "";
    }
    // CLEAR SOME PARAMETERS
    $badchars = array(" ",".",",",";",":","_","-");
    $tel_fijo = str_replace($badchars, "", $tel_fijo);
    $tel_casa = str_replace($badchars, "", $tel_casa);
    $tel_movil = str_replace($badchars, "", $tel_movil);
    $fax = str_replace($badchars, "", $fax);
    // VCARD
    $revision = date("YmdHis", time());
    $name = encode_bad_chars($nombre) . ".vcf";
    $data = array();
    $data[] = "BEGIN:VCARD";
    $data[] = "VERSION:2.1";
    if ($type == "full") {
        $data[] = "N:$nombre2;$nombre1";
        $data[] = "FN:$nombre";
        $data[] = "ORG:$organizacion;$comentarios";
        $data[] = "TITLE:$cargo";
        $data[] = "TEL;WORK;VOICE:$tel_fijo";
        $data[] = "TEL;HOME;VOICE:$tel_casa";
        $data[] = "TEL;CELL;VOICE:$tel_movil";
        $data[] = "TEL;WORK;FAX:$fax";
        $data[] = "TEL;HOME;FAX:";
        $data[] = "ADR;WORK;ENCODING=QUOTED-PRINTABLE:;;$direccion;$poblacion;$provincia;$codpostal;$pais";
        $data[] = "LABEL;WORK;ENCODING=QUOTED-PRINTABLE:=0D=0A$direccion=0D=0A$poblacion, $provincia $codpostal=0D=0A$pais";
        $data[] = "ADR;HOME;ENCODING=QUOTED-PRINTABLE:;;;;;;";
        $data[] = "LABEL;HOME;ENCODING=QUOTED-PRINTABLE:;=0D=0A,=0D=0A";
        $data[] = "URL;WORK:$web";
        $data[] = "EMAIL;PREF;INTERNET:$email";
        $data[] = "EMAIL;INTERNET:$email2";
        $data[] = "REV:$revision";
    }
    if ($type == "small") {
        if ($nombre) {
            $data[] = "FN:$nombre";
        }
        if ($direccion) {
            $data[] = "ADR;WORK;ENCODING=QUOTED-PRINTABLE:;;$direccion;$poblacion;$provincia;$codpostal;$pais";
        }
        if ($tel_fijo) {
            $data[] = "TEL;WORK;VOICE:$tel_fijo";
        }
        if ($tel_movil) {
            $data[] = "TEL;CELL;VOICE:$tel_movil";
        }
    }
    $data[] = "END:VCARD";
    $data = implode("\r\n", $data) . "\r\n";
    return array(
        "name" => $name,
        "data" => $data
    );
}
