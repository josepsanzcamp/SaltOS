<?php
declare(ticks=1000);
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2015 by Josep Sanz Campderrós
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
if(in_array($page,array("facturas","actas","partes","presupuestos"))) {
	require_once("php/getmail.php");
	// DATOS FACTURA/ACTA/PARTE/PRESUPUESTO
	$where="WHERE id IN (".check_ids(getParam("id")).")";
	if($page=="facturas") $query="
		SELECT
			CASE num WHEN '' THEN
				CONCAT('".LANG("albaran")."',' ',LPAD(id,".intval(CONFIG("zero_padding_digits")).",0),' ',nombre)
			ELSE
				CONCAT('".LANG("factura")."',' ',num,' ',nombre)
			END subject,id
		FROM tbl_facturas $where";
	if($page=="actas") $query="
		SELECT
			CONCAT('".LANG("acta")."',' ',LPAD(id,".intval(CONFIG("zero_padding_digits")).",0),' ',nombre) subject,id
		FROM tbl_actas $where";
	if($page=="partes") $query="
		SELECT
			CONCAT('".LANG("parte")."',' ',LPAD(id,".intval(CONFIG("zero_padding_digits")).",0),' ',tarea) subject,id
		FROM tbl_partes $where";
	if($page=="presupuestos") $query="
		SELECT
			CONCAT('".LANG("presupuesto")."',' ',LPAD(id,".intval(CONFIG("zero_padding_digits")).",0),' ',nombre) subject,id
		FROM tbl_presupuestos $where";
	$result=db_query($query);
	$numrows=db_num_rows($result);
	if(!$numrows) action_denied();
	$ids=array();
	$body=array();
	while($row=db_fetch_row($result)) {
		$ids[]=$row["id"];
		$body[]=$row["subject"];
	}
	if($numrows==1) $subject=$body[0];
	if($numrows!=1) $subject=LANG($page,"menu");
	db_free($result);
	if($numrows!=1) array_unshift($ids,implode(",",$ids));
	if($numrows!=1) array_unshift($body,LANG($page,"menu"));
	$files=array();
	foreach($ids as $key=>$val) {
		// PDF FACTURA/ACTA/PARTE/PRESUPUESTO
		$action="pdf";
		setParam("action",$action);
		$_GET["id"]=$val;
		ob_start();
		if(!defined("__CANCEL_DIE__")) define("__CANCEL_DIE__",1);
		include("php/action/pdf.php");
		$pdf=ob_get_clean();
		$file=get_temp_file(getDefault("exts/pdfext",".pdf"));
		file_put_contents($file,$pdf);
		$name=$body[$key].".pdf";
		$name=encode_bad_chars_file($name);
		$mime="application/pdf";
		$size=strlen($pdf);
		$files["pdf_${key}"]=array("file"=>$file,"name"=>$name,"mime"=>$mime,"size"=>$size);
	}
	$body=implode("<br/>",$body);
	//require_once("php/getmail.php");
	//$body=__HTML_TEXT_OPEN__.$body.__HTML_TEXT_CLOSE__;
	sess_init();
	$_SESSION["correo"]=array("subject"=>$subject,"body"=>$body,"files"=>$files);
	sess_close();
	// REBOTAR AL FORMULARIO DE REDACCION
	javascript_location_page("correo&action=form&id=0_session_0");
	die();
}
?>