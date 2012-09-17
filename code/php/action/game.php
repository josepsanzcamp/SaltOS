<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2012 by Josep Sanz Campderrós
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
if(!check_user()) action_denied();
if(getParam("action")=="game") {
	include("php/defines.php");
	ob_start();
	echo __PAGE_HTML_OPEN__;
	echo "<canvas></canvas>";
	echo "<style type='text/css'>canvas{margin-top:45px;}div{text-align:right;}</style>";
	echo "<script type='text/javascript'>".file_get_contents("lib/js1k/bouncingbeholder.js")."</script>";
	echo "<script type='text/javascript'>".file_get_contents("lib/timbre/timbre.min.js")."</script>";
	echo "<script type='text/javascript'>".minify_js(file_get_contents("lib/timbre/004.js"))."</script>";
	echo "<script type='text/javascript'>ex0.play();</script>";
	echo __TEXT_HTML_OPEN__;
	echo "<div>Game: <b>Legend of The Bouncing Beholder</b> | Copyright (c) 2010 Marijn Haverbeke (ZLib/LibPNG license) | <a href='javascript:void(0)' onclick='parent.openwin(\"http://marijn.haverbeke.nl/js1k.html\")'>marijn.haverbeke.nl/js1k.html</a></div>";
	echo "<div>Music: <b>JavaScript Library for Objective Sound Programming</b> | Timbre Synthesizer Example (MIT license) | <a href='javascript:void(0)' onclick='parent.openwin(\"http://mohayonao.github.com/timbre/\")'>mohayonao.github.com/timbre/</a></div>";
	echo __TEXT_HTML_CLOSE__;
	echo __PAGE_HTML_CLOSE__;
	$buffer=ob_get_clean();
	$hash=md5($buffer);
	header_etag($hash);
	ob_start_protected(getDefault("obhandler"));
	header_powered();
	header_expires($hash);
	header("Content-Type: text/html");
	header("x-frame-options: SAMEORIGIN");
	echo $buffer;
	ob_end_flush();
	die();
}
?>