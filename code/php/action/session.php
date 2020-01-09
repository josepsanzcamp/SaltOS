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
if(getParam("action")=="session") {
	$action2=getParam("action2");
	if($action2=="update") {
		$id_session=current_session();
		$sess_time=time();
		$query=make_update_query("tbl_sessions",array(
			"sess_time"=>$sess_time
		),"id='${id_session}'");
		db_query($query);
		javascript_alert(LANG("sessionupdated"));
	} else {
		$id_session=current_session();
		$query="SELECT sess_time FROM tbl_sessions WHERE id='${id_session}'";
		$time=execute_query($query);
		$remain=max(getDefault("sess/timeout")-(time()-$time),0);
		if($remain<=0) {
			$query="DELETE FROM tbl_sessions WHERE id='${id_session}'";
			db_query($query);
		}
		if(useCookie("remember")) $remain=max(useCookie("__remember__")-time(),$remain);
		if($remain<CONFIG("session_warning")) {
			$remain=array(0,0,$remain);
			if($remain[2]>60) {
				$remain[1]=intval($remain[2]/60);
				$remain[2]=intval($remain[2]%60);
			}
			if($remain[1]>60) {
				$remain[0]=intval($remain[1]/60);
				$remain[1]=intval($remain[1]%60);
			}
			foreach($remain as $key=>$val) if(!$val) unset($remain[$key]); else break;
			foreach($remain as $key=>$val) $remain[$key]=sprintf("%02d",$val);
			$remain=implode(":",$remain);
			$update=" <a href='javascript:void(0)' onclick='updatesession()'>[".LANG("sessionupdate")."]</a>";
			javascript_alert(LANG("sessionexpire").$remain.$update);
		}
	}
	javascript_headers();
	die();
}

?>