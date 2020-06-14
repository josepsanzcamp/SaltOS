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

// FOR TEST PURPOSES
if(getParam("alert")!="") {
	session_alert(getParam("alert"));
	die();
}

// FOR TEST PURPOSES
if(getParam("error")!="") {
	session_error(getParam("error"));
	die();
}

$timestamp=getParam("timestamp",time());
$semaphore=array("session_id"=>session_id());
$_RESULT=array();
for(;;) {
	if(time_get_usage(true)>300) break;
	if(!semaphore_acquire($semaphore)) break;
	sess_init();
	if(isset($_SESSION["alerts"])) {
		foreach($_SESSION["alerts"] as $key=>$val) {
			set_array($_SESSION["messages"],"message",array(
				"type"=>"alert",
				"message"=>$val,
				"timestamp"=>time(),
			));
			unset($_SESSION["alerts"][$key]);
		}
	}
	if(isset($_SESSION["errors"])) {
		foreach($_SESSION["errors"] as $key=>$val) {
			set_array($_SESSION["messages"],"message",array(
				"type"=>"error",
				"message"=>$val,
				"timestamp"=>time(),
			));
			unset($_SESSION["errors"][$key]);
		}
	}
	if(isset($_SESSION["messages"])) {
		foreach($_SESSION["messages"] as $key=>$val) {
			if($val["timestamp"]<time()-300) {
				unset($_SESSION["messages"][$key]);
			}
		}
		foreach($_SESSION["messages"] as $key=>$val) {
			if($val["timestamp"]>$timestamp) {
				if(!isset($_RESULT["messages"])) $_RESULT["messages"]=array();
				set_array($_RESULT["messages"],"message",$val);
			}
		}
	}
	sess_close();
	semaphore_release($semaphore);
	if(count($_RESULT)) break;
	// TRICK TO DETECT SERVER RESTART
	$time1=microtime(true);
	sleep(1);
	$time2=microtime(true);
	if($time2-$time1<1) break;
}

$json=json_encode($_RESULT);
output_handler(array(
	"data"=>$json,
	"type"=>"application/json",
	"cache"=>false
));
die();

?>
