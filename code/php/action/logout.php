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

if(getParam("action")=="logout") {
	check_security("logout");
	sess_init();
	setSession("user","");
	setSession("pass","");
	sess_close();
	if(eval_bool(getDefault("security/allowremember"))) {
		$remember=getCookie2("remember");
		if($remember) {
			setCookie2("user","");
			setCookie2("pass","");
			setCookie2("remember",$remember);
		} else {
			setCookie2("user","");
			setCookie2("pass","");
			setCookie2("remember","");
		}
	}
	$querystring=getParam("querystring");
	$querystring=html_entity_decode($querystring,ENT_COMPAT,"UTF-8");
	$querystring=$querystring?"?${querystring}":"";
	javascript_location_base($querystring);
	die();
}

?>