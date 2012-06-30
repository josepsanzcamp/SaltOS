<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2011 by Josep Sanz Campderrós
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
set_include_path("lib/gdataapis:".get_include_path());
include("Google/Calendar.php");

function __gcalendar_connect($email,$password) {
	$service=new Google_Calendar;
	$result=$service->requestClientLogin($email,$password);
	if(!$result) return null;
	return $service;
}

function __gcalendar_format($datetime) {
	return date("Y-m-d\TH:i:s.000P",strtotime($datetime));
}

function __gcalendar_unformat($datetime) {
	return date("Y-m-d H:i:s",strtotime($datetime));
}

function __gcalendar_insert($service,$title,$content,$where,$dstart,$dstop) {
	// CHECK FOR A VALID SERVICE
	if($service==null) return false;
	// PREPARE DATETIME STRINGS
	$dstart=__gcalendar_format($dstart);
	$dstop=__gcalendar_format($dstop);
	// PREPARE XML DATA
	$data="<entry xmlns=\"http://www.w3.org/2005/Atom\" xmlns:gd=\"http://schemas.google.com/g/2005\">\n";
	$data.="<category scheme=\"http://schemas.google.com/g/2005#kind\" term=\"http://schemas.google.com/g/2005#event\"></category>\n";
	$data.="<title type=\"text\">$title</title>\n";
	$data.="<content type=\"text\">$content</content>\n";
	$data.="<gd:where valueString=\"$where\"></gd:where>\n";
	$data.="<gd:when startTime=\"$dstart\" endTime=\"$dstop\"></gd:when>\n";
	$data.="</entry>\n";
	// MAKE THE INSERT
	if(!$service->insert($data)) return false;
	// RETURN THE NEW ID
	$xml=$service->getResponseBody();
	$data=xml2struct($xml);
	$data=__gcalendar_struct($data);
	return $data[0]["id"];
}

function __gcalendar_update($service,$url,$title,$content,$where,$dstart,$dstop) {
	// CHECK FOR A VALID SERVICE
	if($service==null) return false;
	// PREPARE DATETIME STRINGS
	$dstart=__gcalendar_format($dstart);
	$dstop=__gcalendar_format($dstop);
	// PREPARE XML DATA
	$data="<entry xmlns=\"http://www.w3.org/2005/Atom\" xmlns:gd=\"http://schemas.google.com/g/2005\">\n";
	$data.="<category scheme=\"http://schemas.google.com/g/2005#kind\" term=\"http://schemas.google.com/g/2005#event\"></category>\n";
	$data.="<title type=\"text\">$title</title>\n";
	$data.="<content type=\"text\">$content</content>\n";
	$data.="<gd:where valueString=\"$where\"></gd:where>\n";
	$data.="<gd:when startTime=\"$dstart\" endTime=\"$dstop\"></gd:when>\n";
	$data.="</entry>\n";
	// MAKE THE UPDATE
	if(!$service->update($data,$url)) return false;
	return true;
}

function __gcalendar_delete($service,$url) {
	// CHECK FOR A VALID SERVICE
	if($service==null) return false;
	// MAKE THE DELETE
	if(!$service->delete($url)) return false;
	return true;
}

function __gcalendar_struct($datas) {
	$result=array();
	$entry=false;
	foreach($datas as $data) {
		switch($data["tag"]) {
			case "entry":
				switch($data["type"]) {
					case "open":
						if($entry) show_php_error(array("xmlerror"=>"Already open entry"));
						$subresult=array();
						$entry=true;
						break;
					case "close":
						if(!$entry) show_php_error(array("xmlerror"=>"Close of a not open entry"));
						if(isset($subresult["title"]) && isset($subresult["dstart"]) && isset($subresult["dstop"])) {
							foreach($subresult as $key=>$val) $subresult[$key]=str_replace("\r","",trim($val));
							if(!isset($subresult["content"])) $subresult["content"]="";
							if(!isset($subresult["where"])) $subresult["where"]="";
							$subresult["hash"]=md5(serialize(array($subresult["title"],$subresult["content"],$subresult["where"],$subresult["dstart"],$subresult["dstop"])));
							$result[]=$subresult;
						}
						$entry=false;
						break;
					default:
						show_php_error(array("xmlerror"=>"Unknown entry type"));
						break;
				}
				break;
			case "id":
				if(isset($data["value"])) {
					$subresult["id"]=$data["value"];
				}
				break;
			case "title":
				if(isset($data["value"])) {
					$subresult["title"]=$data["value"];
				}
				break;
			case "content":
				if(isset($data["value"])) {
					$subresult["content"]=$data["value"];
				}
				break;
			case "link":
				if(isset($data["attributes"]["rel"])) {
					if($data["attributes"]["rel"]=="edit") {
						if(isset($data["attributes"]["href"])) {
							$subresult["edit"]=$data["attributes"]["href"];
						}
					}
				}
				break;
			case "gd:where":
				if(isset($data["attributes"]["valueString"])) {
					$subresult["where"]=$data["attributes"]["valueString"];
				}
				break;
			case "gd:when":
				if(isset($data["attributes"]["startTime"])) {
					$subresult["dstart"]=$data["attributes"]["startTime"];
					$subresult["dstart"]=__gcalendar_unformat($subresult["dstart"]);
				}
				if(isset($data["attributes"]["endTime"])) {
					$subresult["dstop"]=$data["attributes"]["endTime"];
					$subresult["dstop"]=__gcalendar_unformat($subresult["dstop"]);
				}
				break;
		}
	}
	return $result;
}

function __gcalendar_feed($service,$extra=array()) {
	// CHECK FOR A VALID SERVICE
	if($service==null) return false;
	// REQUEST THE FEED
	$result=array();
	$index=1;
	$maxres=1000;
	while(true) {
		$queries=array_merge(array("max-results"=>$maxres,"start-index"=>$index),$extra);
		if(!$service->requestFeed($queries)) return false;
		$xml=$service->getResponseBody();
		$data=xml2struct($xml);
		$data=__gcalendar_struct($data);
		if(count($data)==0) break;
		$result=array_merge($result,$data);
		$index+=$maxres;
	}
	return $result;
}
?>