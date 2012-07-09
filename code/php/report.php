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
function __report_begin($subject) {
	$message="<html>\n";
	$message.="<head>\n";
	$message.="<title>$subject</title>\n";
	$message.="</head>\n";
	$message.="<body bgcolor='".__report_color("bgbody")."'>\n";
	$message.="<table cellspacing='2px' cellpadding='2px' border='0px' width='600px'>\n";
	$message.=__report_head($subject);
	return $message;
}

function __report_text($label,$value) {
	if($label!="") $label.=":";
	$message="<tr>\n";
	$message.="<td bgcolor='".__report_color("bgreport")."' valign='top' align='right' width='200px'>";
	$message.="<font size='1' face='Verdana,Arial,Helvetica,sans-serif' color='".__report_color("fgreport")."'><b>";
	$message.=$label;
	$message.="</b></font>";
	$message.="</td>\n";
	$message.="<td bgcolor='".__report_color("bgreport")."' valign='top' align='left' width='400px'>";
	$message.="<font size='1' face='Verdana,Arial,Helvetica,sans-serif' color='".__report_color("fgreport")."'><b>";
	$message.=$value;
	$message.="</b></font>";
	$message.="</td>\n";
	$message.="</tr>\n";
	return $message;
}

function __report_textarea($label,$value,$repare=true) {
	if($label!="") $label.=":";
	$message="";
	if($label!="") {
		$message.="<tr>\n";
		$message.="<td bgcolor='".__report_color("bgreport")."' valign='top' align='center' width='600px' colspan='2'>";
		$message.="<font size='1' face='Verdana,Arial,Helvetica,sans-serif' color='".__report_color("fgreport")."'><b>";
		$message.=$label;
		$message.="</b></font>";
		$message.="</td>\n";
		$message.="</tr>\n";
	}
	if($repare) $value=str_replace("\n","<br />\n",$value);
	$message.="<tr>\n";
	$message.="<td bgcolor='".__report_color("bgreport")."' valign='top' align='left' width='600px' colspan='2' height='100px'>";
	$message.="<font size='1' face='Verdana,Arial,Helvetica,sans-serif' color='".__report_color("fgreport")."'><b>";
	$message.=$value;
	$message.="</b></font>";
	$message.="</td>\n";
	$message.="</tr>\n";
	return $message;
}

function __report_mail($label,$value) {
	if($label!="") $label.=":";
	$message="<tr>\n";
	$message.="<td bgcolor='".__report_color("bgreport")."' valign='top' align='right' width='200px'>";
	$message.="<font size='1' face='Verdana,Arial,Helvetica,sans-serif' color='".__report_color("fgreport")."'><b>";
	$message.=$label;
	$message.="</b></font>";
	$message.="</td>\n";
	$message.="<td bgcolor='".__report_color("bgreport")."' valign='top' align='left' width='400px'>";
	$message.="<font size='1' face='Verdana,Arial,Helvetica,sans-serif' color='".__report_color("fgreport")."'><b>";
	$message.="<a style='color:".__report_color("fgreport")."' href='mailto: $value'>$value</a>";
	$message.="</b></font>";
	$message.="</td>";
	$message.="</tr>\n";
	return $message;
}

function __report_link($label,$value,$text) {
	if($label!="") $label.=":";
	$message="<tr>\n";
	$message.="<td bgcolor='".__report_color("bgreport")."' valign='top' align='right' width='200px'>";
	$message.="<font size='1' face='Verdana,Arial,Helvetica,sans-serif' color='".__report_color("fgreport")."'><b>";
	$message.=$label;
	$message.="</b></font>";
	$message.="</td>\n";
	if($text=="") $text=$value;
	$message.="<td bgcolor='".__report_color("bgreport")."' valign='top' align='left' width='400px'>";
	$message.="<font size='1' face='Verdana,Arial,Helvetica,sans-serif' color='".__report_color("fgreport")."'><b>";
	$message.="<a style='color:".__report_color("fgreport")."' href='$value'>$text</a>";
	$message.="</b></font>";
	$message.="</td>";
	$message.="</tr>\n";
	return $message;
}

function __report_end() {
	$message="</table>\n";
	$message.="</body>\n";
	$message.="</html>\n";
	return $message;
}

function __report_head($subject) {
	$message="<tr>\n";
	$message.="<td bgcolor='".__report_color("bgheader")."' valign='top' align='center' width='600px' colspan='2' >";
	$message.="<font size='1' face='Verdana,Arial,Helvetica,sans-serif' color='".__report_color("fgheader")."'><b>";
	$message.=$subject;
	$message.="</b></font>";
	$message.="</td>\n";
	$message.="</tr>\n";
	return $message;
}

function __report_separator() {
	$message="<tr>";
	$message.="<td width='600px' colspan='2'>&nbsp;</td>";
	$message.="</tr>";
	return $message;
}

function __report_color($arg) {
	static $colors=array(
		"bgbody"=>"#FFFFFF",
		"bgheader"=>"#336699",
		"fgheader"=>"#FFFFFF",
		"bgreport"=>"#EEEFFF",
		"fgreport"=>"#666666");
	if(is_array($arg)) {
		foreach($arg as $key=>$val) $colors[$key]=$val;
		return;
	}
	if(!isset($colors[$arg])) return "";
	return $colors[$arg];
}
?>