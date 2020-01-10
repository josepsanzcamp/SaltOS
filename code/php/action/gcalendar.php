<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2020 by Josep Sanz Campderrós
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
if(getParam("action")=="gcalendar") {
	// EXTERNAL LIBRARIES
	require_once("lib/google/autoload.php");
	require_once("php/libaction.php");

	// GET GOOGLE CALENDAR USER ACCOUNT
	$query="SELECT email,token,token2 FROM tbl_gcalendar WHERE id_usuario='".current_user()."'";
	$result=execute_query($query);
	$email=$result["email"];
	$token=$result["token"];
	$token2=base64_decode($result["token2"]);
	if($email=="") {
		session_error(LANG("notgcalendaremail",$page));
		javascript_history(-1);
		die();
	}

	// GET A VALID SERVICE
	$client=new Google_Client();
	$client->setAuthConfigFile("lib/google/saltos.json");
	$client->setRedirectUri("urn:ietf:wg:oauth:2.0:oob");
	$client->addScope("https://www.googleapis.com/auth/calendar");
	$client->setAccessType("offline");
	if($token2!="") {
		$client->setAccessToken($token2);
		if(!$client->getAccessToken()) {
			__gcalendar_updatetokens("","");
			__gcalendar_invalidtoken();
			__gcalendar_requesttoken($client);
			javascript_history(-1);
			die();
		}
	} elseif($token!="") {
		try {
			$client->authenticate($token);
			$token2=$client->getAccessToken();
			__gcalendar_updatetokens("",base64_encode($token2));
			db_query($query);
		} catch(Exception $e) {
			__gcalendar_updatetokens("","");
			__gcalendar_invalidtoken();
			__gcalendar_requesttoken($client);
			javascript_history(-1);
			die();
		}
	} else {
		__gcalendar_requesttoken($client);
		javascript_history(-1);
		die();
	}

	// FOR COMPATIBILITY WITH GDATA AND APIV3
	$oldid="http://www.google.com/calendar/feeds/default/private/full/";
	$oldidlen=strlen($oldid)+1;
	$query="UPDATE tbl_agenda SET id_gcalendar=SUBSTR(id_gcalendar,${oldidlen}) WHERE id_gcalendar LIKE '${oldid}%'";
	db_query($query);

	// GET DATAS FROM GOOGLE CALENDAR AND SALTOS
	try {
		$gevents=__gcalendar_feed($client);
	} catch(Exception $e) {
		__gcalendar_updatetokens("","");
		__gcalendar_invalidtoken();
		__gcalendar_requesttoken($client);
		javascript_history(-1);
		die();
	}
	$query="SELECT a.* FROM tbl_agenda a LEFT JOIN tbl_registros f ON f.id_aplicacion='".page2id("agenda")."' AND f.id_registro=a.id AND f.first=1 WHERE f.id_usuario='".current_user()."'";
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
			"hash"=>md5(json_encode(array($row["nombre"],$row["descripcion"],$row["lugar"],$row["dstart"],$row["dstop"])))
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
						__gcalendar_update($client,$sevent["id_gcalendar"],$sevent["title"],$sevent["content"],$sevent["where"],$sevent["dstart"],$sevent["dstop"]);
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
			$id_gcalendar=__gcalendar_insert($client,$sevent["title"],$sevent["content"],$sevent["where"],$sevent["dstart"],$sevent["dstop"]);
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
					make_control(page2id("agenda"),$sevent["id"]);
					make_indexing(page2id("agenda"),$sevent["id"]);
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
				"sync_gcalendar"=>1,
				"ids_asignados"=>current_user(),
			));
			db_query($query2);
			make_control(page2id("agenda"));
			make_indexing(page2id("agenda"));
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