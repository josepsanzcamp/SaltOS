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
function __import_find_chars($data,$pos,$chars) {
	$result=array();
	$len=strlen($chars);
	for($i=0;$i<$len;$i++) {
		$temp=strpos($data,$chars[$i],$pos);
		if($temp!==false) $result[]=$temp;
	}
	return count($result)?min($result):false;
}

function __import_find_query($data,$pos) {
	$len=strlen($data);
	$parentesis=0;
	$parser=1;
	$exists=0;
	$pos2=__import_find_chars($data,$pos,"\\'();");
	while($pos2!==false) {
		if($data[$pos2]=="\\") $pos2++;
		elseif($data[$pos2]=="'") $parser=!$parser;
		elseif($data[$pos2]=="(" && $parser) $parentesis++;
		elseif($data[$pos2]==")" && $parser) $parentesis--;
		elseif($data[$pos2]==";" && $parser && !$parentesis) { $exists=1; break; }
		if($pos2+1>=$len) break;
		$pos2=__import_find_chars($data,$pos2+1,"\\'();");
	}
	if(!$parser || $parentesis || !$exists) return 0;
	return $pos2-$pos;
}

function __import_importfile($id_importacion,$nodes=null) {
	$query="SELECT * FROM tbl_ficheros WHERE id_aplicacion='".page2id("importaciones")."' AND id_registro='".abs($id_importacion)."'";
	$row=execute_query($query);
	$file=get_directory("dirs/filesdir").$row["fichero_file"];
	$type=$row["fichero_type"];
	switch($type) {
		case "application/xml":
			$array=__import_xml2array($file);
			break;
		case "text/plain":
		case "text/csv":
			$array=__import_csv2array($file,$nodes);
			break;
		case "application/vnd.ms-excel":
		case "application/excel":
			$array=__import_xls2array($file,$nodes);
			break;
		default:
			show_php_error(array("phperror"=>"Unknown type '${type}' for file '${file}' (id_importacion='${id_importacion}')"));
	}
	return $array;
}

function __import_xml2array($file) {
	$xml=file_get_contents($file);
	$data=xml2struct($xml);
	$data=array_reverse($data);
	$array=__import_struct2array($data);
	return $array;
}

function __import_struct2array(&$data) {
	$array=array();
	while($linea=array_pop($data)) {
		$name=$linea["tag"];
		$type=$linea["type"];
		$value="";
		if(isset($linea["value"])) $value=$linea["value"];
		$attr=array();
		if(isset($linea["attributes"])) $attr=$linea["attributes"];
		if($type=="open") {
			// caso 1 <algo>
			$value=__import_struct2array($data);
			if(count($attr)) $value=array("value"=>$value,"#attr"=>$attr);
			set_array($array,$name,$value);
		} elseif($type=="close") {
			// caso 2 </algo>
			return $array;
		} elseif($type=="complete") {
			if($value=="") {
				// caso 3 <algo/>
				if(count($attr)) $value=array("value"=>$value,"#attr"=>$attr);
				set_array($array,$name,$value);
			} else {
				// caso 4 <algo>algo</algo>
				if(count($attr)) $value=array("value"=>$value,"#attr"=>$attr);
				set_array($array,$name,$value);
			}
		} elseif($type=="cdata") {
			// NOTHING TO DO
		} else {
			xml_error("Unknown tag type with name '&lt;/$name&gt;'",$linea);
		}
	}
	return $array;
}

function __import_csv2array($file,$nodes,$sep=";") {
	$fd=fopen($file,"r");
	$array=array();
	$count=null;
	while($row=fgetcsv($fd,0,$sep)) {
		foreach($row as $key=>$val) $row[$key]=getutf8($val);
		if(is_null($count)) $count=count($row);
		$total=count($row);
		if($total==$count) $array[]=$row;
	}
	fclose($fd);
	$array=__import_array2tree($array,$nodes);
	return $array;
}

function __import_xls2array($file,$nodes) {
	set_include_path("lib/phpexcel:".get_include_path());
	include_once("PHPExcel.php");
	require_once('PHPExcel/Reader/Excel5.php');
	$objReader=new PHPExcel_Reader_Excel5();
	$objPHPExcel=$objReader->load($file);
	$SheetCollection=$objPHPExcel->getAllSheets();
	$array=$SheetCollection[0]->toArray();
	unset($objReader);
	unset($objPHPExcel);
	unset($SheetCollection);
	$array=__import_array2tree($array,$nodes);
	return $array;
}

function __import_array2tree($array,$nodes) {
	if(!$nodes) return $array;
	$head=array_shift($array);
	$count_head=count($head);
	$count_nodes=count($nodes);
	foreach($nodes as $key=>$val) {
		if($key==$count_nodes-1) $nodes[$key]=array("offset"=>$val,"length"=>$count_head-$val);
		else $nodes[$key]=array("offset"=>$val,"length"=>$nodes[$key+1]-$val);
	}
	$array=__import_array2tree_rec($array,$nodes);
	echo "<pre>";
	print_r($array);
	echo "</pre>";
	die();
	return $array;
}

function __import_array2tree_rec($array,$nodes) {
	$node=array_shift($nodes);
	$result=array();
	foreach($array as $key=>$val) {
		$array2=array_splice($val,$node["offset"],$node["length"]);
		$hash=md5(serialize($array2));
		if(!isset($result[$hash])) $result[$hash]=array();
		if(count($nodes)) {
			if(!isset($result[$hash]["data"])) $result[$hash]["data"]=$array2;
			if(!isset($result[$hash]["rows"])) $result[$hash]["rows"]=array();
			$result[$hash]["rows"][]=__import_array2tree_rec($array,$nodes);
		} else {
			$result[$hash]=$array2;
		}
	}
	return $result;
}

function __import_col2name($col) {
	static $chars="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	static $len=26;
	$index1=intval($col/$len)-1;
	$index2=$col%$len;
	if($index1==-1) return $chars[$index2];
	return $chars[$index1].$chars[$index2];
}

function __import_make_table($array,$select=null) {
	$head=array_shift($array);
	$result="";
	$result.="<table class='tabla width100'>\n";
	$result.="<tr>\n";
	foreach($head as $col=>$field) {
		$result.="<td class='thead center'>\n";
		$result.=__import_col2name($col);
		$result.="</td>\n";
	}
	$result.="</tr>\n";
	if(is_array($select)) {
		$result.="<tr>\n";
		foreach($head as $col=>$field) {
			$result.="<td class='tbody center'>\n";
			$result.="<select class='ui-state-default ui-corner-all' name='${field}'>\n";
			$result.="<option value=''></option>\n";
			foreach($select as $value=>$option) $result.="<option value='${value}'>${option}</option>\n";
			$result.="</select>";
			$result.="</td>\n";
		}
		$result.="</tr>\n";
	}
	$result.="<tr>\n";
	foreach($head as $field) {
		$result.="<td class='thead center'>\n";
		$result.=$field;
		$result.="</td>\n";
	}
	$result.="</tr>\n";
	foreach($array as $line=>$row) {
		$result.="<tr>\n";
		foreach($row as $field) {
			$result.="<td class='tbody'>\n";
			$result.=$field;
			$result.="</td>\n";
		}
		$result.="</tr>\n";
	}
	$result.="</table>\n";
	return $result;
}
?>