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
if($page=="incidencias") {
	include_once("php/report.php");
	include_once("php/sendmail.php");
	// FUNCIONES
	function __incidencias_packreport($campos,$tipos,$row) {
		$body="";
		$count=count($campos);
		for($i=0;$i<$count;$i++) {
			$campo=$campos[$i];
			$tipo=$tipos[$i];
			$label=LANG($campo);
			$value=$row[$campo];
			switch($tipo) {
				case "text": $body.=__report_text($label,$value); break;
				case "textarea": $body.=__report_textarea($label,$value); break;
			}
		}
		return $body;
	}
	// DATOS SMPT
	$host=CONFIG("email_host");
	$port=CONFIG("email_port");
	$extra=CONFIG("email_extra");
	if($port || $extra) $host.=":$port:$extra";
	$user=CONFIG("email_user");
	$pass=CONFIG("email_pass");
	if(!$host || !$user || !$pass) {
		session_error(LANG("msgnotsmtpemail"));
		javascript_history(-1);
		die();
	}
	// DATOS EMAIL
	$from=CONFIG("email_name")." <".CONFIG("email_from").">";
	$id_incidencia=intval(($action=="insert")?execute_query("SELECT MAX(id) FROM tbl_incidencias"):getParam("id"));
	$query="SELECT y5.valor mailto,".make_extra_query_with_login("d.")." usuario
			FROM tbl_incidencias_u a
			LEFT JOIN tbl_usuarios d ON a.id_usuario=d.id
			LEFT JOIN tbl_direcciones x
				ON x.id=(
					SELECT id
					FROM tbl_direcciones
					WHERE id_aplicacion=d.id_aplicacion AND id_registro=d.id_registro
					ORDER BY seleccion DESC, id ASC LIMIT 1
				)
			LEFT JOIN tbl_comunicaciones y5
				ON y5.id=(
					SELECT id
					FROM tbl_comunicaciones
					WHERE id_direccion=x.id AND id_tipocom=(
						SELECT id
						FROM tbl_tiposcom
						WHERE codigo='email'
					)
					ORDER BY seleccion DESC, id ASC LIMIT 1
				)
			WHERE a.id_incidencia='$id_incidencia'";
	$result2=db_query($query);
	if(db_num_rows($result2)) {
		$to=array();
		while($row2=db_fetch_row($result2)) {
			$to[]=$row2["usuario"]." <".$row2["mailto"].">";
		}
		db_free($result2);
		$subject=($action=="insert")?LANG_ESCAPE("forminsert"):LANG_ESCAPE("formupdate");
		$body=__report_begin($subject);
		$files=array();
		// DATOS GENERALES
		$id_aplicacion=page2id($page);
		// DATOS INCIDENCIA
		$query="SELECT a.nombre nombre,
				a.descripcion descripcion,
				CASE a.id_cliente WHEN '0' THEN '".LANG_ESCAPE("sincliente")."' ELSE b.nombre END cliente,
				CASE id_proyecto WHEN '0' THEN '".LANG_ESCAPE("sinproyecto")."' ELSE c.nombre END proyecto,
				d.nombre estado,
				".make_extra_query_with_login("f.")." username,
				e.`datetime` `datetime`,
				g.nombre prioridad
			FROM tbl_incidencias a
			LEFT JOIN tbl_clientes b ON a.id_cliente=b.id
			LEFT JOIN tbl_proyectos c ON a.id_proyecto=c.id
			LEFT JOIN tbl_estados d ON a.id_estado=d.id
			LEFT JOIN tbl_registros_i e ON e.id_aplicacion='$id_aplicacion' AND e.id_registro=a.id
			LEFT JOIN tbl_usuarios f ON e.id_usuario=f.id
			LEFT JOIN tbl_prioridades g ON g.id=a.id_prioridad
			WHERE a.id='$id_incidencia'";
		$result2=db_query($query);
		if(db_num_rows($result2)) {
			$row2=db_fetch_row($result2);
			db_free($result2);
			// MEJORAR SUBJECT REAL
			$subject.=": ".$row2["nombre"];
			// BODY INCIDENCIAS
			$campos=array("username","datetime","cliente","proyecto","nombre","estado","prioridad","descripcion");
			$tipos=array("text","text","text","text","text","text","text","textarea");
			$body.=__incidencias_packreport($campos,$tipos,$row2);
			// DATOS COMENTARIOS
			$query="SELECT comentarios,`datetime`,
					".make_extra_query_with_login("b.")." username
				FROM tbl_comentarios a
				LEFT JOIN tbl_usuarios b ON a.id_usuario=b.id
				WHERE a.id_aplicacion='$id_aplicacion' AND a.id_registro='$id_incidencia'";
			$result2=db_query($query);
			// BODY COMENTARIOS
			$campos=array("username","datetime","comentarios");
			$tipos=array("text","text","textarea");
			while($row2=db_fetch_row($result2)) {
				$body.=__incidencias_packreport($campos,$tipos,$row2);
			}
			db_free($result2);
			// DATOS USUARIOS
			$query="SELECT
					GROUP_CONCAT(".make_extra_query_with_login("b.")." /*MYSQL SEPARATOR '; ' *//*SQLITE ,'; '*/) usersdata
				FROM tbl_incidencias_u a
				LEFT JOIN tbl_usuarios b ON a.id_usuario=b.id
				WHERE id_incidencia='$id_incidencia'";
			$result2=db_query($query);
			// BODY USUARIOS
			$campos=array("usersdata");
			$tipos=array("text");
			while($row2=db_fetch_row($result2)) {
				$body.=__incidencias_packreport($campos,$tipos,$row2);
			}
			db_free($result2);
			// CERRAR BODY
			$temp=LANG("view")." ".LANG("incidencia");
			$body.=__report_link($temp,get_base()."?page=incidencias&action=form&id=-$id_incidencia",$temp);
			$temp=LANG("edit")." ".LANG("incidencia");
			$body.=__report_link($temp,get_base()."?page=incidencias&action=form&id=$id_incidencia",$temp);
			$body.=__report_end();
			// PARA DEBUGAR
			//~ echo "<pre>SUBJECT=$subject BODY=$body TO=".sprintr($to)."</pre>"; die();
			// ENVIAR EMAIL
			$error=sendmail($from,$to,$subject,$body,$files,$host,$user,$pass);
			if($error) {
				session_error($error);
			} else {
				session_alert(LANG("msgsendemail"));
			}
		}
	}
}
?>