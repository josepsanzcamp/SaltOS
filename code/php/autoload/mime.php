<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2024 by Josep Sanz Campderrós
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

function saltos_content_type($file)
{
    static $mimes = array(
        "css" => "text/css",
        "js" => "text/javascript",
        "xml" => "text/xml",
        "htm" => "text/html",
        "html" => "text/html",
        "png" => "image/png",
        "bmp" => "image/bmp",
        "json" => "application/json"
    );
    $ext = strtolower(extension($file));
    if (isset($mimes[$ext])) {
        return $mimes[$ext];
    }
    if (function_exists("mime_content_type")) {
        return mime_content_type($file);
    }
    if (function_exists("finfo_file")) {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file);
    }
    return "application/octet-stream";
}

function saltos_content_type0($mime)
{
    $mime = explode("/", $mime);
    return array_shift($mime);
}

function saltos_content_type1($mime)
{
    $mime = explode("/", $mime);
    return array_pop($mime);
}
