<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2014 by Josep Sanz Campderrós
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
if(getParam("action")=="gcalendar") {
	include("php/gcalendar.php");
	// GET GOOGLE CALENDAR USER ACCOUNT
	$query="SELECT login,password FROM tbl_gcalendar WHERE id_usuario='".current_user()."'";
	$result=execute_query($query);
	$login=$result["login"];
	$password=$result["password"];
	if($login=="" || $password=="") {
		session_error(LANG("notgcalendarpassword",$page));
		javascript_history(-1);
		die();
	}
	// GET A VALID SERVICE
	capture_next_error();
	$service=__gcalendar_connect($login,$password);
	$error=get_clear_error();
	if($error!="") {
		session_error(LANG("gcalendarerror",$page));
		javascript_history(-1);
		die();
	}
	if($service===null) {
		session_error(LANG("invalidgcalendarpassword",$page));
		javascript_history(-1);
		die();
	}
	// GET DATAS FROM GOOGLE CALENDAR AND SALTOS
	$gevents=__gcalendar_feed($service);
	$query="SELECT a.* FROM tbl_agenda a LEFT JOIN tbl_registros_i f ON f.id_aplicacion='".page2id("agenda")."' AND f.id_registro=a.id WHERE f.id_usuario='".current_user()."'";
	$result=db_query($query);
	$sevents=array();
	while($row=db_fetch_row($result)) {
		foreach($row as $key=>$val) $row[$key]=str_replace("\r","",trim($val));
		$sevents[]=array(
			"id"=>$row["id"],
			"title"=>$row["nombre"],
			"content"=>$row["descripcion"],
			"where"=>$row["lugar"],
			"dstart"=>$row["dstart"],
			"dstop"=>$row["dstop"],
			"id_gcalendar"=>$row["id_gcalendar"],
			"sync_gcalendar"=>$row["sync_gcalendar"],
			"hash"=>md5(serialize(array($row["nombre"],$row["descripcion"],$row["lugar"],$row["dstart"],$row["dstop"])))
		);
	}
	db_free($result);
	// BEGIN THE SYNCHRONIZATION FROM SALTOS TO GOOGLE CALENDAR
	$count_insert_gcalendar=0;
	$count_update_gcalendar=0;
	foreach($sevents as $skey=>$sevent) {
		$finded=false;
		foreach($gevents as $gkey=>$gevent) {
			if($gevent["id"]==$sevent["id_gcalendar"]) {
				if(!$sevent["sync_gcalendar"]) {
					if($gevent["hash"]!=$sevent["hash"]) {
						__gcalendar_update($service,$gevent["edit"],$sevent["title"],$sevent["content"],$sevent["where"],$sevent["dstart"],$sevent["dstop"]);
						$count_update_gcalendar++;
					}
					$query2=make_update_query("tbl_agenda",array(
						"sync_gcalendar"=>"1"
					),"id='${sevent["id"]}'");
					db_query($query2);
					unset($sevents[$skey]);
					unset($gevents[$gkey]);
				}
				$finded=true;
			}
		}
		if(!$finded) {
			$id_gcalendar=__gcalendar_insert($service,$sevent["title"],$sevent["content"],$sevent["where"],$sevent["dstart"],$sevent["dstop"]);
			$count_insert_gcalendar++;
			$query2=make_update_query("tbl_agenda",array(
				"id_gcalendar"=>$id_gcalendar,
				"sync_gcalendar"=>1
			),"id='${sevent["id"]}'");
			db_query($query2);
			unset($sevents[$skey]);
		}
	}
	if($count_insert_gcalendar) {
		session_alert(LANG("insertgcalendar",$page)." ".$count_insert_gcalendar);
	}
	if($count_update_gcalendar) {
		session_alert(LANG("updategcalendar",$page)." ".$count_update_gcalendar);
	}
	// BEGIN THE SYNCHRONIZATION FROM GOOGLE CALENDAR TO SALTOS
	$count_insert_saltos=0;
	$count_update_saltos=0;
	foreach($gevents as $gkey=>$gevent) {
		$finded=false;
		foreach($sevents as $skey=>$sevent) {
			if($sevent["id_gcalendar"]==$gevent["id"]) {
				if($sevent["hash"]!=$gevent["hash"]) {
					$query2=make_update_query("tbl_agenda",array(
						"nombre"=>$gevent["title"],
						"descripcion"=>$gevent["content"],
						"lugar"=>$gevent["where"],
						"dstart"=>$gevent["dstart"],
						"dstop"=>$gevent["dstop"]
					),"id='${sevent["id"]}'");
					db_query($query2);
					$query2=make_insert_query("tbl_registros_u",array(
						"id_aplicacion"=>page2id("agenda"),
						"id_registro"=>$sevent["id"],
						"id_usuario"=>current_user(),
						"datetime"=>current_datetime()
					));
					db_query($query2);
					$count_update_saltos++;
					unset($gevents[$gkey]);
					unset($sevents[$skey]);
				}
				$finded=true;
			}
		}
		if(!$finded) {
			$query2=make_insert_query("tbl_agenda",array(
				"dstart"=>$gevent["dstart"],
				"dstop"=>$gevent["dstop"],
				"nombre"=>$gevent["title"],
				"lugar"=>$gevent["where"],
				"descripcion"=>$gevent["content"],
				"id_gcalendar"=>$gevent["id"],
				"sync_gcalendar"=>1
			));
			db_query($query2);
			$query2=make_insert_query("tbl_agenda_u",array(
				"id_agenda"=>execute_query("SELECT MAX(id) FROM tbl_agenda"),
				"id_usuario"=>current_user()
			));
			db_query($query2);
			$query2=make_insert_query("tbl_registros_i",array(
				"id_aplicacion"=>page2id("agenda"),
				"id_registro"=>execute_query("SELECT MAX(id) FROM tbl_agenda"),
				"id_usuario"=>current_user(),
				"datetime"=>current_datetime()
			));
			db_query($query2);
			$count_insert_saltos++;
			unset($gevents[$gkey]);
		}
	}
	if($count_insert_saltos) {
		session_alert(LANG("insertsaltos",$page)." ".$count_insert_saltos);
	}
	if($count_update_saltos) {
		session_alert(LANG("updatesaltos",$page)." ".$count_update_saltos);
	}
	if($count_insert_gcalendar+$count_update_gcalendar+$count_insert_saltos+$count_update_saltos==0) {
		session_alert(LANG("notinsertupdate",$page));
	}
	// GO BACK
	javascript_history(-1);
	die();
}
?>