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
function __import_eval_with_vars($eval,$vars="") {
	$oldeval=$eval;
	if(!is_array($vars)) $vars=array();
	extract($vars);
	if(substr($eval,-1,1)==";") $eval=substr($eval,0,-1);
	$eval=__import_eval_explode(";",$eval);
	if(!count($eval)) $eval[]="";
	$eval[count($eval)-1]="return ".$eval[count($eval)-1].";";
	$eval=implode(";",$eval);
	capture_next_error();
	ob_start();
	$eval=eval($eval);
	$error1=ob_get_clean();
	$error2=get_clear_error();
	if($error1.$error2) show_php_error(array("phperror"=>"Internal error: $oldeval","details"=>$error1.$error2,"backtrace"=>debug_backtrace()));
	$vars=compact(array_keys($vars));
	return array($eval,$vars);
}

function __import_make_insert_query($table,$array) {
	$list1=array();
	$list2=array();
	foreach($array as $key=>$val) {
		$list1[]="`".$key."`";
		$list2[]="'".getString($val)."'";
	}
	$list1=implode(",",$list1);
	$list2=implode(",",$list2);
	$query="INSERT INTO $table($list1) VALUES($list2)";
	return $query;
}

function __import_make_update_query($table,$array,$id) {
	$list1=array();
	foreach($array as $key=>$val) {
		$list1[]="`".$key."`='".getString($val)."'";
	}
	$list1=implode(",",$list1);
	$query="UPDATE $table SET $list1 WHERE id='$id'";
	return $query;
}

function __import_getnode($path,$array) {
	if(!is_array($path)) $path=explode("/",$path);
	$elem=array_shift($path);
	if(!is_array($array) || !isset($array[$elem])) return null;
	if(count($path)==0) return $array[$elem];
	return __import_getnode($path,__import_getvalue($array[$elem]));
}

function __import_getvalue($array) {
	return (is_array($array) && isset($array["value"]) && isset($array["#attr"]))?$array["value"]:$array;
}

function __import_importfile($id_importacion) {
	$query="SELECT * FROM tbl_ficheros WHERE id_aplicacion='".page2id("importaciones")."' AND id_registro='".abs($id_importacion)."'";
	$row=execute_query($query);
	switch($row["fichero_type"]) {
		case "application/xml":
			$temp=__import_xml2array($file);
			$tree=__import_xml2tree($temp,$levels,$array,$needed);
			break;
		case "text/plain":
			$temp=__import_csv2array($file,$fixbug);
			$tree=__import_matrix2tree($temp,$levels,$array,$needed);
			break;
		case "application/vnd.ms-excel":
			$temp=__import_xls2array($file);
			$tree=__import_matrix2tree($temp,$levels,$array,$needed);
			break;
		default:
			show_php_error("Unknown type '$tipo'");
	}
	return $tree;
}

function __import_matrix2tree_rec($row,$levels,$array,&$hashes,&$result,$needed) {
	$level=array_shift($levels);
	$row2=__import_process_block($row,__import_getnode(__import_getnode("usar",$level),$array));
	if(is_null($needed) || $needed=="" || (!is_null($needed) && $needed!="" && isset($row2[$needed]) && $row2[$needed]!="")) {
		$hash=md5(serialize($row2));
		if(count($levels)) {
			if(!isset($hashes[$hash])) {
				$hashes[$hash]=array();
				$result[]=array($row2,array());
			}
			$pos=array_search($hash,array_keys($hashes));
			__import_matrix2tree_rec($row,$levels,$array,$hashes[$hash],$result[$pos][1],null);
		} else {
			$hashes[]=$hash;
			$result[]=$row2;
		}
	}
}

function __import_matrix2tree($data,$levels,$array,$needed) {
	$header=array_shift($data);
	foreach($header as $key=>$val) $header[$key]=encode_bad_chars($val);
	$hashes=array();
	$result=array();
	foreach($data as $key=>$val) __import_matrix2tree_rec(array_combine($header,$val),$levels,$array,$hashes,$result,$needed);
	return $result;
}

function __import_xml2tree($data,$levels,$array,$needed) {
	$result=array();
	$level=array_shift($levels);
	$data=__import_getnode(__import_getnode("from",$level),$data);
	$temp=__import_getnode("value",$data);
	if(!is_null($temp)) $data=$temp;
	$fields=__import_getnode(__import_getnode("usar",$level),$array);
	if(count($levels)) $from_next=__import_getnode("from",reset($levels));
	$tabla=__import_getnode("tabla",$level);
	foreach($data as $key=>$val) {
		if(is_array($val)) {
			if(isset($from_next)) {
				$backup=isset($val[$from_next])?$val[$from_next]:null;
				unset($val[$from_next]);
			}
			foreach($val as $key2=>$val2) {
				$temp=encode_bad_chars($key2);
				$val[$temp]=$val2;
				unset($val[$key2]);
			}
			$val=__import_process_block($val,$fields);
			if(isset($from_next)) {
				if(is_null($needed) || $needed=="" || (!is_null($needed) && $needed!="" && isset($val[$needed]) && $val[$needed]!="")) {
					if(is_array($backup) && count($backup)) {
						$result[]=array($val,__import_xml2tree(array($from_next=>$backup),$levels,$array,null));
					} else {
						$result[]=array($val,array());
					}
				}
				unset($backup);
			} else {
				$result[]=$val;
			}
		} else {
			$val=__import_process_block(array(encode_bad_chars(limpiar_key($key))=>$val),$fields);
			$result[]=$val;
		}
	}
	return $result;
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

function __import_csv2array($file,$fixbug) {
	$fd=fopen($file,"r");
	$array=array();
	$count=null;
	while($row=fgetcsv($fd,0,";")) {
		foreach($row as $key=>$val) $row[$key]=getutf8($val);
		if(is_null($count)) $count=count($row);
		$total=count($row);
		if(!is_null($fixbug) && $fixbug!="" && $total==$count+1) {
			$row[$fixbug].=";".$row[$fixbug+1];
			$total--;
			for($i=$fixbug+1;$i<$total;$i++) $row[$i]=$row[$i+1];
			unset($row[$total]);
		}
		if($total==$count) $array[]=$row;
	}
	fclose($fd);
	return $array;
}

function __import_xls2array($file) {
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
	return $array;
}
?>