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
if(getParam("action")=="ajax") {
	$config=xml2array("xml/ajax.xml");
	if(eval_bool(getDefault("debug/actiondebug"))) debug_dump(false);
	$query=getParam("query","query");
	if(!isset($config[$query])) die();
	$config=eval_attr(array($query=>$config[$query]));
	if(eval_bool(getDefault("debug/actiondebug"))) debug_dump();
	$query=$config[$query];
	$result=db_query($query);
	$count=1;
	$_RESULT=array("rows"=>array());
	while($row=db_fetch_row($result)) {
		$row["__ROW_NUMBER__"]=$count++;
		set_array($_RESULT["rows"],"row",$row);
	}
	db_free($result);
	$format=strtolower(getParam("format","xml"));
	if($format=="xml") {
		$buffer="<?xml version='1.0' encoding='UTF-8' ?>\n";
		$buffer.=array2xml($_RESULT);
		$format="text/xml";
	} elseif($format=="plain") {
		$buffer="";
		foreach($_RESULT["rows"] as $row) $buffer.=implode("|",$row)."\n";
		$format="text/plain";
	} elseif($format=="json") {
		$buffer=array();
		foreach($_RESULT["rows"] as $row) $buffer[]=json_encode($row);
		$buffer="[".implode(",",$buffer)."]";
		$format="application/json";
	} else {
		die();
	}
	output_buffer($buffer,$format);
}
?>