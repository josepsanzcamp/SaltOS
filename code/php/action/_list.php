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

require_once("php/libaction.php");
$_LANG["default"]="${page},menu,common";
$_CONFIG[$page]=xml2array("xml/${page}.xml");
$page=lastpage($page);
history($page);

require_once("php/listsim.php");
$config=getDefault("$page/$action");
$config=eval_attr($config);
$_RESULT[$action]=$config;
// GET AND REMOVE THE NEEDED XML NODES
foreach(array("query","order","limit","offset") as $node) {
	if(!isset($config[$node])) show_php_error(array("xmlerror"=>"&lt;$node&gt; not found for &lt;$action&gt;"));
	unset($_RESULT[$action][$node]);
}
$query0=$config["query"];
$limit=$config["limit"];
$offset=$config["offset"];
// CHECK ORDER
list($order,$array)=list_check_order($config["order"],$config["fields"]);
// MARK THE SELECTED ORDER FIELD
foreach($_RESULT[$action]["fields"] as $key=>$val) {
	$selected=0;
	if(isset($val["name"]) && $val["name"]==$array[0][0]) $selected=1;
	if(isset($val["order"]) && $val["order"]==$array[0][0]) $selected=1;
	if(isset($val["order".$array[0][1]]) && $val["order".$array[0][1]]==$array[0][0]) $selected=1;
	if($selected) $_RESULT[$action]["fields"][$key]["selected"]=$array[0][1];
}
// EXECUTE THE QUERY TO GET THE ROWS WITH LIMIT AND OFFSET
$query="$query0 ORDER BY $order LIMIT $offset,$limit";
$result=db_query($query);
$count=0;
while($row=db_fetch_row($result)) {
	$row["__ROW_NUMBER__"]=++$count;
	set_array($_RESULT[$action]["rows"],"row",$row);
}
db_free($result);
// CONTINUE WITH NORMAL OPERATION
$_RESULT[$action]=__default_eval_querytag($_RESULT[$action]);
$_RESULT[$action]=__remove_temp_nodes($_RESULT[$action]);

$json=json_encode($_RESULT);
output_handler(array(
	"data"=>$json,
	"type"=>"application/json",
	"cache"=>false
));

?>