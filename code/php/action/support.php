<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2013 by Josep Sanz Campderrós
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
if(getParam("action")=="support") {
	include_once("php/report.php");
	include_once("php/sendmail.php");
	// DATOS SMPT
	if(!CONFIG("email_host") || !CONFIG("email_user") || !CONFIG("email_pass")) {
		session_error(LANG("msgnotsmtpemail","support"));
		javascript_history(-1);
		die();
	}
	// DATOS EMAIL
	if(!CONFIG("email_support")) {
		session_error(LANG("msgnotemailsupport","support"));
		javascript_history(-1);
		die();
	}
	$to=LANG("contact","support")." <".CONFIG("email_support").">";
	$contact=LANG("contact","support");
	$nombre=getParam("default_0_nombre");
	$subject=getParam("default_0_subject");
	$comentarios=getParam("default_0_comentarios");
	if(!$subject) {
		session_error(LANG("msgnotsubject","support"));
	} elseif(!$comentarios) {
		session_error(LANG("msgnotcomments","support"));
	} else {
		$body=__report_begin($contact);
		$body.=__report_text(LANG("nombre","support"),$nombre);
		$body.=__report_text(LANG("subject","support"),$subject);
		$body.=__report_textarea(LANG("comentarios","support"),$comentarios,false);
		$body.=__report_end();
		// PARA DEBUGAR
		//~ echo $body;die();
		// ENVIAR EMAIL
		$send=sendmail(0,$to,$contact.": ".$subject,$body);
		if($send!="") {
			session_error($send);
		} else {
			session_alert(LANG("msgsendemail","support"));
		}
	}
	javascript_history(-1);
	die();
}
?>