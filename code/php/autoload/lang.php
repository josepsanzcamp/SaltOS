<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2019 by Josep Sanz Campderrós
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

function LANG_LOADED() {
	global $_LANG;
	return isset($_LANG);
}

function LANG($key,$arg="") {
	global $_LANG;
	if(!LANG_LOADED()) return "$key not load";
	if($arg) $arg="$arg,";
	$default=explode(",",$arg.$_LANG["default"]);
	foreach($default as $d) {
		if(isset($_LANG[$d][$key])) {
			return eval_bool(getDefault("debug/langdebug"))?"LANG(".$_LANG[$d][$key].")":$_LANG[$d][$key];
		}
	}
	return "$key (not found)";
}

function LANG_ESCAPE($key,$arg="") {
	return addslashes(LANG($key,$arg));
}

function LANG_ENCODE($key,$arg="") {
	return encode_bad_chars(LANG($key,$arg));
}

function load_lang($lang) {
	return file_exists("xml/lang/${lang}.xml");
}

?>