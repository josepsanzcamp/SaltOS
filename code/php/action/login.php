<?php
declare(ticks=1000);
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2015 by Josep Sanz Campderrós
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
if(getParam("action")=="login") {
	$user=getParam("user");
	$pass=getParam("pass");
	$captcha=getParam("captcha");
	$remember=getParam("remember");
	$lang=getParam("lang");
	$style=getParam("style");
	$iconset=getParam("iconset");
	if($user!="" || $pass!="") {
		$check1=check_security("retries");
		$check2=check_captcha($captcha);
		if(!$check1 && !$check2) {
			setParam("action","logout");
			include("php/action/logout.php");
		}
		$pass=remake_password($user,$pass);
		sess_init();
		useSession("user",$user);
		useSession("pass",$pass);
		sess_close();
		pre_datauser();
		check_security("login");
		if(eval_bool(getDefault("security/allowremember"))) {
			if($remember) {
				useCookie("user",$user);
				useCookie("pass",$pass);
				useCookie("remember",$remember);
			} else {
				useCookie("user","null");
				useCookie("pass","null");
				useCookie("remember","null");
			}
		}
		if(!$check1 && $check2) {
			check_security("captcha");
		}
	}
	useCookie("lang",$lang);
	useCookie("style",$style);
	useCookie("iconset",$iconset);
	$querystring=getParam("querystring");
	$querystring=html_entity_decode($querystring,ENT_COMPAT,"UTF-8");
	$querystring=$querystring?"?${querystring}":"";
	javascript_location_base($querystring);
	die();
}
?>