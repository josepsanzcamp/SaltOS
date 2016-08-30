<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2016 by Josep Sanz Campderrós
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
if(!check_user()) action_denied();
if(getParam("action")=="pdf") {
	require_once("php/libaction.php");
	$_LANG["default"]="$page,menu,common";
	if(!file_exists("xml/${page}.xml")) action_denied();
	$config=xml2array("xml/${page}.xml");
	if(!isset($config[$action])) action_denied();
	$config=$config[$action];
	if(eval_bool(getDefault("debug/actiondebug"))) debug_dump(false);
	$config=eval_attr($config);
	if(eval_bool(getDefault("debug/actiondebug"))) debug_dump();
	__pdf_eval_pdftag($config);
	if(!defined("__CANCEL_DIE__")) die();
}
?>