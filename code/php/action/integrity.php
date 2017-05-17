<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2017 by Josep Sanz Campderrós
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
if(getParam("action")=="integrity") {
	if(!eval_bool(getDefault("enableintegrity"))) die();
	// CHECK THE SEMAPHORE
	if(!semaphore_acquire(getParam("action"),getDefault("semaphoretimeout",100000))) die();
	// FIXING INTEGRITY PROBLEMS
	$query=make_select_query("tbl_aplicaciones",array(
		"id",
		"tabla"
	),make_where_query(array(
		"tabla"=>array("!=","")
	)));
	$result=db_query($query);
	$total=0;
	while($row=db_fetch_row($result)) {
		if(time_get_usage()>getDefault("server/percentstop")) break;
		$id_aplicacion=$row["id"];
		$tabla=$row["tabla"];
		for(;;) {
			if(time_get_usage()>getDefault("server/percentstop")) break;
			// SEARCH IDS OF THE MAIN APPLICATION TABLE, THAT DO NOT EXIST ON THE REGISTER TABLE
			$query=make_select_query($tabla,"id",make_where_query(array(),"AND",array(
				"id NOT IN (".make_select_query("tbl_registros_i","id_registro",make_where_query(array(
					"id_aplicacion"=>$id_aplicacion
				))).")"
			)),array(
				"limit"=>1000
			));
			$ids=execute_query_array($query);
			if(!count($ids)) break;
			make_control($id_aplicacion,$ids);
			$total+=count($ids);
		}
		for(;;) {
			if(time_get_usage()>getDefault("server/percentstop")) break;
			// SEARCH IDS OF THE REGISTER TABLE, THAT DO NOT EXIST ON THE MAIN APPLICATION TABLE
			$query=make_select_query("tbl_registros_i","id_registro",make_where_query(array(
				"id_aplicacion"=>$id_aplicacion
			),"AND",array(
				"id_registro NOT IN (".make_select_query($tabla,"id").")"
			)),array(
				"limit"=>1000
			));
			$ids=execute_query_array($query);
			if(!count($ids)) break;
			make_control($id_aplicacion,$ids);
			$total+=count($ids);
		}
	}
	db_free($result);
	// CHECK INTEGRITY
	for(;;) {
		if(time_get_usage()>getDefault("server/percentstop")) break;
		// SEARCH FOR DUPLICATED ROWS IN REGISTER TABLE
		$query=make_select_query("tbl_registros_i",array(
			"GROUP_CONCAT(id)"=>"ids",
			"id_aplicacion"=>"id_aplicacion",
			"id_registro"=>"id_registro",
			"COUNT(*)"=>"total"
		),"",array(
			"groupby"=>array("id_aplicacion","id_registro"),
			"having"=>"total>1",
			"limit"=>"1000"
		));
		$ids=execute_query_array($query);
		if(!count($ids)) break;
		foreach($ids as $id) {
			$temp=explode(",",$id["ids"]);
			array_shift($temp);
			$temp=implode(",",$temp);
			$query=make_delete_query("tbl_registros_i","id IN (${temp})");
			db_query($query);
		}
		$total+=count($ids);
	}
	// SEND RESPONSE
	if($total) javascript_alert($total.LANG("msgregistersindexed".min($total,2)));
	// RELEASE SEMAPHORE
	semaphore_release(getParam("action"));
	javascript_headers();
	die();
}
?>