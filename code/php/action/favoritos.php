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

if (!check_user()) {
    action_denied();
}

if (getParam("id") != "") {
    if (!check_commands(array(getDefault("commands/preview"),getDefault("commands/xserver")), 60)) {
        output_handler(array(
            "file" => "img/none.png",
            "cache" => true
        ));
        die();
    }
    $query = "SELECT url FROM tbl_favoritos WHERE id='" . intval(getParam("id")) . "'";
    $url = execute_query($query);
    if (!$url) {
        output_handler(array(
            "file" => "img/none.png",
            "cache" => true
        ));
        die();
    }
    $format = "jpg";
    $width = 1366;
    $height = 768;
    $colors = 16;
    $useragent = getServer("HTTP_USER_AGENT");
    $width2 = 350;
    $height2 = 200;
    $cache = get_cache_file(array($url,$format,$width,$height,$colors,$useragent,$width2,$height2), $format);
    if (!file_exists($cache)) {
        $query = "SELECT preview FROM tbl_favoritos WHERE id='" . intval(getParam("id")) . "'";
        $preview = execute_query($query);
        if ($preview != "") {
            file_put_contents($cache, base64_decode($preview));
            chmod($cache, 0666);
        }
    }
    if (!file_exists($cache)) {
        if (!semaphore_acquire(getParam("action"))) {
            die();
        }
        if (!file_exists($cache)) {
            // GET THE IMAGE WITH DIFFERENT DELAYS
            foreach (array(1000,5000) as $delay) {
                $preview = str_replace(
                    array("__FORMAT__","__WIDTH__","__HEIGHT__","__DELAY__","__USER_AGENT__","__INPUT__","__OUTPUT__"),
                    array($format,$width,$height,$delay,$useragent,$url,$cache),
                    getDefault("commands/__preview__")
                );
                $xserver = str_replace(
                    array("__WIDTH__","__HEIGHT__","__COLORS__","__COMMAND__"),
                    array($width,$height,$colors,$preview),
                    getDefault("commands/__xserver__")
                );
                ob_passthru($xserver);
                semaphore_release(getParam("action"));
                if (!file_exists($cache)) {
                    output_handler(array(
                        "file" => "img/none.png",
                        "cache" => true
                    ));
                    die();
                }
                // COMPUTE THE HISTOGRAM
                // http://bubble.ro/How_to_create_the_histogram_of_an_image_using_PHP.html
                $im = imagecreatefromjpeg($cache);
                $imgw = imagesx($im);
                $imgh = imagesy($im);
                $n = $imgw * $imgh;
                $histo = array();
                for ($i = 0; $i < $imgw; $i++) {
                    for ($j = 0; $j < $imgh; $j++) {
                        $rgb = ImageColorAt($im, $i, $j);
                        $r = ($rgb >> 16) & 0xFF;
                        $g = ($rgb >> 8) & 0xFF;
                        $b = $rgb & 0xFF;
                        $v = round(($r + $g + $b) / 3);
                        if (!isset($histo[$v])) {
                            $histo[$v] = 0;
                        }
                        $histo[$v] += $v / $n;
                    }
                }
                imagedestroy($im);
                // OK IF HISTOGRAM CONTAINS MORE THAN 1 COLOR
                if (count($histo) > 1) {
                    break;
                }
            }
            // RESIZE
            $im1 = imagecreatefromjpeg($cache);
            $im2 = imagecreatetruecolor($width2, $height2);
            imagecopyresampled($im2, $im1, 0, 0, 0, 0, $width2, $height2, imagesx($im1), imagesy($im1));
            imagejpeg($im2, $cache);
            imagedestroy($im1);
            imagedestroy($im2);
            chmod($cache, 0666);
            // DATABASE
            $query = make_update_query("tbl_favoritos", array(
                "preview" => base64_encode(file_get_contents($cache))
            ), make_where_query(array(
                "id" => intval(getParam("id"))
            )));
            db_query($query);
        }
    }
    output_handler(array(
        "file" => $cache,
        "cache" => true
    ));
    die();
}

require_once "php/libaction.php";
$url = getParam("url");
$scheme = parse_url($url, PHP_URL_SCHEME);
if (!$scheme) {
    $url = "http://" . $url;
}
//~ if(substr($url,-1,1)=="/") $url=substr($url,0,-1);
$query = "SELECT id FROM tbl_favoritos WHERE " . make_where_query(array("url" => $url));
if (!execute_query($query)) {
    capture_next_error();
    $html = url_get_contents($url);
    $error = get_clear_error();
    if (!$error && $html != "") {
        // NOMBRE EN TAG TITLE
        $nombre = $url;
        $pos1 = stripos($html, "<title>");
        if ($pos1 !== false) {
            $pos1 = strpos($html, ">", $pos1);
        }
        $pos2 = stripos($html, "</title>");
        if ($pos1 !== false && $pos2 !== false) {
            $nombre = substr($html, $pos1 + 1, $pos2 - $pos1 - 1);
        }
        // NOMBRE Y DESCRIPCION EN TAGS META
        $descripcion = $url;
        $metas = __favoritos_get_metas($html);
        foreach ($metas as $meta) {
            if (isset($meta["name"]) && $meta["name"] == "description" && isset($meta["content"])) {
                $descripcion = $meta["content"];
            }
            if (isset($meta["property"]) && $meta["property"] == "og:description" && isset($meta["content"])) {
                $descripcion = $meta["content"];
            }
            if (isset($meta["property"]) && $meta["property"] == "og:title" && isset($meta["content"])) {
                $nombre = $meta["content"];
            }
        }
        // INSERT EN TBL_FAVORITOS
        $query = make_insert_query("tbl_favoritos", array(
            "url" => $url,
            "nombre" => html_entity_decode(getutf8($nombre), ENT_COMPAT, "UTF-8"),
            "descripcion" => html_entity_decode(getutf8($descripcion), ENT_COMPAT, "UTF-8")
        ));
        db_query($query);
        // CONTINUE
        make_control(page2id("favoritos"));
        make_indexing(page2id("favoritos"));
        javascript_alert(LANG("bookmarkadded", "favoritos"));
        if (getParam("refresh")) {
            javascript_history(0);
        }
    } else {
        javascript_alert(LANG("invalidurl", "favoritos") . getParam("url"));
    }
} else {
    javascript_alert(LANG("bookmarkexists", "favoritos"));
}
die();
