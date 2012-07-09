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
if(getParam("page")=="support") {
	include_once("php/report.php");
	include_once("php/sendmail.php");
	// DATOS SMPT
	$host=CONFIG("email_host");
	$port=CONFIG("email_port");
	$extra=CONFIG("email_extra");
	if($port || $extra) $host.=":$port:$extra";
	$user=CONFIG("email_user");
	$pass=CONFIG("email_pass");
	if(!$host || !$user || !$pass) {
		session_error(LANG("msgnotsmtpemail"));
		javascript_history(-1);
		die();
	}
	// DATOS EMAIL
	if(!CONFIG("email_support")) {
		session_error(LANG("msgnotemailsupport",$page));
		javascript_history(-1);
		die();
	}
	$from=CONFIG("email_name")." <".CONFIG("email_from").">";
	$to=LANG("contact",$page)." <".CONFIG("email_support").">";
	$contact=LANG("contact",$page);
	$nombre=getParam("default_0_nombre");
	$subject=getParam("default_0_subject");
	$comentarios=getParam("default_0_comentarios");
	if(!$subject) {
		session_error(LANG("msgnotsubject",$page));
	} elseif(!$comentarios) {
		session_error(LANG("msgnotcomments",$page));
	} else {
		$body=__report_begin($contact);
		$files=array();
		$body.=__report_text(LANG("nombre"),$nombre);
		$body.=__report_text(LANG("subject",$page),$subject);
		$body.=__report_textarea(LANG("comentarios"),$comentarios,false);
		$body.=__report_end();
		// PARA DEBUGAR
		//echo $body;die();
		// ENVIAR EMAIL
		$send=sendmail($from,$to,$contact.": ".$subject,$body,$files,$host,$user,$pass);
		if($send!="") {
			session_error($send);
		} else {
			session_alert(LANG("msgsendemail",$page));
		}
	}
	javascript_history(-1);
	die();
}
?>