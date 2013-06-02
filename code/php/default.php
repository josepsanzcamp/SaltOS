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
if(!defined("__DEFAULT_PHP__")) {
	define("__DEFAULT_PHP__",1);

	function __eval_querytag($array) {
		foreach($array as $key=>$val) {
			if(is_array($val)) {
				$array[$key]=__eval_querytag($val);
			} elseif($key=="query") {
				$result=db_query($val);
				$count=0;
				while($row=db_fetch_row($result)) {
					$row["__ROW_NUMBER__"]=++$count;
					set_array($array["rows"],"row",$row);
				}
				db_free($result);
				unset($array[$key]);
			}
		}
		return $array;
	}

	function __eval_value($input,$row) {
		return eval_protected($input,array("row"=>$row));
	}

	function __eval_array($array,$row) {
		foreach($array as $key=>$val) {
			$array[$key]=__eval_value($val,$row);
		}
		return $array;
	}

	function __eval_explode($separator,$str,$limit=0) {
		$result=array();
		$len=strlen($str);
		$ini=0;
		$count=0;
		$open=array("'"=>0,'"'=>0);
		$pars=0;
		for($i=0;$i<$len;$i++) {
			$letter=$str[$i];
			if(array_key_exists($letter,$open)) {
				$open[$letter]=($open[$letter]==1)?0:1;
			} elseif($letter=="(") {
				$pars++;
			} elseif($letter==")") {
				$pars--;
			}
			if($letter==$separator && array_sum($open)+$pars==0) {
				if($limit>0 && $count==$limit-1) {
					$result[]=substr($str,$ini);
					$ini=$i;
					break;
				} else {
					$result[]=substr($str,$ini,$i-$ini);
					$ini=$i+1;
					$count++;
				}
			}
		}
		if($i!=$ini) {
			$result[]=substr($str,$ini,$i-$ini);
		}
		return $result;
	}

	function __eval_pdftag($array,$row=array()) {
		static $pdf;

		if(!defined("__CLASS_PDF__")) {
			define("__CLASS_PDF__",1);

			include("lib/tcpdf/tcpdf.php");

			class PDF extends TCPDF {
				var $arr_header;
				var $row_header;
				var $arr_footer;
				var $row_footer;
				var $check_y_enabled;

				function Init() {
					$this->Set_Header(array(),array());
					$this->Set_Footer(array(),array());
					$this->check_y_enable(true);
				}

				function Set_Header($arr,$row) {
					$this->arr_header=$arr;
					$this->row_header=$row;
				}

				function Set_Footer($arr,$row) {
					$this->arr_footer=$arr;
					$this->row_footer=$row;
				}

				function Header() {
					$oldenable=$this->check_y_enable(false);
					__eval_pdftag($this->arr_header,$this->row_header);
					$this->check_y_enable($oldenable);
				}

				function Footer() {
					$oldenable=$this->check_y_enable(false);
					__eval_pdftag($this->arr_footer,$this->row_footer);
					$this->check_y_enable($oldenable);
				}

				function check_y($offset=0) {
					if($this->check_y_enabled) {
						if($this->y+$offset>($this->hPt/$this->k)-$this->bMargin) {
							$oldx=$this->GetX();
							$this->AddPage();
							$this->SetY($this->tMargin);
							$this->SetX($oldx);
						}
					}
				}

				function check_y_enable($enable) {
					$retval=$this->check_y_enabled;
					$this->check_y_enabled=$enable;
					return $retval;
				}
			}
		}

		// SUPPORT FOR LTR AND RTL LANGS
		global $_LANG;
		$dir=$_LANG["dir"];
		$rtl=array("ltr"=>array("L"=>"L","C"=>"C","R"=>"R"),"rtl"=>array("L"=>"R","C"=>"C","R"=>"L"));
		$fonts=array("normal"=>"dejavusanscondensed","mono"=>"dejavusansmono");

		if(is_array($array)) {
			foreach($array as $key=>$val) {
				$key=strtok($key,"#");
				static $booleval=1;
				switch($key) {
					case "eval":
						$booleval=__eval_value($val,$row);
						break;
					case "constructor":
						if(!$booleval) break;
						$temp=__eval_array(__eval_explode(",",$val),$row);
						$pdf=new PDF($temp[0],$temp[1],$temp[2]);
						$pdf->SetCreator(get_name_version_revision());
						$pdf->setRTL($dir=="rtl");
						$pdf->Init();
						break;
					case "margins":
						if(!$booleval) break;
						$temp=__eval_array(__eval_explode(",",$val),$row);
						$pdf->SetMargins($temp[3],$temp[0],$temp[1]);
						$pdf->SetAutoPageBreak(true,$temp[2]);
						break;
					case "query":
						if(!$booleval) break;
						$query=__eval_value($val,$row);
						break;
					case "foreach":
						if(!$booleval) break;
						if(!isset($query)) show_php_error(array("phperror"=>"Foreach without query!!!"));
						$result=db_query($query);
						$count=0;
						while($row2=db_fetch_row($result)) {
							$row2["__ROW_NUMBER__"]=++$count;
							__eval_pdftag($val,$row2);
						}
						db_free($result);
						break;
					case "output":
						if(!$booleval) break;
						$name=__eval_value($val,$row);
						$buffer=$pdf->Output($name,"S");
						if(!defined("__CANCEL_HEADER__")) {
							header_powered();
							header_expires(false);
							header("Content-Type: application/pdf");
							header("Content-Disposition: attachment; filename=\"$name\"");
						}
						echo $buffer;
						if(!defined("__CANCEL_DIE__")) die();
						break;
					case "header":
						if(!$booleval) break;
						$pdf->Set_Header($val,$row);
						break;
					case "footer":
						if(!$booleval) break;
						$pdf->Set_Footer($val,$row);
						break;
					case "newpage":
						if(!$booleval) break;
						if($val) $pdf->AddPage(__eval_value($val,$row));
						else $pdf->AddPage();
						break;
					case "font":
						if(!$booleval) break;
						$temp2=__eval_array(__eval_explode(",",$val,4),$row);
						$temp=array($temp2[0],$temp2[1],$temp2[2],color2dec($temp2[3],"R"),color2dec($temp2[3],"G"),color2dec($temp2[3],"B"));
						$pdf->SetFont($fonts[$temp[0]],$temp[1],$temp[2]);
						$pdf->SetTextColor($temp[3],$temp[4],$temp[5]);
						break;
					case "image":
						if(!$booleval) break;
						$temp=__eval_array(__eval_explode(",",$val,6),$row);
						if(isset($temp[5])) $pdf->StartTransform();
						if(isset($temp[5])) $pdf->Rotate($temp[5],$temp[0],$temp[1]);
						if(!file_exists($temp[4])) $temp[4]=get_directory("dirs/filesdir").getDefault("configs/logo_file","img/deflogo.png");
						$pdf->Image($temp[4],$temp[0],$temp[1],$temp[2],$temp[3]);
						if(isset($temp[5])) $pdf->StopTransform();
						break;
					case "text":
						if(!$booleval) break;
						$temp=__eval_array(__eval_explode(",",$val,4),$row);
						if(isset($temp[3])) $pdf->StartTransform();
						if(isset($temp[3])) $pdf->Rotate($temp[3],$temp[0],$temp[1]);
						$pdf->SetXY($temp[0],$temp[1]);
						$pdf->Cell(0,0,$temp[2]);
						if(isset($temp[3])) $pdf->StopTransform();
						break;
					case "textarea":
						if(!$booleval) break;
						$temp=__eval_array(__eval_explode(",",$val,7),$row);
						if(isset($temp[6])) $pdf->StartTransform();
						if(isset($temp[6])) $pdf->Rotate($temp[6],$temp[0],$temp[1]);
						$pdf->SetXY($temp[0],$temp[1]);
						if(!isset($temp[6])) $pdf->check_y($temp[3]);
						$pdf->MultiCell($temp[2],$temp[3],$temp[5],0,$rtl[$dir][$temp[4]]);
						if(isset($temp[6])) $pdf->StopTransform();
						break;
					case "color":
						if(!$booleval) break;
						$temp2=__eval_array(__eval_explode(",",$val,2),$row);
						$temp=array(color2dec($temp2[0],"R"),color2dec($temp2[0],"G"),color2dec($temp2[0],"B"),color2dec($temp2[1],"R"),color2dec($temp2[1],"G"),color2dec($temp2[1],"B"));
						$pdf->SetDrawColor($temp[0],$temp[1],$temp[2]);
						$pdf->SetFillColor($temp[3],$temp[4],$temp[5]);
						break;
					case "rect":
						if(!$booleval) break;
						$temp=__eval_array(__eval_explode(",",$val,7),$row);
						if(isset($temp[5])) $pdf->SetLineWidth($temp[5]);
						if(isset($temp[6])) {
							$pdf->RoundedRect($temp[0],$temp[1],$temp[2],$temp[3],$temp[6],"1111",$temp[4]);
						} else {
							$pdf->Rect($temp[0],$temp[1],$temp[2],$temp[3],$temp[4]);
						}
						break;
					case "line":
						if(!$booleval) break;
						$temp=__eval_array(__eval_explode(",",$val,5),$row);
						if(isset($temp[4])) $pdf->SetLineWidth($temp[4]);
						$pdf->Line($temp[0],$temp[1],$temp[2],$temp[3]);
						break;
					case "setxy":
						if(!$booleval) break;
						$temp=__eval_array(__eval_explode(",",$val,2),$row);
						$pdf->SetXY($temp[0],$temp[1]);
						$pdf->check_y();
						break;
					case "getxy":
						if(!$booleval) break;
						$temp=__eval_array(__eval_explode(",",$val,2),$row);
						$row[$temp[0]]=$pdf->GetX();
						$row[$temp[1]]=$pdf->GetY();
						break;
					case "pageno":
						if(!$booleval) break;
						$temp=__eval_array(__eval_explode(",",$val,7),$row);
						if(!isset($temp[4])) {
							$pdf->SetXY($temp[0],$temp[1]);
							$a=isset($temp[2])?$temp[2]:"";
							$b=isset($temp[3])?$temp[3]:"";
							$pdf->Cell(0,0,$a.$pdf->PageNo().$b.$pdf->getAliasNbPages());
						} else {
							$pdf->SetXY($temp[0],$temp[1]);
							$a=isset($temp[5])?$temp[5]:"";
							$b=isset($temp[6])?$temp[6]:"";
							$pdf->check_y($temp[3]);
							$pdf->MultiCell($temp[2],$temp[3],$a.$pdf->PageNo().$b.$pdf->getAliasNbPages(),0,$temp[4]);
						}
						break;
					case "checky":
						if(!$booleval) break;
						$temp=__eval_array(__eval_explode(",",$val,1),$row);
						$pdf->check_y($temp[0]);
						break;
					default:
						show_php_error(array("phperror"=>"Eval PDF Tag error: $key"));
						break;
				}
			}
		}
	}

	function __xls_dump($query,$page) {
		$result=db_query($query);
		$matrix=array(array());
		for($i=0;$i<db_num_fields($result);$i++) {
			$matrix[0][]=db_field_name($result,$i);
		}
		while($row=db_fetch_row($result)) {
			$matrix[]=array_values($row);
		}
		db_free($result);
		set_include_path("lib/phpexcel:".get_include_path());
		include_once("PHPExcel.php");
		// TODO: POSSIBLE PHPEXCEL 1.7.9 BUG
		//~ $cacheMethod=PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		//~ $cacheSettings=array("memoryCacheSize"=>"8MB");
		//~ PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);
		$objPHPExcel=new PHPExcel();
		$objPHPExcel->getProperties()->setCreator(get_name_version_revision());
		$objPHPExcel->getProperties()->setLastModifiedBy(current_datetime());
		$title=ucfirst($page);
		$objPHPExcel->getProperties()->setTitle($title);
		$objPHPExcel->getProperties()->setSubject($title);
		$objPHPExcel->getProperties()->setDescription($title);
		$objPHPExcel->getProperties()->setKeywords($title);
		$objPHPExcel->getProperties()->setCategory($title);
		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->fromArray($matrix,NULL,"A1");
		$objPHPExcel->getActiveSheet()->setTitle(substr($title,0,31));
		if(!defined("__CANCEL_HEADER__")) {
			header_powered();
			header_expires(false);
			header("Content-Type: application/x-excel");
			$name=$page.getDefault("exts/excelext",".xls");
			header("Content-Disposition: attachment; filename=\"$name\"");
		}
		$objWriter=PHPExcel_IOFactory::createWriter($objPHPExcel,"Excel5");
		$objWriter->save("php://output");
		if(!defined("__CANCEL_DIE__")) die();
	}

	function __process_data_rec($query,&$go,&$commit) {
		$rows=array();
		foreach($query as $key=>$val) {
			if(is_array($val)) {
				set_array($rows,$key,__process_data_rec($val,$go,$commit));
			} else {
				$val=trim($val);
				if($commit) {
					$result=db_query($val);
					$count=0;
					while($row=db_fetch_row($result)) {
						$row["__ROW_NUMBER__"]=++$count;
						$is_action=0;
						if(isset($row["action_error"])) {
							$error=$row["action_error"];
							session_error($error);
							$is_action=1;
						}
						if(isset($row["action_alert"])) {
							$alert=$row["action_alert"];
							session_alert($alert);
							$is_action=1;
						}
						if(isset($row["action_commit"])) {
							$commit=$row["action_commit"];
							$is_action=1;
						}
						if(isset($row["action_go"])) {
							$go=$row["action_go"];
							$is_action=1;
						}
						if(isset($row["action_include"])) {
							$include=$row["action_include"];
							$include=explode(",",$include);
							foreach($include as $file) {
								if(!file_exists($file)) show_php_error(array("xmlerror"=>"Include '$file' not found"));
								include($file);
							}
							$is_action=1;
						}
						if(!$is_action) {
							set_array($rows[$key],"row",$row);
						}
					}
					db_free($result);
				}
			}
		}
		return $rows;
	}
}

