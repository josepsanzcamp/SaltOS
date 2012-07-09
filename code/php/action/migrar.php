<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2012 by Josep Sanz Campderrós
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
	if($row==null) action_denied();
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
	execute_query($query);
	// OBTENER ID DEL NUEVO PROYECTO
	$query="SELECT MAX(id) FROM tbl_proyectos";
	$id_proyecto=execute_query($query);
	// AÑADIR CONTROL DEL REGISTRO
	$id_aplicacion=page2id("proyectos");
	$id_usuario=current_user();
	$datetime=current_datetime();
	$query="INSERT INTO tbl_registros(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`,`primero`)
		VALUES(NULL,'$id_aplicacion','$id_proyecto','$id_usuario','$datetime','1')";
	execute_query($query);
	// COPIAR LAS TAREAS DEL PRESUPUESTO AL PROYECTO
	$query="SELECT * FROM tbl_presupuestos_t WHERE id_presupuesto='$id_presupuesto' ORDER BY id ASC";
	$result=execute_query($query);
	if($result!=null) {
		if(isset($result["id"])) $result=array($result);
		foreach($result as $row) {
			$tarea=$row["tarea"];
			$horas=$row["horas"];
			$precio=$row["precio"];
			$descuento=$row["descuento"];
			$query="INSERT INTO tbl_proyectos_t(`id`,`id_proyecto`,`tarea`,`horas`,`precio`,`descuento`)
				VALUES(NULL,'$id_proyecto','$tarea','$horas','$precio','$descuento')";
			execute_query($query);
		}
	}
	// COPIAR LOS PRODUCTOS DEL PRESUPUESTO AL PROYECTO
	$query="SELECT * FROM tbl_presupuestos_p WHERE id_presupuesto='$id_presupuesto' ORDER BY id ASC";
	$result=execute_query($query);
	if($result!=null) {
		if(isset($result["id"])) $result=array($result);
		foreach($result as $row) {
			$id_producto=$row["id_producto"];
			$unidades=$row["unidades"];
			$precio=$row["precio"];
			$descuento=$row["descuento"];
			$query="INSERT INTO tbl_proyectos_p(`id`,`id_proyecto`,`id_producto`,`unidades`,`precio`,`descuento`)
				VALUES(NULL,'$id_proyecto','$id_producto','$unidades','$precio','$descuento')";
			execute_query($query);
		}
	}
	// RELACIONAR PRESUPUESTO CON EL PROYECTO
	$query="UPDATE tbl_presupuestos SET `id_proyecto`='$id_proyecto' WHERE id='$id_presupuesto'";
	execute_query($query);
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
	if($row==null) action_denied();
	// CREAR CLIENTE
	$id_campanya=$row["id_campanya"];
	$id_importacion=$row["id_importacion"];
	$nombre=$row["nombre"];
	$cif=$row["cif"];
	$comentarios=$row["comentarios"];
	$query="INSERT INTO tbl_clientes(`id`,`id_campanya`,`id_importacion`,`id_tipo`,`nombre`,`nombre1`,`nombre2`,`cif`,`comentarios`,`corriente`,`contable`,`diapago`)
		VALUES(NULL,'$id_campanya','$id_importacion','1','$nombre','$nombre','$nombre','$cif','$comentarios','','','0')";
	execute_query($query);
	// OBTENER ID DEL NUEVO CLIENTE
	$query="SELECT MAX(id) FROM tbl_clientes";
	$id_cliente=execute_query($query);
	// AÑADIR CONTROL DEL REGISTRO
	$id_aplicacion=page2id("clientes");
	$id_usuario=current_user();
	$datetime=current_datetime();
	$query="INSERT INTO tbl_registros(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`,`primero`)
		VALUES(NULL,'$id_aplicacion','$id_cliente','$id_usuario','$datetime','0')";
	execute_query($query);
	// CREAR DIRECCION
	$nombre=LANG("sinnombre")." (".str_replace(array("-",":"," "),"",current_datetime()).")";
	$direccion=$row["direccion"];
	$id_pais=$row["id_pais"];
	$id_provincia=$row["id_provincia"];
	$id_poblacion=$row["id_poblacion"];
	$id_codpostal=$row["id_codpostal"];
	$nombre_pais=$row["nombre_pais"];
	$nombre_provincia=$row["nombre_provincia"];
	$nombre_poblacion=$row["nombre_poblacion"];
	$nombre_codpostal=$row["nombre_codpostal"];
	$query="INSERT INTO tbl_direcciones(`id`,`id_aplicacion`,`id_registro`,`nombre`,`direccion`,`id_pais`,`id_provincia`,`id_poblacion`,`id_codpostal`,`nombre_pais`,`nombre_provincia`,`nombre_poblacion`,`nombre_codpostal`,`seleccion`)
		VALUES(NULL,'$id_aplicacion','$id_cliente','$nombre','$direccion','$id_pais','$id_provincia','$id_poblacion','$id_codpostal','$nombre_pais','$nombre_provincia','$nombre_poblacion','$nombre_codpostal','0')";
	execute_query($query);
	// DISABLE DB CACHE
	$oldcache=set_use_cache("false");
	// OBTENER ID DE LA NUEVA DIRECCION
	$query="SELECT MAX(id) FROM tbl_direcciones";
	$id_direccion=execute_query($query);
	// RESTORE DB CACHE
	set_use_cache($oldcache);
	// CREAR COMUNICACIONES
	$tel_fijo=$row["tel_fijo"];
	if($tel_fijo) {
		$temp=$tel_fijo;
		$temp=str_replace(" ","",$temp);
		$temp=str_replace(";",",",$temp);
		$temp=explode(",",$temp);
		foreach($temp as $t) {
			$query="INSERT INTO tbl_comunicaciones(`id`,`id_aplicacion`,`id_registro`,`id_direccion`,`id_tipocom`,`nombre`,`valor`,`seleccion`)
				VALUES(NULL,'$id_aplicacion','$id_cliente','$id_direccion','1','','$t','0')";
			execute_query($query);
		}
	}
	$tel_movil=$row["tel_movil"];
	if($tel_movil) {
		$temp=$tel_movil;
		$temp=str_replace(" ","",$temp);
		$temp=str_replace(";",",",$temp);
		$temp=explode(",",$temp);
		foreach($temp as $t) {
			$query="INSERT INTO tbl_comunicaciones(`id`,`id_aplicacion`,`id_registro`,`id_direccion`,`id_tipocom`,`nombre`,`valor`,`seleccion`)
				VALUES(NULL,'$id_aplicacion','$id_cliente','$id_direccion','2','','$t','0')";
			execute_query($query);
		}
	}
	$email=$row["email"];
	if($email) {
		$temp=$email;
		$temp=str_replace(" ","",$temp);
		$temp=str_replace(";",",",$temp);
		$temp=explode(",",$temp);
		foreach($temp as $t) {
			$query="INSERT INTO tbl_comunicaciones(`id`,`id_aplicacion`,`id_registro`,`id_direccion`,`id_tipocom`,`nombre`,`valor`,`seleccion`)
				VALUES(NULL,'$id_aplicacion','$id_cliente','$id_direccion','5','','$t','0')";
			execute_query($query);
		}
	}
	$web=$row["web"];
	if($web) {
		$temp=$web;
		$temp=str_replace(" ","",$temp);
		$temp=str_replace(";",",",$temp);
		$temp=explode(",",$temp);
		foreach($temp as $t) {
			$query="INSERT INTO tbl_comunicaciones(`id`,`id_aplicacion`,`id_registro`,`id_direccion`,`id_tipocom`,`nombre`,`valor`,`seleccion`)
				VALUES(NULL,'$id_aplicacion','$id_cliente','$id_direccion','6','','$t','0')";
			execute_query($query);
		}
	}
	// CREAR CONTACTO
	$contacto=$row["contacto"];
	$cargo=$row["cargo"];
	$query="INSERT INTO tbl_contactos(id,id_registro,id_aplicacion,nombre,nombre1,nombre2,cargo,comentarios)
		VALUES(NULL,'$id_cliente','$id_aplicacion','$contacto','$contacto','$contacto','$cargo','$comentarios')";
	execute_query($query);
	// OBTENER ID DEL NUEVO CONTACTO
	$query="SELECT MAX(id) FROM tbl_contactos";
	$id_contacto=execute_query($query);
	// AÑADIR CONTROL DEL REGISTRO
	$id_aplicacion2=page2id("contactos");
	$query="INSERT INTO tbl_registros(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`,`primero`)
		VALUES(NULL,'$id_aplicacion2','$id_contacto','$id_usuario','$datetime','1')";
	execute_query($query);
	// CREAR DIRECCION
	$query="INSERT INTO tbl_direcciones(`id`,`id_aplicacion`,`id_registro`,`nombre`,`direccion`,`id_pais`,`id_provincia`,`id_poblacion`,`id_codpostal`,`nombre_pais`,`nombre_provincia`,`nombre_poblacion`,`nombre_codpostal`,`seleccion`)
		VALUES(NULL,'$id_aplicacion2','$id_contacto','$nombre','$direccion','$id_pais','$id_provincia','$id_poblacion','$id_codpostal','$nombre_pais','$nombre_provincia','$nombre_poblacion','$nombre_codpostal','0')";
	execute_query($query);
	// DISABLE DB CACHE
	$oldcache=set_use_cache("false");
	// OBTENER ID DE LA NUEVA DIRECCION
	$query="SELECT MAX(id) FROM tbl_direcciones";
	$id_direccion2=execute_query($query);
	// RESTORE DB CACHE
	set_use_cache($oldcache);
	// CREAR COMUNICACIONES
	if($tel_fijo) {
		$temp=$tel_fijo;
		$temp=str_replace(" ","",$temp);
		$temp=str_replace(";",",",$temp);
		$temp=explode(",",$temp);
		foreach($temp as $t) {
			$query="INSERT INTO tbl_comunicaciones(`id`,`id_aplicacion`,`id_registro`,`id_direccion`,`id_tipocom`,`nombre`,`valor`,`seleccion`)
				VALUES(NULL,'$id_aplicacion2','$id_contacto','$id_direccion2','1','','$t','0')";
			execute_query($query);
		}
	}
	if($tel_movil) {
		$temp=$tel_movil;
		$temp=str_replace(" ","",$temp);
		$temp=str_replace(";",",",$temp);
		$temp=explode(",",$temp);
		foreach($temp as $t) {
			$query="INSERT INTO tbl_comunicaciones(`id`,`id_aplicacion`,`id_registro`,`id_direccion`,`id_tipocom`,`nombre`,`valor`,`seleccion`)
				VALUES(NULL,'$id_aplicacion2','$id_contacto','$id_direccion2','2','','$t','0')";
			execute_query($query);
		}
	}
	if($email) {
		$temp=$email;
		$temp=str_replace(" ","",$temp);
		$temp=str_replace(";",",",$temp);
		$temp=explode(",",$temp);
		foreach($temp as $t) {
			$query="INSERT INTO tbl_comunicaciones(`id`,`id_aplicacion`,`id_registro`,`id_direccion`,`id_tipocom`,`nombre`,`valor`,`seleccion`)
				VALUES(NULL,'$id_aplicacion2','$id_contacto','$id_direccion2','5','','$t','0')";
			execute_query($query);
		}
	}
	if($web) {
		$temp=$web;
		$temp=str_replace(" ","",$temp);
		$temp=str_replace(";",",",$temp);
		$temp=explode(",",$temp);
		foreach($temp as $t) {
			$query="INSERT INTO tbl_comunicaciones(`id`,`id_aplicacion`,`id_registro`,`id_direccion`,`id_tipocom`,`nombre`,`valor`,`seleccion`)
				VALUES(NULL,'$id_aplicacion2','$id_contacto','$id_direccion2','6','','$t','0')";
			execute_query($query);
		}
	}
	// RELACIONAR AGENDAS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
	$query="UPDATE tbl_agenda SET `id_posiblecli`='0',id_cliente='$id_cliente' WHERE `id_posiblecli`='$id_posiblecli'";
	execute_query($query);
	// RELACIONAR PRESUPUESTOS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
	$query="UPDATE tbl_presupuestos SET `id_posiblecli`='0',id_cliente='$id_cliente' WHERE `id_posiblecli`='$id_posiblecli'";
	execute_query($query);
	// RELACIONAR ACTAS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
	$query="UPDATE tbl_actas SET `id_posiblecli`='0',id_cliente='$id_cliente' WHERE `id_posiblecli`='$id_posiblecli'";
	execute_query($query);
	// RELACIONAR FICHEROS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
	$id_aplicacion3=page2id("posiblescli");
	$query="UPDATE tbl_ficheros SET `id_aplicacion`='$id_aplicacion',`id_registro`='$id_cliente' WHERE `id_aplicacion`='$id_aplicacion3' AND `id_registro`='$id_posiblecli'";
	execute_query($query);
	// RELACIONAR COMENTARIOS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
	$query="UPDATE tbl_comentarios SET `id_aplicacion`='$id_aplicacion',`id_registro`='$id_cliente' WHERE `id_aplicacion`='$id_aplicacion3' AND `id_registro`='$id_posiblecli'";
	execute_query($query);
	// MOVER CONTROL DEL REGISTRO DE POSIBLES CLIENTES A CLIENTES
	$query="UPDATE tbl_registros SET `id_aplicacion`='$id_aplicacion',`id_registro`='$id_cliente' WHERE `id_aplicacion`='$id_aplicacion3' AND `id_registro`='$id_posiblecli'";
	execute_query($query);
	// BORRAR EL POSIBLE CLIENTE
	$query="DELETE FROM tbl_posiblescli WHERE `id`='$id_posiblecli'";
	execute_query($query);
	// VOLVER
	session_alert(LANG("clientcreatedok","posiblescli"));
	javascript_history(-1);
	die();
}
?>