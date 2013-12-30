<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2013 by Josep Sanz Campderrós
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
if($page=="presupuestos") {
	// BUSCAR DATOS DEL PRESUPUESTO
	$id_presupuesto=abs(intval(getParam("id")));
	$query="SELECT * FROM tbl_presupuestos WHERE id='$id_presupuesto'";
	$row=execute_query($query);
	if($row===null) action_denied();
	$id_proyecto=$row["id_proyecto"];
	if($id_proyecto>0) {
		session_error(LANG("projectexists","presupuestos"));
		javascript_history(-1);
		die();
	}
	$id_cliente=$row["id_cliente"];
	if($id_cliente==0) {
		session_error(LANG("clientnotexists","presupuestos"));
		javascript_history(-1);
		die();
	}
	// CREAR PROYECTO
	$id_campanya=$row["id_campanya"];
	$nombre=$row["nombre"];
	$descripcion=$row["descripcion"];
	$query="INSERT INTO tbl_proyectos(`id`,`id_campanya`,`id_cliente`,`nombre`,`id_estado`,`descripcion`)
		VALUES(NULL,'$id_campanya','$id_cliente','$nombre','0','$descripcion')";
	db_query($query);
	// OBTENER ID DEL NUEVO PROYECTO
	$query="SELECT MAX(id) FROM tbl_proyectos";
	$id_proyecto=execute_query($query);
	// AÑADIR CONTROL DEL REGISTRO
	$id_aplicacion=page2id("proyectos");
	$id_usuario=current_user();
	$datetime=current_datetime();
	$query="INSERT INTO tbl_registros_i(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`)
		VALUES(NULL,'$id_aplicacion','$id_proyecto','$id_usuario','$datetime')";
	db_query($query);
	// COPIAR LAS TAREAS DEL PRESUPUESTO AL PROYECTO
	$query="SELECT * FROM tbl_presupuestos_t WHERE id_presupuesto='$id_presupuesto' ORDER BY id ASC";
	$result=execute_query_array($query);
	foreach($result as $row) {
		$tarea=$row["tarea"];
		$horas=$row["horas"];
		$precio=$row["precio"];
		$descuento=$row["descuento"];
		$query="INSERT INTO tbl_proyectos_t(`id`,`id_proyecto`,`tarea`,`horas`,`precio`,`descuento`)
			VALUES(NULL,'$id_proyecto','$tarea','$horas','$precio','$descuento')";
		db_query($query);
	}
	// COPIAR LOS PRODUCTOS DEL PRESUPUESTO AL PROYECTO
	$query="SELECT * FROM tbl_presupuestos_p WHERE id_presupuesto='$id_presupuesto' ORDER BY id ASC";
	$result=execute_query_array($query);
	foreach($result as $row) {
		$id_producto=$row["id_producto"];
		$concepto=addslashes($row["concepto"]);
		$unidades=$row["unidades"];
		$precio=$row["precio"];
		$descuento=$row["descuento"];
		$query="INSERT INTO tbl_proyectos_p(`id`,`id_proyecto`,`id_producto`,`concepto`,`unidades`,`precio`,`descuento`)
			VALUES(NULL,'$id_proyecto','$id_producto','$concepto','$unidades','$precio','$descuento')";
		db_query($query);
	}
	// RELACIONAR PRESUPUESTO CON EL PROYECTO
	$query="UPDATE tbl_presupuestos SET `id_proyecto`='$id_proyecto' WHERE id='$id_presupuesto'";
	db_query($query);
	// VOLVER
	session_alert(LANG("projectcreatedok","presupuestos"));
	javascript_history(-1);
	die();
}
if($page=="posiblescli") {
	// BUSCAR DATOS DEL POSIBLE CLIENTE
	$id_posiblecli=abs(intval(getParam("id")));
	$query="SELECT * FROM tbl_posiblescli WHERE id='$id_posiblecli'";
	$row=execute_query($query);
	if($row===null) action_denied();
	// CREAR CLIENTE
	$id_campanya=$row["id_campanya"];
	$nombre=$row["nombre"];
	$cif=$row["cif"];
	$comentarios=$row["comentarios"];
	$direccion=$row["direccion"];
	$id_pais=$row["id_pais"];
	$id_provincia=$row["id_provincia"];
	$id_poblacion=$row["id_poblacion"];
	$id_codpostal=$row["id_codpostal"];
	$nombre_pais=$row["nombre_pais"];
	$nombre_provincia=$row["nombre_provincia"];
	$nombre_poblacion=$row["nombre_poblacion"];
	$nombre_codpostal=$row["nombre_codpostal"];
	$email=$row["email"];
	$web=$row["web"];
	$tel_fijo=$row["tel_fijo"];
	$tel_movil=$row["tel_movil"];
	$fax=$row["fax"];
	$query="INSERT INTO tbl_clientes(`id`,`id_campanya`,`id_tipo`,`nombre`,`nombre1`,`nombre2`,`cif`,`comentarios`,`corriente`,`contable`,`diapago`,`direccion`,`id_pais`,`id_provincia`,`id_poblacion`,`id_codpostal`,`nombre_pais`,`nombre_provincia`,`nombre_poblacion`,`nombre_codpostal`,`email`,`web`,`tel_fijo`,`tel_movil`,`fax`)
		VALUES(NULL,'$id_campanya','1','$nombre','$nombre','$nombre','$cif','$comentarios','','','0','$direccion','$id_pais','$id_provincia','$id_poblacion','$id_codpostal','$nombre_pais','$nombre_provincia','$nombre_poblacion','$nombre_codpostal','$email','$web','$tel_fijo','$tel_movil','$fax')";
	db_query($query);
	// OBTENER ID DEL NUEVO CLIENTE
	$query="SELECT MAX(id) FROM tbl_clientes";
	$id_cliente=execute_query($query);
	// AÑADIR CONTROL DEL REGISTRO
	$id_aplicacion=page2id("clientes");
	$id_usuario=current_user();
	$datetime=current_datetime();
	$query="INSERT INTO tbl_registros_u(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`)
		VALUES(NULL,'$id_aplicacion','$id_cliente','$id_usuario','$datetime')";
	db_query($query);
	// CREAR CONTACTO
	$contacto=$row["contacto"];
	$cargo=$row["cargo"];
	$query="INSERT INTO tbl_contactos(`id`,`id_registro`,`id_aplicacion`,`nombre`,`nombre1`,`nombre2`,`cargo`,`comentarios`,`direccion`,`id_pais`,`id_provincia`,`id_poblacion`,`id_codpostal`,`nombre_pais`,`nombre_provincia`,`nombre_poblacion`,`nombre_codpostal`,`email`,`web`,`tel_fijo`,`tel_movil`,`fax`)
		VALUES(NULL,'$id_cliente','$id_aplicacion','$contacto','$contacto','$contacto','$cargo','$comentarios','$direccion','$id_pais','$id_provincia','$id_poblacion','$id_codpostal','$nombre_pais','$nombre_provincia','$nombre_poblacion','$nombre_codpostal','$email','$web','$tel_fijo','$tel_movil','$fax')";
	db_query($query);
	// OBTENER ID DEL NUEVO CONTACTO
	$query="SELECT MAX(id) FROM tbl_contactos";
	$id_contacto=execute_query($query);
	// AÑADIR CONTROL DEL REGISTRO
	$id_aplicacion2=page2id("contactos");
	$query="INSERT INTO tbl_registros_i(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`)
		VALUES(NULL,'$id_aplicacion2','$id_contacto','$id_usuario','$datetime')";
	db_query($query);
	// RELACIONAR AGENDAS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
	$query="UPDATE tbl_agenda SET `id_posiblecli`='0',id_cliente='$id_cliente' WHERE `id_posiblecli`='$id_posiblecli'";
	db_query($query);
	// RELACIONAR PRESUPUESTOS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
	$query="UPDATE tbl_presupuestos SET `id_posiblecli`='0',id_cliente='$id_cliente' WHERE `id_posiblecli`='$id_posiblecli'";
	db_query($query);
	// RELACIONAR ACTAS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
	$query="UPDATE tbl_actas SET `id_posiblecli`='0',id_cliente='$id_cliente' WHERE `id_posiblecli`='$id_posiblecli'";
	db_query($query);
	// RELACIONAR FICHEROS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
	$id_aplicacion3=page2id("posiblescli");
	$query="UPDATE tbl_ficheros SET `id_aplicacion`='$id_aplicacion',`id_registro`='$id_cliente' WHERE `id_aplicacion`='$id_aplicacion3' AND `id_registro`='$id_posiblecli'";
	db_query($query);
	// RELACIONAR COMENTARIOS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
	$query="UPDATE tbl_comentarios SET `id_aplicacion`='$id_aplicacion',`id_registro`='$id_cliente' WHERE `id_aplicacion`='$id_aplicacion3' AND `id_registro`='$id_posiblecli'";
	db_query($query);
	// MOVER CONTROL DEL REGISTRO DE POSIBLES CLIENTES A CLIENTES
	$query="UPDATE tbl_registros_i SET `id_aplicacion`='$id_aplicacion',`id_registro`='$id_cliente' WHERE `id_aplicacion`='$id_aplicacion3' AND `id_registro`='$id_posiblecli'";
	db_query($query);
	$query="UPDATE tbl_registros_u SET `id_aplicacion`='$id_aplicacion',`id_registro`='$id_cliente' WHERE `id_aplicacion`='$id_aplicacion3' AND `id_registro`='$id_posiblecli'";
	db_query($query);
	// BORRAR EL POSIBLE CLIENTE
	$query="DELETE FROM tbl_posiblescli WHERE `id`='$id_posiblecli'";
	db_query($query);
	// VOLVER
	session_alert(LANG("clientcreatedok","posiblescli"));
	javascript_history(-1);
	die();
}
?>