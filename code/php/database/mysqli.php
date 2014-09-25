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
class database_mysqli {
	private $link=null;

	function database_mysqli($args) {
		if(!function_exists("mysqli_connect")) { show_php_error(array("phperror"=>"mysqli_connect not found","details"=>"Try to install php-mysql package")); return; }
		$this->link=mysqli_connect($args["host"],$args["user"],$args["pass"],$args["name"],$args["port"]);
		if($this->link) {
			$this->db_query("SET NAMES 'UTF8'");
			$this->db_query("SET FOREIGN_KEY_CHECKS=0");
			$this->db_query("SET GROUP_CONCAT_MAX_LEN:=@@MAX_ALLOWED_PACKET");
		}
	}

	function db_query($query,$fetch="query") {
		$query=parse_query($query,"MYSQL");
		$result=array("total"=>0,"header"=>array(),"rows"=>array());
		if(!$query) return $result;
		// DO QUERY
		$stmt=mysqli_query($this->link,$query);
		if($stmt===false) show_php_error(array("dberror"=>mysqli_error($this->link),"query"=>$query));
		// DUMP RESULT TO MATRIX
		if(!is_bool($stmt) && mysqli_num_fields($stmt)>0) {
			if($fetch=="auto") {
				$fetch=mysqli_num_fields($stmt)>1?"query":"column";
			}
			if($fetch=="query") {
				while($row=mysqli_fetch_assoc($stmt)) $result["rows"][]=$row;
				$result["total"]=count($result["rows"]);
				if($result["total"]>0) $result["header"]=array_keys($result["rows"][0]);
				mysqli_free_result($stmt);
			}
			if($fetch=="column") {
				while($row=mysqli_fetch_row($stmt)) $result["rows"][]=$row[0];
				$result["total"]=count($result["rows"]);
				$result["header"]=array("__a__");
				mysqli_free_result($stmt);
			}
		}
		return $result;
	}

	function db_disconnect() {
		mysqli_close($this->link);
		$this->link=null;
	}
}
?>