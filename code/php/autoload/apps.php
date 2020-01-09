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

function __aplicaciones($tipo,$dato,$def) {
	static $diccionario=array();
	if(!count($diccionario)) {
		$query="SELECT id,codigo,tabla,subtablas FROM tbl_aplicaciones";
		$result=db_query($query);
		$diccionario["page2id"]=array();
		$diccionario["id2page"]=array();
		$diccionario["page2table"]=array();
		$diccionario["table2page"]=array();
		$diccionario["id2table"]=array();
		$diccionario["table2id"]=array();
		$diccionario["id2subtables"]=array();
		$diccionario["page2subtables"]=array();
		$diccionario["table2subtables"]=array();
		while($row=db_fetch_row($result)) {
			$diccionario["page2id"][$row["codigo"]]=$row["id"];
			$diccionario["id2page"][$row["id"]]=$row["codigo"];
			$diccionario["page2table"][$row["codigo"]]=$row["tabla"];
			$diccionario["table2page"][$row["tabla"]]=$row["codigo"];
			$diccionario["id2table"][$row["id"]]=$row["tabla"];
			$diccionario["table2id"][$row["tabla"]]=$row["id"];
			$diccionario["id2subtables"][$row["id"]]=$row["subtablas"];
			$diccionario["page2subtables"][$row["codigo"]]=$row["subtablas"];
			$diccionario["table2subtables"][$row["tabla"]]=$row["subtablas"];
		}
		db_free($result);
	}
	if(!isset($diccionario[$tipo])) return $def;
	if(!isset($diccionario[$tipo][$dato])) return $def;
	return $diccionario[$tipo][$dato];
}

function page2id($page,$def="") {
	return __aplicaciones(__FUNCTION__,$page,$def);
}

function id2page($id,$def="") {
	return __aplicaciones(__FUNCTION__,$id,$def);
}

function page2table($page,$def="") {
	return __aplicaciones(__FUNCTION__,$page,$def);
}

function table2page($table,$def="") {
	return __aplicaciones(__FUNCTION__,$table,$def);
}

function id2table($id,$def="") {
	return __aplicaciones(__FUNCTION__,$id,$def);
}

function table2id($table,$def="") {
	return __aplicaciones(__FUNCTION__,$table,$def);
}

function id2subtables($id,$def="") {
	return __aplicaciones(__FUNCTION__,$id,$def);
}

function page2subtables($page,$def="") {
	return __aplicaciones(__FUNCTION__,$page,$def);
}

function table2subtables($table,$def="") {
	return __aplicaciones(__FUNCTION__,$table,$def);
}

?>