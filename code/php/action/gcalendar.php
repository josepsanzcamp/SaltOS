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
	// EXTERNAL LIBRARIES
	require_once("lib/google/autoload.php");
	require_once("lib/phpclasses/http.php");
	// FUNCTIONS FOR THE NEW API V3
	function __gcalendar_getattr($html,$attr) {
		$pos1=stripos($html,$attr);
		if($pos1===false) return "";
		$len=strlen($attr);
		$pos2=$pos1+$len;
		$max=strlen($html);
		while($pos2<$max && $html[$pos2]!='=') $pos2++;
		if($pos2==$max) return "";
		while($pos2<$max && $html[$pos2]!='"' && $html[$pos2]!="'") $pos2++;
		if($pos2==$max) return "";
		$pos3=$pos2+1;
		while($pos3<$max && $html[$pos3]!='"' && $html[$pos3]!="'") $pos3++;
		if($pos3==$max) return "";
		return substr($html,$pos2+1,$pos3-$pos2-1);
	}

	function __gcalendar_getnode($html,$name) {
		$pos1=stripos($html,"<${name}");
		if($pos1===false) return "";
		$len=strlen($name);
		$pos2=strpos($html,">",$pos1);
		if($pos2===false || $pos2<$pos1) return "";
		$pos3=stripos($html,"</${name}>",$pos2);
		if($pos3!==false) {
			if($pos3<$pos2) return "";
			$node=substr($html,$pos1,$pos3+$len+3-$pos1);
		} else {
			$node=substr($html,$pos1,$pos2+1-$pos1);
		}
		return $node;
	}

	function __gcalendar_parse($html) {
		$html=remove_script_tag($html);
		$html=remove_style_tag($html);
		$html=__gcalendar_getnode($html,"form");
		$method=__gcalendar_getattr($html,"method");
		$action=__gcalendar_getattr($html,"action");
		$inputs=array();
		for(;;) {
			$input=__gcalendar_getnode($html,"input");
			if($input=="") break;
			$html=str_replace($input,"",$html);
			$inputs[__gcalendar_getattr($input,"name")]=__gcalendar_getattr($input,"value");
		}
		return array("method"=>$method,"action"=>$action,"inputs"=>$inputs);
	}

	function __gcalendar_request($url,$method="",$values=array(),$cookies=array(),$referer="") {
		require_once("lib/phpclasses/http.php");
		$http=new http_class;
		$http->user_agent=get_name_version_revision();
		$http->follow_redirect=1;
		if(count($cookies)) $http->RestoreCookies($cookies);
		$arguments=array();
		$error=$http->GetRequestArguments($url,$arguments);
		if($error!="") show_php_error(array("phperror"=>$error));
		$error=$http->Open($arguments);
		if($error!="") show_php_error(array("phperror"=>$error));
		if($method!="") $arguments["RequestMethod"]=strtoupper($method);
		if(count($values)) $arguments["PostValues"]=$values;
		if($referer!="") $arguments["Referer"]=$referer;
		$error=$http->SendRequest($arguments);
		if($error!="") show_php_error(array("phperror"=>$error));
		$headers=array();
		$error=$http->ReadReplyHeaders($headers);
		if($error!="") show_php_error(array("phperror"=>$error));
		$body="";
		$error=$http->ReadWholeReplyBody($body);
		if($error!="") show_php_error(array("phperror"=>$error));
		$http->Close();
		//~ echo $body;die();
		//~ echo "<pre>".sprintr($headers)."</pre>";die();
		$cookies=array();
		$http->SaveCookies($cookies);
		return array($body,$headers,$cookies);
	}

	function __gcalendar_token1($client,$login,$password) {
		$url=$client->createAuthUrl();
		list($body,$header,$cookies)=__gcalendar_request($url);
		// PROCESS LOGIN PAGE
		$parsed=__gcalendar_parse($body);
		$parsed["inputs"]["Email"]=$login;
		$parsed["inputs"]["Passwd"]=$password;
		$parsed["inputs"]["continue"]=str_replace("&amp;","&",$parsed["inputs"]["continue"]);
		//~ echo "<pre>".sprintr($parsed)."</pre>";die();
		list($body,$header,$cookies)=__gcalendar_request($parsed["action"],$parsed["method"],$parsed["inputs"],$cookies,$url);
		// PROCESS ACCEPT PAGE
		$url=$parsed["action"];
		$parsed=__gcalendar_parse($body);
		$parsed["action"]=str_replace("&amp;","&",$parsed["action"]);
		$parsed["inputs"]["submit_access"]="true";
		//~ echo "<pre>".sprintr($parsed)."</pre>";die();
		list($body,$header,$cookies)=__gcalendar_request($parsed["action"],$parsed["method"],$parsed["inputs"],$cookies,$url);
		// PROCESS TOKEN PAGE
		$html=__gcalendar_getnode($body,"input");
		$token1=__gcalendar_getattr($html,"value");
		return $token1;
	}

	function __gcalendar_connect($login,$password) {
		$client=new Google_Client();
		$client->setAuthConfigFile("lib/google/saltos.json");
		$client->setRedirectUri("urn:ietf:wg:oauth:2.0:oob");
		$client->addScope("https://www.googleapis.com/auth/calendar");
		$client->setAccessType("offline");
		$token2=execute_query("SELECT token2 FROM tbl_gcalendar WHERE id_usuario='".current_user()."'");
		if($token2!="") {
			$client->setAccessToken(base64_decode($token2));
			if($client->getAccessToken()) return $client;
		}
		$token1=__gcalendar_token1($client,$login,$password);
		if($token1=="") return null;
		$client->authenticate($token1);
		$token2=$client->getAccessToken();
		if(!$token2) return null;
		$query=make_update_query("tbl_gcalendar",array(
			"token2"=>base64_encode($token2)
		),"id_usuario='".current_user()."'");
		db_query($query);
		return $client;
	}
	// MODIFIED FUNCTIONS
	function __gcalendar_format($datetime) {
		return date("Y-m-d\TH:i:sP",strtotime($datetime));
	}

	function __gcalendar_unformat($datetime) {
		return date("Y-m-d H:i:s",strtotime($datetime));
	}

	function __gcalendar_insert($client,$title,$content,$where,$dstart,$dstop) {
		// CHECK FOR A VALID SERVICE
		if($client===null) return false;
		// PREPARE DATETIME STRINGS
		$dstart=__gcalendar_format($dstart);
		$dstop=__gcalendar_format($dstop);
		$service=new Google_Service_Calendar($client);
		$event=new Google_Service_Calendar_Event();
		$event->setSummary($title);
		$event->setDescription($content);
		$event->setLocation($where);
		$start=new Google_Service_Calendar_EventDateTime();
		$start->setDateTime($dstart);
		$event->setStart($start);
		$end=new Google_Service_Calendar_EventDateTime();
		$end->setDateTime($dstop);
		$event->setEnd($end);
		$createdEvent=$service->events->insert("primary",$event);
		return $createdEvent->getId();
	}

	function __gcalendar_update($client,$id,$title,$content,$where,$dstart,$dstop) {
		// CHECK FOR A VALID SERVICE
		if($client===null) return false;
		// PREPARE DATETIME STRINGS
		$dstart=__gcalendar_format($dstart);
		$dstop=__gcalendar_format($dstop);
		// First retrieve the event from the API.
		$service=new Google_Service_Calendar($client);
		$event=$service->events->get("primary",$id);
		$event->setSummary($title);
		$event->setDescription($content);
		$event->setLocation($where);
		$start = new Google_Service_Calendar_EventDateTime();
		$start->setDateTime($dstart);
		$event->setStart($start);
		$end = new Google_Service_Calendar_EventDateTime();
		$end->setDateTime($dstop);
		$event->setEnd($end);
		$updatedEvent=$service->events->update("primary",$id,$event);
		return true;
	}

	function __gcalendar_feed($client) {
		// CHECK FOR A VALID SERVICE
		if($client===null) return false;
		// CONTINUE
		$service=new Google_Service_Calendar($client);
		$events=$service->events->listEvents("primary");
		$result=array();
		while(true) {
			foreach($events->getItems() as $event) {
				$temp=array(
					"id"=>$event->getId(),
					"title"=>$event->getSummary(),
					"content"=>$event->getDescription(),
					"where"=>$event->getLocation(),
					"dstart"=>__gcalendar_unformat($event->getStart()->getDateTime()),
					"dstop"=>__gcalendar_unformat($event->getEnd()->getDateTime())
				);
				foreach($temp as $key=>$val) $temp[$key]=str_replace("\r","",trim($val));
				$temp["hash"]=md5(serialize(array($temp["title"],$temp["content"],$temp["where"],$temp["dstart"],$temp["dstop"])));
				$result[]=$temp;
			}
			$pageToken=$events->getNextPageToken();
			if($pageToken) {
				$optParams=array("pageToken"=>$pageToken);
				$events=$service->events->listEvents("primary",$optParams);
			} else {
				break;
			}
		}
		return $result;
	}
	// GET A VALID SERVICE
	capture_next_error();
	$client=__gcalendar_connect($login,$password);
	$error=get_clear_error();
	if($error!="") {
		session_error(LANG("gcalendarerror",$page));
		javascript_history(-1);
		die();
	}
	if($client===null) {
		session_error(LANG("invalidgcalendarpassword",$page));
		javascript_history(-1);
		die();
	}
	// FOR COMPATIBILITY WITH GDATA AND APIV3
	$oldid="http://www.google.com/calendar/feeds/default/private/full/";
	$oldidlen=strlen($oldid)+1;
	$query="UPDATE tbl_agenda SET id_gcalendar=SUBSTR(id_gcalendar,${oldidlen}) WHERE id_gcalendar LIKE '${oldid}%'";
	db_query($query);
	// GET DATAS FROM GOOGLE CALENDAR AND SALTOS
	$gevents=__gcalendar_feed($client);
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
				"id_usuario"=>current_user()
			),array(
				"id_agenda"=>"SELECT MAX(id) FROM tbl_agenda"
			));
			db_query($query2);
			$query2=make_insert_query("tbl_registros_i",array(
				"id_aplicacion"=>page2id("agenda"),
				"id_usuario"=>current_user(),
				"datetime"=>current_datetime()
			),array(
				"id_registro"=>"SELECT MAX(id) FROM tbl_agenda"
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