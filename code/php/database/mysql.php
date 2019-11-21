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

class database_mysql {
	private $link=null;

	function __construct($args) {
		if(!function_exists("mysql_connect")) { show_php_error(array("phperror"=>"mysql_connect not found","details"=>"Try to install php-mysql package")); return; }
		$this->link=mysql_connect($args["host"].":".$args["port"],$args["user"],$args["pass"]);
		if($this->link===false) show_php_error(array("dberror"=>mysql_error()));
		if(!mysql_select_db($args["name"],$this->link)) show_php_error(array("dberror"=>mysql_error($this->link)));
		if($this->link) {
			$this->db_query("SET NAMES 'utf8mb4'");
			$this->db_query("SET FOREIGN_KEY_CHECKS=0");
			$this->db_query("SET GROUP_CONCAT_MAX_LEN:=@@MAX_ALLOWED_PACKET");
		}
	}

	function db_query($query,$fetch="query") {
		$query=parse_query($query,"MYSQL");
		$result=array("total"=>0,"header"=>array(),"rows"=>array());
		if(!strlen(trim($query))) return $result;
		// DO QUERY
		$stmt=mysql_query($query,$this->link);
		if($stmt===false) show_php_error(array("dberror"=>mysql_error($this->link),"query"=>$query));
		unset($query); // TRICK TO RELEASE MEMORY
		// DUMP RESULT TO MATRIX
		if(!is_bool($stmt) && mysql_num_fields($stmt)) {
			if($fetch=="auto") {
				$fetch=mysql_num_fields($stmt)>1?"query":"column";
			}
			if($fetch=="query") {
				while($row=mysql_fetch_assoc($stmt)) $result["rows"][]=$row;
				$result["total"]=count($result["rows"]);
				if($result["total"]>0) $result["header"]=array_keys($result["rows"][0]);
				mysql_free_result($stmt);
			}
			if($fetch=="column") {
				while($row=mysql_fetch_row($stmt)) $result["rows"][]=$row[0];
				$result["total"]=count($result["rows"]);
				$result["header"]=array("__a__");
				mysql_free_result($stmt);
			}
		}
		return $result;
	}

	function db_disconnect() {
		mysql_close($this->link);
		$this->link=null;
	}
}

?>