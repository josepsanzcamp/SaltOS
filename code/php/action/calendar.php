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
if(getParam("action")=="calendar") {
	// PREPARAR COSAS COMUNES
	$_RESULT=array("options"=>array(),"rows"=>array());
	$cells=array("a","b","c","d","e","f","g");
	$offset=intval(getParam("offset"));
	// APLICAR OFFSET DE MESES
	$delta=0;
	$yesterday=current_date(86400*($delta-1));
	$today=current_date(86400*$delta);
	$tomorrow=current_date(86400*($delta+1));
	$unix=strtotime($today);
	$month=intval(date("m",$unix));
	$year=intval(date("Y",$unix));
	$month+=$offset;
	while($month>12) {
		$month-=12;
		$year++;
	}
	while($month<1) {
		$month+=12;
		$year--;
	}
	// SIMULAR EL LISTADO PARA OBTENER LOS IDS
	require_once("php/listsim.php");
	$ids=list_simulator("agenda");
	// MONTAR SELECT CON MESES Y AÑOS
	$meses=array("",LANG("enero"),LANG("febrero"),LANG("marzo"),
				LANG("abril"),LANG("mayo"),LANG("junio"),
				LANG("julio"),LANG("agosto"),LANG("setiembre"),
				LANG("octubre"),LANG("noviembre"),LANG("diciembre"));
	$query="SELECT COUNT(*) `count`,MIN(dstart) `min`,MAX(dstop) `max` FROM tbl_agenda WHERE id IN ($ids)";
	$result=execute_query($query);
	if($result["count"]) {
		$result["min"]=explode("-",$result["min"]);
		$result["max"]=explode("-",$result["max"]);
		$valmin=$result["min"][0]*12+$result["min"][1];
		$valmax=$result["max"][0]*12+$result["max"][1];
	} else {
		$unix=strtotime($today);
		$imonth=intval(date("m",$unix));
		$iyear=intval(date("Y",$unix));
		$valmin=$iyear*12+$imonth;
		$valmax=$iyear*12+$imonth;
	}
	$valcur=$year*12+$month;
	$unix=strtotime($today);
	$imonth=intval(date("m",$unix));
	$iyear=intval(date("Y",$unix));
	$valhoy=$iyear*12+$imonth;
	if($valcur-12<$valmin) $valmin=$valcur-12;
	if($valcur+12>$valmax) $valmax=$valcur+12;
	if($valhoy-12<$valmin) $valmin=$valhoy-12;
	if($valhoy+12>$valmax) $valmax=$valhoy+12;
	for($i=$valmin;$i<=$valmax;$i++) {
		$imonth=(($imonth=$i%12)==0)?12:$imonth;
		$iyear=intval(($i-1)/12);
		$value=$i-$valhoy;
		$selected=($i==$valcur)?1:0;
		set_array($_RESULT["options"],"option",array("label"=>$meses[$imonth]." ".$iyear,"value"=>$value,"selected"=>$selected));
	}
	// PREPARAR FILTRO PARA QUERY
	$dstart=strtotime("$year-$month-01");
	$dow=date("N",$dstart)-1;
	$days=date("t",$dstart);
	$dstart=$dstart-$dow*86400;
	$dstop=$dstart+86400*7*6-1;
	// CALCULAR SEMANAS DEL AÑO
	$woymax=max(date("W",$dstart),date("W",strtotime("$year-12-28")));
	// MONTAR CALENDARIO INICIAL
	$woy=date("W",$dstart);
	$unix=$dstart;
	$orig=array("/01/","/02/","/03/","/04/","/05/","/06/","/07/","/08/","/09/","/10/","/11/","/12/");
	$dest=array(" ".mb_substr(LANG("enero"),0,3)." "," ".mb_substr(LANG("febrero"),0,3)." "," ".mb_substr(LANG("marzo"),0,3)." ",
				" ".mb_substr(LANG("abril"),0,3)." "," ".mb_substr(LANG("mayo"),0,3)." "," ".mb_substr(LANG("junio"),0,3)." ",
				" ".mb_substr(LANG("julio"),0,3)." "," ".mb_substr(LANG("agosto"),0,3)." "," ".mb_substr(LANG("setiembre"),0,3)." ",
				" ".mb_substr(LANG("octubre"),0,3)." "," ".mb_substr(LANG("noviembre"),0,3)." "," ".mb_substr(LANG("diciembre"),0,3)." ");
	$dias=array("",mb_substr(LANG("lunes"),0,3),mb_substr(LANG("martes"),0,3),mb_substr(LANG("miercoles"),0,3),
				mb_substr(LANG("jueves"),0,3),mb_substr(LANG("viernes"),0,3),mb_substr(LANG("sabado"),0,3),mb_substr(LANG("domingo"),0,3));
	while($unix<=$dstop) {
		$current=date("Y-m-d",$unix);
		$month2=date("m",$unix);
		$letter=$cells[date("N",$unix)-1];
		$number=date("W",$unix)-$woy+1;
		if($number<1) $number+=$woymax;
		$cell=$letter.$number;
		$data=$dias[date("N",$unix)]." ".str_replace($orig,$dest,date("d/m/Y",$unix));
		if($current==$today) $data=LANG("today","agenda").", ".$data;
		if($current==$yesterday) $data=LANG("yesterday","agenda").", ".$data;
		if($current==$tomorrow) $data=LANG("tomorrow","agenda").", ".$data;
		$class="siwrap right bold italic";
		if($month2!=$month) $class.=" ui-state-disabled";
		if($letter=="f") $class.=" ui-state-disabled";
		if($letter=="g") $class.=" ui-state-disabled";
		$style="";
		set_array($_RESULT["rows"],"row",array("cell"=>$cell,"data"=>$data,"class"=>$class,"style"=>$style));
		$correccion=(date("I",$unix)-date("I",$unix+86400))*3600;
		$unix+=86400+$correccion;
	}
	// BUSCAR EVENTOS EN LA AGENDA
	$query="SELECT a.*,
				CASE a.id_estado WHEN '0' THEN '".LANG("sinestado")."' ELSE c.nombre END estado,
				c.activo activo,
				CASE f.id_usuario WHEN '".current_user()."' THEN 1 ELSE 0 END propietario,
				".make_extra_query_with_login("d.")." usuario
			FROM tbl_agenda a
			LEFT JOIN tbl_estados c ON a.id_estado=c.id
			LEFT JOIN tbl_registros_i f ON f.id_aplicacion='".page2id("agenda")."' AND f.id_registro=a.id
			LEFT JOIN tbl_usuarios d ON f.id_usuario=d.id
			WHERE a.id IN ($ids)
				AND UNIX_TIMESTAMP(dstart)>=$dstart
				AND UNIX_TIMESTAMP(dstart)<=$dstop
			ORDER BY dstart ASC, a.id ASC";
	$result=db_query($query);
	$current=current_datetime();
	while($row=db_fetch_row($result)) {
		$id=$row["id"];
		$subclass="";
		if($row["activo"] && $row["dstop"]<=$current) $subclass.=" red";
		if($row["activo"] && $row["dstop"]>$current) $subclass.=" blue";
		if(!$row["activo"]) $subclass.=" green";
		$fechaini=substr($row["dstart"],0,10);
		$fechafin=substr($row["dstop"],0,10);
		$horaini=substr($row["dstart"],11,5);
		$horafin=substr($row["dstop"],11,5);
		$estado=$row["estado"];
		$nombre=str_replace("'","",$row["nombre"]);
		$lugar=str_replace("'","",$row["lugar"]);
		$propietario=$row["propietario"];
		$usuario=str_replace("'","",$row["usuario"]);
		$nombre2=intelligence_cut($nombre,25);
		$lugar2=intelligence_cut($lugar,25);
		$usuario2=intelligence_cut(LANG("by","agenda")." ".$usuario,25);
		$urlview="opencontent(\"?page=agenda&action=form&id=-$id\")";
		$urledit="opencontent(\"?page=agenda&action=form&id=$id\")";
		$urlcopy="opencontent(\"?page=agenda&action=form&id=0_copy_$id\")";
		$class="siwrap ui-state-highlight ui-corner-all normal";
		$style="overflow:hidden;height:1.2em";
		$unixini=strtotime($fechaini);
		$unixfin=strtotime($fechafin);
		$maxiter=0;
		$class2="float";
		while($unixini+$maxiter*86400<=$unixfin) {
			$current2=date("Y-m-d",$unixini+$maxiter*86400);
			if($current2==$yesterday || $current2==$today || $current2==$tomorrow) $class2.=" opened";
			$maxiter++;
		}
		$curiter=0;
		while($unixini+$curiter*86400<=$unixfin) {
			$letter=$cells[date("N",$unixini+$curiter*86400)-1];
			$number=date("W",$unixini+$curiter*86400)-$woy+1;
			if($number<1) $number+=$woymax;
			$cell=$letter.$number;
			if($fechaini==$fechafin) {
				$horario=$horaini."-".$horafin;
				if($horaini==$horafin) $horario=$horaini;
			} else {
				if($curiter==0) $horario=$horaini;
				elseif($curiter<$maxiter-1) $horario=LANG("continue","agenda");
				else $horario=$horafin;
			}
			$data="<a href='javascript:void(0)' onclick='openclose_calendar(this)' style='text-decoration:none'>";
			$data.="<span class='$class2'>[+]</span>";
			$data.=$horario;
			if(!$propietario && $usuario) $data.=" <span title='$usuario'>$usuario2</span>";
			$data.=" <span class='$subclass'>($estado)</span>";
			$data.=", <span title='$nombre'>$nombre2</span>";
			if($lugar) $data.=", <span title='$lugar'>$lugar2</span>";
			$data.="</a>";
			$data.=" [<a href='javascript:void(0)' style='text-decoration:none' onclick='$urlview'>".LANG("view")."</a>]";
			$data.=" [<a href='javascript:void(0)' style='text-decoration:none' onclick='$urledit'>".LANG("edit")."</a>]";
			$data.=" [<a href='javascript:void(0)' style='text-decoration:none' onclick='$urlcopy'>".LANG("copy")."</a>]";
			set_array($_RESULT["rows"],"row",array("cell"=>$cell,"data"=>$data,"class"=>$class,"style"=>$style));
			$curiter++;
		}
	}
	db_free($result);
	// PONER ENLACE CREAR EVENTO
	$create=LANG("create","agenda");
	$unix=$dstart;
	while($unix<=$dstop) {
		$current=date("Y-m-d",$unix);
		$letter=$cells[date("N",$unix)-1];
		$number=date("W",$unix)-$woy+1;
		if($number<1) $number+=$woymax;
		$cell=$letter.$number;
		$data="<a href='javascript:void(0)' title=\"$create\" onclick='opencontent(\"?page=agenda&action=form&id=0&dstart=$current+10:00:00&dstop=$current+10:00:00\")'>$create</a>";
		$class="siwrap right italic";
		$style="";
		set_array($_RESULT["rows"],"row",array("cell"=>$cell,"data"=>$data,"class"=>$class,"style"=>$style));
		$correccion=(date("I",$unix)-date("I",$unix+86400))*3600;
		$unix+=86400+$correccion;
	}
	// ENVIAR XML DE SALIDA
	$buffer=__XML_HEADER__;
	$buffer.=array2xml($_RESULT);
	output_buffer($buffer,"text/xml");
}
?>