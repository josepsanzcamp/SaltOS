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

if(!check_user()) action_denied();
if(in_array($page,array("contactos","clientes","proveedores","empleados","posiblescli"))) {
	$where="WHERE id='".abs(intval(getParam("id")))."'";
	$id_aplicacion=page2id($page);
	if($page=="contactos") {
		$query="SELECT * FROM tbl_contactos $where";
		$result=db_query($query);
		$row=db_fetch_row($result);
		db_free($result);
		$nombre=$row["nombre"];
		$nombre1=$row["nombre1"];
		$nombre2=$row["nombre2"];
		$cargo=$row["cargo"];
		$comentarios=$row["comentarios"];
		// BUSCAR DATOS CLIENTE O PROVEEDOR
		$id_cliente=($id_aplicacion==page2id("clientes"))?$row["id_cliente"]:0;
		$id_proveedor=($id_aplicacion==page2id("proveedores"))?$row["id_proveedor"]:0;
		$id_empleado=($id_aplicacion==page2id("empleados"))?$row["id_empleado"]:0;
		if($id_cliente) $query="SELECT * FROM tbl_clientes WHERE id='$id_cliente'";
		if($id_proveedor) $query="SELECT * FROM tbl_proveedores WHERE id='$id_proveedor'";
		if($id_empleado) $query="SELECT * FROM tbl_empleados WHERE id='$id_empleado'";
		$result=db_query($query);
		$row2=db_fetch_row($result);
		db_free($result);
		$organizacion=$row2["nombre"];
		// CONTINUAR
		$direccion=$row["direccion"];
		$pais=$row["nombre_pais"];
		$provincia=$row["nombre_provincia"];
		$poblacion=$row["nombre_poblacion"];
		$codpostal=$row["nombre_codpostal"];
		$tel_fijo=$row["tel_fijo"];
		$tel_casa="";
		$tel_movil=$row["tel_movil"];
		$fax=$row["fax"];
		$web=$row["web"];
		$email=$row["email"];
		$email2="";
	}
	if($page=="clientes" || $page=="proveedores" || $page=="empleados") {
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
		$organizacion=$row["nombre"];
		$direccion=$row["direccion"];
		$pais=$row["nombre_pais"];
		$provincia=$row["nombre_provincia"];
		$poblacion=$row["nombre_poblacion"];
		$codpostal=$row["nombre_codpostal"];
		$tel_fijo=$row["tel_fijo"];
		$tel_casa="";
		$tel_movil=$row["tel_movil"];
		$fax=$row["fax"];
		$web=$row["web"];
		$email=$row["email"];
		$email2="";
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
		$fax=$row["fax"];
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
	if(!defined("__CANCEL_DIE__")) {
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
	if(!defined("__CANCEL_DIE__")) {
		output_handler(array(
			"data"=>$buffer,
			"type"=>"text/x-vcard",
			"cache"=>false,
			"name"=>$name
		));
	} else {
		echo $buffer;
	}
}

?>