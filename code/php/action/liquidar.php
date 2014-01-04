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
if($page=="clientes") {
	$id_cliente=abs(getParam("id"));
	$query=array();
	if($id_cliente) $query[]="INSERT INTO tbl_facturas
		SELECT NULL id,'$id_cliente' id_cliente,'0' id_epigrafe,a.nombre,
			direccion,id_pais,id_provincia,id_poblacion,id_codpostal,nombre_pais,nombre_provincia,nombre_poblacion,nombre_codpostal,
			cif,'' num,'".current_date()."' fecha,'0.00' iva,'0.00' irpf,
			'0' cerrado,'0' cobrado,'0' id_cuenta,'".current_date()."' fecha2,'0' id_proyecto,'0' id_periodica,'0' mes_periodica,'' notas,'0' id_formapago,'0' id_presupuesto
		FROM tbl_clientes a
		WHERE a.id='$id_cliente'";
	if(!$id_cliente) $query[]="INSERT INTO tbl_facturas(`id`,`id_cliente`,`id_epigrafe`,`nombre`,
		`direccion`,`id_codpostal`,`id_poblacion`,`id_provincia`,`id_pais`,`nombre_codpostal`,`nombre_poblacion`,`nombre_provincia`,`nombre_pais`,
		`cif`,`num`,`fecha`,`iva`,`irpf`,`cerrado`,`cobrado`,`id_cuenta`,`fecha2`,`id_proyecto`,`id_periodica`,`mes_periodica`,`notas`,`id_formapago`,`id_presupuesto`)
		VALUES(NULL,'$id_cliente','0','','','0','0','0','0','','','','','','','".current_date()."','0.00','0.00','0','0','0','".current_date()."','0','0','0','','0','0')";
	$query[]="INSERT INTO tbl_registros_i(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`)
		VALUES(NULL,'".page2id("facturas")."',(SELECT MAX(id) FROM tbl_facturas),'".current_user()."','".current_datetime()."')";
	$prefix=array();
	foreach(array_merge($_POST,$_GET) as $key=>$val) if(substr($key,0,13)=="prefix_partes") $prefix[]=$val;
	$liquidar=0;
	foreach($prefix as $p) {
		if(getParam($p."liquidar")) {
			$id=intval(getParam($p."id"));
			$parte=sprintf("%05d",$id);
			$fecha=getParam($p."fecha");
			$tarea=get_filtered_field(getParam($p."tarea"));
			$comentarios=getParam($p."comentarios");
			$horas=getParam($p."horas");
			$preciohora=getParam($p."precio");
			$total=getParam($p."total");
			$concepto="$parte ($fecha): $tarea";
			if($comentarios!="") $concepto.="\n$comentarios";
			if(intval($preciohora)!=0) {
				$unidades=$horas;
				$precio=$preciohora;
			} else {
				$unidades=1;
				$precio=$total;
			}
			$query[]="INSERT INTO tbl_facturas_c(id,id_factura,id_producto,concepto,unidades,descuento,precio)
				VALUES(NULL,(SELECT MAX(id) FROM tbl_facturas),'0','$concepto','$unidades','0.00','$precio')";
			$query[]="UPDATE tbl_partes SET liquidado='1',fecha2='".current_date()."' WHERE id='$id'";
			$liquidar=1;
		}
	}
	if($liquidar) foreach($query as $q) db_query($q);
	javascript_history(-1);
	die();
}
if($page=="partes") {
	$ids=check_ids(getParam("id"));
	if($ids) {
		$query2="SELECT DISTINCT id_cliente FROM tbl_partes WHERE id IN ($ids)";
		$result2=db_query($query2);
		while($row2=db_fetch_row($result2)) {
			$id_cliente=$row2["id_cliente"];
			$query=array();
			if($id_cliente) $query[]="INSERT INTO tbl_facturas
				SELECT NULL id,'$id_cliente' id_cliente,'0' id_epigrafe,a.nombre,
					direccion,id_pais,id_provincia,id_poblacion,id_codpostal,nombre_pais,nombre_provincia,nombre_poblacion,nombre_codpostal,
					cif,'' num,'".current_date()."' fecha,'0.00' iva,'0.00' irpf,
					'0' cerrado,'0' cobrado,'0' id_cuenta,'".current_date()."' fecha2,'0' id_proyecto,'0' id_periodica,'0' mes_periodica,'' notas,'0' id_formapago,'0' id_presupuesto
				FROM tbl_clientes a
				WHERE a.id='$id_cliente'";
			if(!$id_cliente) $query[]="INSERT INTO tbl_facturas(`id`,`id_cliente`,`id_epigrafe`,`nombre`,
				`direccion`,`id_codpostal`,`id_poblacion`,`id_provincia`,`id_pais`,`nombre_codpostal`,`nombre_poblacion`,`nombre_provincia`,`nombre_pais`,
				`cif`,`num`,`fecha`,`iva`,`irpf`,`cerrado`,`cobrado`,`id_cuenta`,`fecha2`,`id_proyecto`,`id_periodica`,`mes_periodica`,`notas`,`id_formapago`,`id_presupuesto`)
				VALUES(NULL,'$id_cliente','0','','','0','0','0','0','','','','','','','".current_date()."','0.00','0.00','0','0','0','".current_date()."','0','0','0','','0','0')";
			$query[]="INSERT INTO tbl_registros_i(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`)
				VALUES(NULL,'".page2id("facturas")."',(SELECT MAX(id) FROM tbl_facturas),'".current_user()."','".current_datetime()."')";
			$liquidar=0;
			$query2="SELECT id,usuario,cliente,tarea,comentarios,fecha,horas,precio,total,
						(SELECT nombre FROM tbl_proyectos WHERE id=id_proyecto) proyecto
				FROM (SELECT a.*,
					".make_extra_query_with_login("b.")." usuario,
					c.nombre cliente,e.`datetime`,e.id_usuario id_usuario,b.id_grupo id_grupo
					FROM tbl_partes a
					LEFT JOIN tbl_registros_i e ON e.id_aplicacion='".page2id("partes")."' AND e.id_registro=a.id
					LEFT JOIN tbl_usuarios b ON e.id_usuario=b.id LEFT JOIN tbl_clientes c ON a.id_cliente=c.id) d
				WHERE ROUND(id) IN ($ids) AND id_cliente='$id_cliente' AND liquidado='0'";
			$result=db_query($query2);
			while($row=db_fetch_row($result)) {
				$id=$row["id"];
				$parte=sprintf("%05d",$id);
				$fecha=$row["fecha"];
				$tarea=get_filtered_field($row["tarea"]);
				$comentarios=addslashes($row["comentarios"]);
				$horas=$row["horas"];
				$preciohora=$row["precio"];
				$total=$row["total"];
				$concepto="$parte ($fecha): $tarea";
				if($comentarios!="") $concepto.="\n$comentarios";
				if(intval($preciohora)!=0) {
					$unidades=$horas;
					$precio=$preciohora;
				} else {
					$unidades=1;
					$precio=$total;
				}
				$query[]="INSERT INTO tbl_facturas_c(id,id_factura,id_producto,concepto,unidades,descuento,precio)
					VALUES(NULL,(SELECT MAX(id) FROM tbl_facturas),'0','$concepto','$unidades','0.00','$precio')";
				$query[]="UPDATE tbl_partes SET liquidado='1',fecha2='".current_date()."' WHERE id='$id'";
				$liquidar=1;
			}
			db_free($result);
			if($liquidar) foreach($query as $q) db_query($q);
		}
	} else {
		session_error(LANG("msgerror","partes"));
	}
	javascript_history(-1);
	die();
}
if($page=="gastos") {
	$ids=check_ids(getParam("id"));
	if($ids) {
		$query="SELECT id,total FROM tbl_gastos WHERE ROUND(id) IN ($ids) AND liquidado='0'";
		$result=db_query($query);
		$total=0;
		while($row=db_fetch_row($result)) {
			$id_gasto=intval($row["id"]);
			$total+=floatval($row["total"]);
			$query2="UPDATE tbl_gastos SET liquidado='1',fecha2='".current_date()."' WHERE id='$id_gasto'";
			db_query($query2);
		}
		db_free($result);
		session_alert(LANG("totalliquidar","gastos").$total);
	} else {
		session_error(LANG("msgerror","gastos"));
	}
	javascript_history(-1);
	die();
}
if($page=="proyectos" && getParam("extra")=="partes") {
	$id_proyecto=abs(getParam("id"));
	$id_cliente=execute_query("SELECT id_cliente FROM tbl_proyectos WHERE id='$id_proyecto'");
	$query=array();
	if($id_cliente) $query[]="INSERT INTO tbl_facturas
		SELECT NULL id,'$id_cliente' id_cliente,'0' id_epigrafe,a.nombre,
			direccion,id_pais,id_provincia,id_poblacion,id_codpostal,nombre_pais,nombre_provincia,nombre_poblacion,nombre_codpostal,
			cif,'' num,'".current_date()."' fecha,'0.00' iva,'0.00' irpf,
			'0' cerrado,'0' cobrado,'0' id_cuenta,'".current_date()."' fecha2,'$id_proyecto' id_proyecto,'0' id_periodica,'0' mes_periodica,'' notas,'0' id_formapago,'0' id_presupuesto
		FROM tbl_clientes a
		WHERE a.id='$id_cliente'";
	if(!$id_cliente) $query[]="INSERT INTO tbl_facturas(`id`,`id_cliente`,`id_epigrafe`,`nombre`,
		`direccion`,`id_codpostal`,`id_poblacion`,`id_provincia`,`id_pais`,`nombre_codpostal`,`nombre_poblacion`,`nombre_provincia`,`nombre_pais`,
		`cif`,`num`,`fecha`,`iva`,`irpf`,`cerrado`,`cobrado`,`id_cuenta`,`fecha2`,`id_proyecto`,`id_periodica`,`mes_periodica`,`notas`,`id_formapago`,`id_presupuesto`)
		VALUES(NULL,'$id_cliente','0','','','0','0','0','0','','','','','','','".current_date()."','0.00','0.00','0','0','0','".current_date()."','$id_proyecto','0','0','','0','0')";
	$query[]="INSERT INTO tbl_registros_i(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`)
		VALUES(NULL,'".page2id("facturas")."',(SELECT MAX(id) FROM tbl_facturas),'".current_user()."','".current_datetime()."')";
	$prefix=array();
	foreach(array_merge($_POST,$_GET) as $key=>$val) if(substr($key,0,13)=="prefix_partes") $prefix[]=$val;
	$liquidar=0;
	foreach($prefix as $p) {
		if(getParam($p."liquidar")) {
			$id=intval(getParam($p."id"));
			$parte=sprintf("%05d",$id);
			$fecha=getParam($p."fecha");
			$tarea=get_filtered_field(getParam($p."tarea"));
			$comentarios=getParam($p."comentarios");
			$horas=getParam($p."horas");
			$preciohora=getParam($p."precio");
			$total=getParam($p."total");
			$concepto="$parte ($fecha): $tarea";
			if($comentarios!="") $concepto.="\n$comentarios";
			if(intval($preciohora)!=0) {
				$unidades=$horas;
				$precio=$preciohora;
			} else {
				$unidades=1;
				$precio=$total;
			}
			$query[]="INSERT INTO tbl_facturas_c(id,id_factura,id_producto,concepto,unidades,descuento,precio)
				VALUES(NULL,(SELECT MAX(id) FROM tbl_facturas),'0','$concepto','$unidades','0.00','$precio')";
			$query[]="UPDATE tbl_partes SET liquidado='1',fecha2='".current_date()."' WHERE id='$id'";
			$liquidar=1;
		}
	}
	if($liquidar) foreach($query as $q) db_query($q);
	javascript_history(-1);
	die();
}
if($page=="proyectos" && getParam("extra")=="facturas") {
	$id_proyecto=abs(getParam("id"));
	$id_cliente=execute_query("SELECT id_cliente FROM tbl_proyectos WHERE id='$id_proyecto'");
	$query2="SELECT id FROM tbl_facturas WHERE id_proyecto='$id_proyecto' AND cerrado='0'";
	$id_factura=execute_query($query2);
	if(!$id_factura) {
		$query=array();
		if($id_cliente) $query[]="INSERT INTO tbl_facturas
			SELECT NULL id,'$id_cliente' id_cliente,'0' id_epigrafe,a.nombre,
				direccion,id_pais,id_provincia,id_poblacion,id_codpostal,nombre_pais,nombre_provincia,nombre_poblacion,nombre_codpostal,
				cif,'' num,'".current_date()."' fecha,'0.00' iva,'0.00' irpf,
				'0' cerrado,'0' cobrado,'0' id_cuenta,'".current_date()."' fecha2,'$id_proyecto' id_proyecto,'0' id_periodica,'0' mes_periodica,'' notas,'0' id_formapago,'0' id_presupuesto
			FROM tbl_clientes a
			WHERE a.id='$id_cliente'";
		if(!$id_cliente) $query[]="INSERT INTO tbl_facturas(`id`,`id_cliente`,`id_epigrafe`,`nombre`,
			`direccion`,`id_codpostal`,`id_poblacion`,`id_provincia`,`id_pais`,`nombre_codpostal`,`nombre_poblacion`,`nombre_provincia`,`nombre_pais`,
			`cif`,`num`,`fecha`,`iva`,`irpf`,`cerrado`,`cobrado`,`id_cuenta`,`fecha2`,`id_proyecto`,`id_periodica`,`mes_periodica`,`notas`,`id_formapago`,`id_presupuesto`)
			VALUES(NULL,'$id_cliente','0','','','0','0','0','0','','','','','','','".current_date()."','0.00','0.00','0','0','0','".current_date()."','$id_proyecto','0','0','','0','0')";
		$query[]="INSERT INTO tbl_registros_i(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`)
			VALUES(NULL,'".page2id("facturas")."',(SELECT MAX(id) FROM tbl_facturas),'".current_user()."','".current_datetime()."')";
		// GET THE TASKS
		$prefix=array();
		foreach(array_merge($_POST,$_GET) as $key=>$val) if(substr($key,0,17)=="prefix_tareas_old") $prefix[]=$val;
		$ids=array();
		foreach($prefix as $p) if(getParam($p."liquidar")) $ids[]=intval(getParam($p."id"));
		$ids_tareas=count($ids)?implode(",",$ids):0;
		// GET THE PRODUCTS
		$prefix=array();
		foreach(array_merge($_POST,$_GET) as $key=>$val) if(substr($key,0,20)=="prefix_productos_old") $prefix[]=$val;
		$ids=array();
		foreach($prefix as $p) if(getParam($p."liquidar")) $ids[]=intval(getParam($p."id"));
		$ids_productos=count($ids)?implode(",",$ids):0;
		// CHECK WHEN USER DONT SELECT NOTHING!!!
		if($ids_tareas=="0" && $ids_productos=="0") {
			$query2="SELECT id FROM tbl_proyectos_t WHERE id_proyecto='${id_proyecto}'";
			$ids=execute_query_array($query2);
			$ids_tareas=count($ids)?implode(",",$ids):0;
			$query2="SELECT id FROM tbl_proyectos_p WHERE id_proyecto='${id_proyecto}'";
			$ids=execute_query_array($query2);
			$ids_productos=count($ids)?implode(",",$ids):0;
		}
		// CONTINUE WITH NORMAL OPERATION
		$liquidar=0;
		$query2="SELECT id_producto,concepto,unidades,descuento,precio
					FROM tbl_proyectos_p
					WHERE id_proyecto='$id_proyecto' and id IN ($ids_productos)
				UNION
				SELECT '0' id_producto,tarea concepto,horas unidades,descuento,precio
					FROM tbl_proyectos_t
					WHERE id_proyecto='$id_proyecto' and id IN ($ids_tareas)";
		$result=execute_query_array($query2);
		foreach($result as $row) {
			$id_producto=$row["id_producto"];
			$concepto=addslashes($row["concepto"]);
			$unidades=$row["unidades"];
			$descuento=$row["descuento"];
			$precio=$row["precio"];
			$query[]="INSERT INTO tbl_facturas_c(id,id_factura,id_producto,concepto,unidades,descuento,precio)
				VALUES(NULL,(SELECT MAX(id) FROM tbl_facturas),'$id_producto','$concepto','$unidades','$descuento','$precio')";
			$liquidar=1;
		}
		if($liquidar) foreach($query as $q) db_query($q);
		session_alert(LANG("okfacturar","proyectos"));
	} else {
		session_error(LANG("errorfacturar","proyectos"));
	}
	javascript_history(-1);
	die();
}
if($page=="periodicas") {
	$ids=check_ids(getParam("id"));
	$meses=getParam("meses");
	if($ids && $meses) {
		$query2="SELECT *,(SELECT nombre FROM tbl_clientes WHERE id_cliente=tbl_clientes.id) cliente FROM tbl_periodicas WHERE id IN ($ids) ORDER BY id ASC";
		$result=db_query($query2);
		$total=0;
		while($row=db_fetch_row($result)) {
			$id_cliente=$row["id_cliente"];
			$id_epigrafe=$row["id_epigrafe"];
			$id_cuenta=$row["id_cuenta"];
			$id_formapago=$row["id_formapago"];
			$id_periodica=$row["id"];
			$iva=$row["iva"];
			$irpf=$row["irpf"];
			$query=array();
			if($id_cliente) $query[]="INSERT INTO tbl_facturas
				SELECT NULL id,'$id_cliente' id_cliente,'$id_epigrafe' id_epigrafe,a.nombre,
					direccion,id_pais,id_provincia,id_poblacion,id_codpostal,nombre_pais,nombre_provincia,nombre_poblacion,nombre_codpostal,
					cif,'' num,'".current_date()."' fecha,'$iva' iva,'$irpf' irpf,
					'0' cerrado,'0' cobrado,'$id_cuenta' id_cuenta,'".current_date()."' fecha2,
					'0' id_proyecto,'$id_periodica' id_periodica,'$meses' mes_periodica,'' notas,'$id_formapago' id_formapago,'0' id_presupuesto
				FROM tbl_clientes a
				WHERE a.id='$id_cliente'";
			if(!$id_cliente) $query[]="INSERT INTO tbl_facturas(`id`,`id_cliente`,`id_epigrafe`,`nombre`,
				`direccion`,`id_codpostal`,`id_poblacion`,`id_provincia`,`id_pais`,`nombre_codpostal`,`nombre_poblacion`,`nombre_provincia`,`nombre_pais`,
				`cif`,`num`,`fecha`,`iva`,`irpf`,`cerrado`,`cobrado`,`id_cuenta`,`fecha2`,`id_proyecto`,`id_periodica`,`mes_periodica`,`notas`,`id_formapago`,`id_presupuesto`)
				VALUES(NULL,'$id_cliente','$id_epigrafe','','','0','0','0','0','','','','','','','".current_date()."','$iva','$irpf',
					'0','0','$id_cuenta','".current_date()."','0','$id_periodica','$meses','','$id_formapago','0')";
			$query[]="INSERT INTO tbl_registros_i(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`)
				VALUES(NULL,'".page2id("facturas")."',(SELECT MAX(id) FROM tbl_facturas),'".current_user()."','".current_datetime()."')";
			$hacer=0;
			$query2="SELECT * FROM tbl_periodicas_c WHERE id_periodica='$id_periodica' ORDER BY id ASC";
			$result=db_query($query2);
			while($row=db_fetch_row($result)) {
				$id_concepto=$row["id"];
				$query[]="INSERT INTO tbl_facturas_c
					SELECT NULL id,(SELECT MAX(id) FROM tbl_facturas) id_factura,id_producto,concepto,unidades,descuento,precio
					FROM tbl_periodicas_c WHERE id='$id_concepto'";
				$hacer=1;
			}
			db_free($result);
			$query2="SELECT *
				FROM tbl_facturas
				WHERE id_periodica='$id_periodica' AND mes_periodica='$meses' AND SUBSTR(fecha,1,7)='".substr(current_date(),0,7)."'";
			$result=db_query($query2);
			if(db_num_rows($result)!=0) $hacer=0;
			db_free($result);
			if($hacer) {
				$total++;
				foreach($query as $q) db_query($q);
			}
		}
		db_free($result);
		session_alert(LANG("totalliquidar","periodicas").$total);
	} else {
		session_error(LANG("msgerror","periodicas"));
	}
	javascript_history(-1);
	die();
}
if($page=="presupuestos") {
	$id_presupuesto=abs(getParam("id"));
	$id_cliente=execute_query("SELECT id_cliente FROM tbl_presupuestos WHERE id='$id_presupuesto'");
	$query2="SELECT id FROM tbl_facturas WHERE id_presupuesto='$id_presupuesto' AND cerrado='0'";
	$id_factura=execute_query($query2);
	if(!$id_factura) {
		$query=array();
		if($id_cliente) $query[]="INSERT INTO tbl_facturas
			SELECT NULL id,'$id_cliente' id_cliente,'0' id_epigrafe,a.nombre,
				direccion,id_pais,id_provincia,id_poblacion,id_codpostal,nombre_pais,nombre_provincia,nombre_poblacion,nombre_codpostal,
				cif,'' num,'".current_date()."' fecha,'0.00' iva,'0.00' irpf,
				'0' cerrado,'0' cobrado,'0' id_cuenta,'".current_date()."' fecha2,'0' id_proyecto,'0' id_periodica,'0' mes_periodica,'' notas,'0' id_formapago,'$id_presupuesto' id_presupuesto
			FROM tbl_clientes a
			WHERE a.id='$id_cliente'";
		if(!$id_cliente) $query[]="INSERT INTO tbl_facturas(`id`,`id_cliente`,`id_epigrafe`,`nombre`,
			`direccion`,`id_codpostal`,`id_poblacion`,`id_provincia`,`id_pais`,`nombre_codpostal`,`nombre_poblacion`,`nombre_provincia`,`nombre_pais`,
			`cif`,`num`,`fecha`,`iva`,`irpf`,`cerrado`,`cobrado`,`id_cuenta`,`fecha2`,`id_proyecto`,`id_periodica`,`mes_periodica`,`notas`,`id_formapago`,`id_presupuesto`)
			VALUES(NULL,'$id_cliente','0','','','0','0','0','0','','','','','','','".current_date()."','0.00','0.00','0','0','0','".current_date()."','0','0','0','','0','$id_presupuesto')";
		$query[]="INSERT INTO tbl_registros_i(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`)
			VALUES(NULL,'".page2id("facturas")."',(SELECT MAX(id) FROM tbl_facturas),'".current_user()."','".current_datetime()."')";
		// GET THE TASKS
		$prefix=array();
		foreach(array_merge($_POST,$_GET) as $key=>$val) if(substr($key,0,17)=="prefix_tareas_old") $prefix[]=$val;
		$ids=array();
		foreach($prefix as $p) if(getParam($p."liquidar")) $ids[]=intval(getParam($p."id"));
		$ids_tareas=count($ids)?implode(",",$ids):0;
		// GET THE PRODUCTS
		$prefix=array();
		foreach(array_merge($_POST,$_GET) as $key=>$val) if(substr($key,0,20)=="prefix_productos_old") $prefix[]=$val;
		$ids=array();
		foreach($prefix as $p) if(getParam($p."liquidar")) $ids[]=intval(getParam($p."id"));
		$ids_productos=count($ids)?implode(",",$ids):0;
		// CHECK WHEN USER DONT SELECT NOTHING!!!
		if($ids_tareas=="0" && $ids_productos=="0") {
			$query2="SELECT id FROM tbl_presupuestos_t WHERE id_presupuesto='${id_presupuesto}'";
			$ids=execute_query_array($query2);
			$ids_tareas=count($ids)?implode(",",$ids):0;
			$query2="SELECT id FROM tbl_presupuestos_p WHERE id_presupuesto='${id_presupuesto}'";
			$ids=execute_query_array($query2);
			$ids_productos=count($ids)?implode(",",$ids):0;
		}
		// CONTINUE WITH NORMAL OPERATION
		$liquidar=0;
		$query2="SELECT id_producto,concepto,unidades,descuento,precio
					FROM tbl_presupuestos_p
					WHERE id_presupuesto='$id_presupuesto' and id IN ($ids_productos)
				UNION
				SELECT '0' id_producto,tarea concepto,horas unidades,descuento,precio
					FROM tbl_presupuestos_t
					WHERE id_presupuesto='$id_presupuesto' and id IN ($ids_tareas)";
		$result=execute_query_array($query2);
		foreach($result as $row) {
			$id_producto=$row["id_producto"];
			$concepto=addslashes($row["concepto"]);
			$unidades=$row["unidades"];
			$descuento=$row["descuento"];
			$precio=$row["precio"];
			$query[]="INSERT INTO tbl_facturas_c(id,id_factura,id_producto,concepto,unidades,descuento,precio)
				VALUES(NULL,(SELECT MAX(id) FROM tbl_facturas),'$id_producto','$concepto','$unidades','$descuento','$precio')";
			$liquidar=1;
		}
		if($liquidar) foreach($query as $q) db_query($q);
		session_alert(LANG("okfacturar","presupuestos"));
	} else {
		session_error(LANG("errorfacturar","presupuestos"));
	}
	javascript_history(-1);
	die();
}
?>