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
if(in_array($page,array("contactos","clientes","proveedores","empleados","posiblescli"))) {
	$where="WHERE id='".abs(getParam("id"))."'";
	$id_aplicacion=page2id($page);
	if($page=="contactos") {
		// BUSCAR DATOS CONTACTO
		$query="SELECT * FROM tbl_contactos $where";
		$result=db_query($query);
		$row=db_fetch_row($result);
		db_free($result);
		$nombre=$row["nombre"];
		$nombre1=$row["nombre1"];
		$nombre2=$row["nombre2"];
		$cargo=$row["cargo"];
		$comentarios=$row["comentarios"];
		$id_cliente=($id_aplicacion==page2id("clientes"))?$row["id_cliente"]:0;
		$id_proveedor=($id_aplicacion==page2id("proveedores"))?$row["id_proveedor"]:0;
		$id_empleado=($id_aplicacion==page2id("empleados"))?$row["id_empleado"]:0;
		$id_registro=$row["id"];
		// BUSCAR DATOS CLIENTE O PROVEEDOR
		if($id_cliente) $query="SELECT * FROM tbl_clientes WHERE id='$id_cliente'";
		if($id_proveedor) $query="SELECT * FROM tbl_proveedores WHERE id='$id_proveedor'";
		if($id_empleado) $query="SELECT * FROM tbl_empleados WHERE id='$id_empleado'";
		$result=db_query($query);
		$row=db_fetch_row($result);
		db_free($result);
		$organizacion=$row["nombre"];
	}
	if($page=="clientes" || $page=="proveedores" || $page=="empleados") {
		// BUSCAR DATOS CLIENTE O PROVEEDOR
		if($page=="clientes") $query="SELECT * FROM tbl_clientes $where";
		if($page=="proveedores") $query="SELECT * FROM tbl_proveedores $where";
		if($page=="empleados") $query="SELECT * FROM tbl_empleados $where";
		$result=db_query($query);
		$row=db_fetch_row($result);
		db_free($result);
		$nombre=$row["nombre"];
		$nombre1="";
		$nombre2="";
		$cargo="";
		$comentarios=$row["comentarios"];
		$id_registro=$row["id"];
		$organizacion=$row["nombre"];
	}
	if($page=="contactos" || $page=="clientes" || $page=="proveedores" || $page=="empleados") {
		// BUSCAR DIRECCION PREFERIDA
		$query="SELECT * FROM tbl_direcciones WHERE id_aplicacion='$id_aplicacion' AND id_registro='$id_registro' ORDER BY seleccion DESC,id ASC LIMIT 1";
		$result=db_query($query);
		$row=db_fetch_row($result);
		db_free($result);
		$direccion=$row["direccion"];
		$codpostal=$row["nombre_codpostal"];
		$poblacion=$row["nombre_poblacion"];
		$provincia=$row["nombre_provincia"];
		$pais=$row["nombre_pais"];
		$id_direccion=$row["id"];
		// BUSCAR COMUNICACIONES PREFERIDAS
		$lista=array(
			array(1,"tel_fijo",0),
			array(3,"tel_casa",0),
			array(2,"tel_movil",0),
			array(4,"fax",0),
			array(6,"web",0),
			array(5,"email",0),
			array(5,"email2",1));
		foreach($lista as $key=>$val) {
			$id_tipocom=$val[0];
			$variable=$val[1];
			$offset=$val[2];
			$query="SELECT * FROM tbl_comunicaciones WHERE id_aplicacion='$id_aplicacion' AND id_registro='$id_registro' AND id_direccion='$id_direccion' AND id_tipocom='$id_tipocom' ORDER BY seleccion DESC,id ASC LIMIT $offset,1";
			$result=db_query($query);
			$row=db_fetch_row($result);
			db_free($result);
			$$variable=$row["valor"];
		}
	}
	if($page=="posiblescli") {
		$query="SELECT * FROM tbl_posiblescli $where";
		$result=db_query($query);
		$row=db_fetch_row($result);
		db_free($result);
		$nombre=$row["contacto"];
		$nombre1="";
		$nombre2="";
		$cargo=$row["cargo"];
		$comentarios=$row["comentarios"];
		$organizacion=$row["nombre"];
		$direccion=$row["direccion"];
		$pais=$row["nombre_pais"];
		$provincia=$row["nombre_provincia"];
		$poblacion=$row["nombre_poblacion"];
		$codpostal=$row["nombre_codpostal"];
		$tel_fijo=$row["tel_fijo"];
		$tel_casa="";
		$tel_movil=$row["tel_movil"];
		$fax="";
		$web=$row["web"];
		$email=$row["email"];
		$email2="";
	}
	// CLEAR SOME PARAMETERS
	$badchars=array(" ",".",",",";",":","_","-");
	$tel_fijo=str_replace($badchars,"",$tel_fijo);
	$tel_casa=str_replace($badchars,"",$tel_casa);
	$tel_movil=str_replace($badchars,"",$tel_movil);
	$fax=str_replace($badchars,"",$fax);
	// VCARD
	$revision=date("YmdHis",time());
	$name=encode_bad_chars($nombre).".vcf";
	$buffer="BEGIN:VCARD\r\n";
	$buffer.="VERSION:2.1\r\n";
	if(!defined("__CANCEL_FULL__")) {
		$buffer.="N:$nombre2;$nombre1\r\n";
		$buffer.="FN:$nombre\r\n";
		$buffer.="ORG:$organizacion;$comentarios\r\n";
		$buffer.="TITLE:$cargo\r\n";
		$buffer.="TEL;WORK;VOICE:$tel_fijo\r\n";
		$buffer.="TEL;HOME;VOICE:$tel_casa\r\n";
		$buffer.="TEL;CELL;VOICE:$tel_movil\r\n";
		$buffer.="TEL;WORK;FAX:$fax\r\n";
		$buffer.="TEL;HOME;FAX:\r\n";
		$buffer.="ADR;WORK;ENCODING=QUOTED-PRINTABLE:;;$direccion;$poblacion;$provincia;$codpostal;$pais\r\n";
		$buffer.="LABEL;WORK;ENCODING=QUOTED-PRINTABLE:=0D=0A$direccion=0D=0A$poblacion, $provincia $codpostal=0D=0A$pais\r\n";
		$buffer.="ADR;HOME;ENCODING=QUOTED-PRINTABLE:;;;;;;\r\n";
		$buffer.="LABEL;HOME;ENCODING=QUOTED-PRINTABLE:;=0D=0A,=0D=0A\r\n";
		$buffer.="URL;WORK:$web\r\n";
		$buffer.="EMAIL;PREF;INTERNET:$email\r\n";
		$buffer.="EMAIL;INTERNET:$email2\r\n";
		$buffer.="REV:$revision\r\n";
	} else {
		if($nombre) $buffer.="FN:$nombre\r\n";
		if($direccion) $buffer.="ADR;WORK;ENCODING=QUOTED-PRINTABLE:;;$direccion;$poblacion;$provincia;$codpostal;$pais\r\n";
		if($tel_fijo) $buffer.="TEL;WORK;VOICE:$tel_fijo\r\n";
		if($tel_movil) $buffer.="TEL;CELL;VOICE:$tel_movil\r\n";
	}
	$buffer.="END:VCARD\r\n";
	if(!defined("__CANCEL_HEADER__")) {
		ob_start_protected(getDefault("obhandler"));
		header_powered();
		header_expires(false);
		header("Content-Type: text/x-vcard");
		header("Content-disposition: attachment; filename=\"$name\"");
		echo $buffer;
		$length=ob_get_length();
		header("Content-Length: $length");
		ob_end_flush();
	} else {
		echo $buffer;
	}
	if(!defined("__CANCEL_DIE__")) die();
}
?>