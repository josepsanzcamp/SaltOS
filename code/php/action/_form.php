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

if(!check_user()) action_denied();

$page=getParam("page");
$id=intval(getParam("id"));

require_once("php/libaction.php");
$_LANG["default"]="${page},menu,common";
$_CONFIG[$page]=xml2array("xml/${page}.xml");
$page=lastpage($page);
history($page);

$config=getDefault("$page/form");
$config=eval_attr($config);
$_RESULT=$config;
unset($_RESULT["views"]);
if($id==0) {
	if(isset($config["views"]["insert"]["title"])) $_RESULT["title"]=$config["views"]["insert"]["title"];
	if(isset($config["views"]["insert"]["query"])) $query=$config["views"]["insert"]["query"];
} else {
	if($id>0) {
		if(isset($config["views"]["update"]["title"])) $_RESULT["title"]=$config["views"]["update"]["title"];
		if(isset($config["views"]["update"]["query"])) $query=$config["views"]["update"]["query"];
	} else {
		if(isset($config["views"]["view"]["title"])) $_RESULT["title"]=$config["views"]["view"]["title"];
		if(isset($config["views"]["view"]["query"])) $query=$config["views"]["view"]["query"];
	}
}
$fixquery=is_array($query)?0:1;
$go=0;
$commit=1;
if($fixquery) $query=array("default"=>$query);
$rows=__default_process_querytag($query,$go,$commit);
if($fixquery) $rows=$rows["default"];
set_array($_RESULT,"rows",$rows);
if($go) {
	if(is_numeric($go)) {
		//~ javascript_history($go);
	} else {
		//~ javascript_history("update");
		//~ javascript_location_page($go);
	}
	die();
}
$_RESULT=__default_eval_querytag($_RESULT);
$_RESULT=__remove_temp_nodes($_RESULT);

$json=json_encode($_RESULT);
output_handler(array(
	"data"=>$json,
	"type"=>"application/json",
	"cache"=>false
));

?>