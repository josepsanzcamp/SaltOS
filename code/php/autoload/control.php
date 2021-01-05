<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2021 by Josep Sanz Campderrós
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

function make_control($id_aplicacion=null,$id_registro=null,$id_usuario=null,$datetime=null) {
	// CHECK PARAMETERS
	if($id_aplicacion===null) $id_aplicacion=page2id(getParam("page"));
	$tabla=id2table($id_aplicacion);
	if($tabla=="") return -1;
	if($id_registro===null) $id_registro=execute_query("SELECT MAX(id) FROM ${tabla}");
	if($id_usuario===null) $id_usuario=current_user();
	if($datetime===null) $datetime=current_datetime();
	if(is_string($id_registro) && strpos($id_registro,",")!==false) $id_registro=explode(",",$id_registro);
	if(is_array($id_registro)) {
		$result=array();
		foreach($id_registro as $id) $result[]=make_control($id_aplicacion,$id,$id_usuario,$datetime);
		return $result;
	}
	// BUSCAR SI EXISTE REGISTRO DE CONTROL
	$query="SELECT id FROM tbl_registros WHERE id_aplicacion='${id_aplicacion}' AND id_registro='${id_registro}' AND first='1'";
	$id_control=execute_query($query);
	// SOME CHECKS
	if(is_array($id_control)) {
		$temp=$id_control;
		$id_control=array_pop($temp);
		foreach($temp as $temp2) {
			$query="DELETE FROM tbl_registros WHERE id='${temp2}'";
			db_query($query);
		}
	}
	// BUSCAR SI EXISTEN DATOS DE LA TABLA PRINCIPAL
	$query="SELECT id FROM ${tabla} WHERE id='${id_registro}'";
	$id_data=execute_query($query);
	if(!$id_data) {
		if($id_control) {
			$query="DELETE FROM tbl_registros WHERE id_aplicacion='${id_aplicacion}' AND id_registro='${id_registro}'";
			db_query($query);
			return 3;
		} else {
			return -2;
		}
	}
	if($id_control) {
		$query=make_insert_query("tbl_registros",array(
			"id_aplicacion"=>$id_aplicacion,
			"id_registro"=>$id_registro,
			"id_usuario"=>$id_usuario,
			"datetime"=>$datetime,
			"first"=>0
		));
		db_query($query);
		return 2;
	} else {
		$query=make_insert_query("tbl_registros",array(
			"id_aplicacion"=>$id_aplicacion,
			"id_registro"=>$id_registro,
			"id_usuario"=>$id_usuario,
			"datetime"=>$datetime,
			"first"=>1
		));
		db_query($query);
		return 1;
	}
}

function get_id_control($id_aplicacion,$id_registro) {
	$query="SELECT id FROM tbl_registros WHERE id_aplicacion='${id_aplicacion}' AND id_registro='${id_registro}' AND first='1'";
	$id_control=execute_query($query);
	return $id_control;
}

?>