if(!getDefault("$page/$action")) {
	$_LANG["default"]="denied,menu,common";
	$_CONFIG["denied"]=eval_attr(xml2array("xml/denied.xml"));
	$_RESULT["form"]=getDefault("denied/form");
	add_css_js_page($_RESULT["form"],"denied");
	set_array($_ERROR,"error","Unknown action '$action'");
	$action="";
}

switch($action) {
	case "insert":
	case "update":
	case "delete":
		eval_files();
		$config=getDefault("$page/$action");
		if(eval_bool(getDefault("debug/actiondebug"))) {
			debug_dump(false);
			$config=eval_attr($config);
			debug_dump();
		}
		$commit=1;
		if($action=="insert") $go=-2;
		if($action=="update") $go=-2;
		if($action=="delete") $go=-1;
		if(eval_bool(intval(getParam("returnhere"))?"true":"false")) $go=-1;
		foreach($config as $query) {
			$inline=eval_attr($query);
			foreach($inline as $query) {
				$query=trim($query);
				if($query=="") continue;
				$is_select=strtoupper(substr($query,0,6))=="SELECT";
				if($is_select) {
					$result=db_query($query);
					$count=0;
					while($row=db_fetch_row($result)) {
						$row["__ROW_NUMBER__"]=++$count;
						if(isset($row["action_delete"])) {
							$delete=$row["action_delete"];
							if(substr($delete,0,1)!="/") $delete=get_directory("dirs/filesdir").$delete;
							if(file_exists($delete)) unlink($delete);
						}
						if(isset($row["action_error"])) {
							$error=$row["action_error"];
							session_error($error);
						}
						if(isset($row["action_alert"])) {
							$alert=$row["action_alert"];
							session_alert($alert);
						}
						if(isset($row["action_commit"])) {
							$commit=$row["action_commit"];
						}
						if(isset($row["action_go"])) {
							$go=$row["action_go"];
						}
						if(isset($row["action_include"])) {
							$include=$row["action_include"];
							$include=explode(",",$include);
							foreach($include as $file) {
								if(!file_exists($file)) show_php_error(array("xmlerror"=>"Include '$file' not found"));
								include($file);
							}
						}
						if(!$commit) break;
					}
					db_free($result);
				} else {
					db_query($query);
				}
				if(!$commit) break;
			}
			if(!$commit) break;
		}
		javascript_history($go);
		die();
	case "list":
		include("php/listsim.php");
		$config=getDefault("$page/$action");
		if(eval_bool(getDefault("debug/actiondebug"))) debug_dump(false);
		$config=eval_attr($config);
		if(eval_bool(getDefault("debug/actiondebug"))) debug_dump();
		$_RESULT[$action]=$config;
		add_css_js_page($_RESULT[$action],$page);
		// GET AND REMOVE THE NEEDED XML NODES
		foreach(array("query","order","limit","offset") as $node) {
			if(!isset($config[$node])) show_php_error(array("xmlerror"=>"&lt;$node&gt; not found for &lt;$action&gt;"));
			unset($_RESULT[$action][$node]);
		}
		$query0=$config["query"];
		$limit=$config["limit"];
		$offset=$config["offset"];
		// CHECK ORDER
		list($order,$array)=list_check_order($config["order"],$config["fields"]);
		// MARK THE SELECTED ORDER FIELD
		foreach($_RESULT[$action]["fields"] as $key=>$val) {
			$selected=0;
			if(isset($val["name"]) && $val["name"]==$array[0][0]) $selected=1;
			if(isset($val["order"]) && $val["order"]==$array[0][0]) $selected=1;
			if(isset($val["order".$array[0][1]]) && $val["order".$array[0][1]]==$array[0][0]) $selected=1;
			if($selected) $_RESULT[$action]["fields"][$key]["selected"]=$array[0][1];
		}
		// EXECUTE THE QUERY TO GET THE ROWS WITH LIMIT AND OFFSET
		$query="$query0 ORDER BY $order LIMIT $offset,$limit";
		$result=db_query($query);
		$count=0;
		while($row=db_fetch_row($result)) {
			$row["__ROW_NUMBER__"]=++$count;
			set_array($_RESULT[$action]["rows"],"row",$row);
		}
		db_free($result);
		// CONTINUE WITH NORMAL OPERATION
		$_RESULT[$action]=__eval_querytag($_RESULT[$action]);
		break;
	case "form":
		$config=getDefault("$page/$action");
		if(eval_bool(getDefault("debug/actiondebug"))) debug_dump(false);
		$config=eval_attr($config);
		if(eval_bool(getDefault("debug/actiondebug"))) debug_dump();
		$_RESULT[$action]=$config;
		add_css_js_page($_RESULT[$action],$page);
		unset($_RESULT[$action]["views"]);
		if($id==0) {
			if(isset($config["views"]["insert"]["title"])) $_RESULT[$action]["title"]=$config["views"]["insert"]["title"];
			if(isset($config["views"]["insert"]["query"])) $query=$config["views"]["insert"]["query"];
		} else {
			if($id>0) {
				if(isset($config["views"]["update"]["title"])) $_RESULT[$action]["title"]=$config["views"]["update"]["title"];
				if(isset($config["views"]["update"]["query"])) $query=$config["views"]["update"]["query"];
			} else {
				if(isset($config["views"]["view"]["title"])) $_RESULT[$action]["title"]=$config["views"]["view"]["title"];
				if(isset($config["views"]["view"]["query"])) $query=$config["views"]["view"]["query"];
			}
		}
		if(isset($query)) {
			$fixquery=is_array($query)?0:1;
			$go=0;
			$commit=1;
			if($fixquery) $query=array("default"=>$query);
			$rows=__process_data_rec($query,$go,$commit);
			if($fixquery) $rows=$rows["default"];
			set_array($_RESULT[$action],"rows",$rows);
			if($go) {
				javascript_history($go);
				die();
			}
		} else {
			$_LANG["default"]="denied,menu,common";
			$_CONFIG["denied"]=eval_attr(xml2array("xml/denied.xml"));
			$_RESULT["form"]=getDefault("denied/form");
			add_css_js_page($_RESULT["form"],"denied");
			set_array($_ERROR,"error","Unknown action '$action'");
		}
		$_RESULT[$action]=__eval_querytag($_RESULT[$action]);
		break;
	case "excel":
		$config=getDefault("$page/$action");
		if(eval_bool(getDefault("debug/actiondebug"))) debug_dump(false);
		$config=eval_attr($config);
		if(eval_bool(getDefault("debug/actiondebug"))) debug_dump();
		$oldcache=set_use_cache("false");
		$query=$config["query"];
		__xls_dump($query,$page);
		set_use_cache($oldcache);
		if(!defined("__CANCEL_DIE__")) die();
		break;
	case "pdf":
		$config=getDefault("$page/$action");
		if(eval_bool(getDefault("debug/actiondebug"))) debug_dump(false);
		$config=eval_attr($config);
		if(eval_bool(getDefault("debug/actiondebug"))) debug_dump();
		$oldcache=set_use_cache("false");
		__eval_pdftag($config);
		set_use_cache($oldcache);
		if(!defined("__CANCEL_DIE__")) die();
		break;
	default:
		if(!$action) break;
		$_LANG["default"]="denied,menu,common";
		$_CONFIG["denied"]=eval_attr(xml2array("xml/denied.xml"));
		$_RESULT["form"]=getDefault("denied/form");
		add_css_js_page($_RESULT["form"],"denied");
		set_array($_ERROR,"error","Unknown action '$action'");
		break;
}
?>