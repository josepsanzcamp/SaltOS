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

if(!defined("__CLASS_PDF__")) {
	define("__CLASS_PDF__",1);

	require_once("lib/tcpdf/tcpdf.php");

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
			if(getDefault("ini_set")) eval_iniset(getDefault("ini_set"));
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

	// FUNCTIONS
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
		static $debug=false;

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
						capture_next_error(); // TO PREVENT SOME SPURIOUS BUGS
						$pdf->Image($temp[4],$temp[0],$temp[1],$temp[2],$temp[3]);
						get_clear_error();
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
						if($debug) {
							$pdf->SetDrawColor(100,100,100);
							$pdf->SetFillColor(200,200,200);
							$pdf->Rect($temp[0],$temp[1],$temp[2],$temp[3],"DF");
							$pdf->SetTextColor(50,50,50);
						}
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
							$pdf->Cell(0,0,$a.$pdf->getAliasNumPage().$b.$pdf->getAliasNbPages());
						} else {
							 // TO FIX AN ALIGN BUG
							if($temp[4]=="C") $temp[0]+=7.5;
							if($temp[4]=="R") $temp[0]+=15;
							// CONTINUE
							$pdf->SetXY($temp[0],$temp[1]);
							$a=isset($temp[5])?$temp[5]:"";
							$b=isset($temp[6])?$temp[6]:"";
							$pdf->check_y($temp[3]);
							$pdf->MultiCell($temp[2],$temp[3],$a.$pdf->getAliasNumPage().$b.$pdf->getAliasNbPages(),0,$temp[4]);
						}
						break;
					case "checky":
						if(!$booleval) break;
						$temp=__eval_array(__eval_explode(",",$val,1),$row);
						$pdf->check_y($temp[0]);
						break;
					case "link":
						if(!$booleval) break;
						$temp=__eval_array(__eval_explode(",",$val,4),$row);
						$pdf->SetXY($temp[0],$temp[1]);
						$pdf->Cell(0,0,$temp[2],0,0,"",false,$temp[3]);
						break;
					case "debug":
						if(!$booleval) break;
						$debug=eval_bool($val);
						break;
					default:
						show_php_error(array("phperror"=>"Eval PDF Tag error: $key"));
						break;
				}
			}
		}
	}

}

if(getParam("action")=="pdf") {
	$_LANG["default"]="$page,menu,common";
	if(!file_exists("xml/${page}.xml")) action_denied();
	$config=xml2array("xml/${page}.xml");
	if(!isset($config[$action])) action_denied();
	$config=$config[$action];
	if(eval_bool(getDefault("debug/actiondebug"))) debug_dump(false);
	$config=eval_attr($config);
	if(eval_bool(getDefault("debug/actiondebug"))) debug_dump();
	$oldcache=set_use_cache("false");
	__eval_pdftag($config);
	set_use_cache($oldcache);
	if(!defined("__CANCEL_DIE__")) die();
}
?>