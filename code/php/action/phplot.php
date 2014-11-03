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
if(!check_user()) action_denied();
// GET DATA FROM QUERYSTRING
$width=intval(getParam("width"));
$height=intval(getParam("height"));
$title=getParam("title");
$legend=explode("|",getParam("legend"));
$vars=intval(getParam("vars"));
$colors=explode("|",getParam("colors"));
$graph=getParam("graph");
$ticks=explode("|",getParam("ticks"));
$posx=explode("|",getParam("posx"));
$data=array();
for($i=1;$i<=$vars;$i++) $data[$i]=explode("|",getParam("data$i"));
// CACHE CONTROL
$params=serialize(array($width,$height,$title,$legend,$vars,$colors,$graph,$ticks,$posx,$data));
$cache=get_cache_file($params,getDefault("exts/xmlext",".xml"));
//if(file_exists($cache)) unlink($cache); // ONLY FOR TESTS PURPOSES AND TODO REMOVED
if(!file_exists($cache)) {
	// BEGIN THE PHPLOT WRAPPER
	include_once("lib/phplot/phplot.php");
	include_once("lib/phplot/contrib/color_range.php");
	include_once("lib/phplot/rgb.inc.php");
	// REPAIR SOME DATA
	if($legend[count($legend)-1]=="") array_pop($legend);
	if($colors[count($colors)-1]=="") array_pop($colors);
	if($ticks[count($ticks)-1]=="") array_pop($ticks);
	if($posx[count($posx)-1]=="") array_pop($posx);
	for($i=1;$i<=$vars;$i++) if($data[$i][count($data[$i])-1]=="") array_pop($data[$i]);
	// REMOVE SOME DATA IF GRAPH IS PIE
	if($graph=="pie") {
		$newticks=array();
		$newdata=array();
		for($i=1;$i<=$vars;$i++) $newdata[$i]=array();
		foreach($data[1] as $key=>$val) {
			if(floatval($val)>0) {
				$newticks[]=$ticks[$key];
				for($i=1;$i<=$vars;$i++) $newdata[$i][]=$data[$i][$key];
			}
		}
		if(count($newticks)) {
			$ticks=$newticks;
			for($i=1;$i<=$vars;$i++) $data[$i]=$newdata[$i];
		} else {
			$graph="error";
		}
	}
	// MOUNT THE CORRECT DATA STRUCT
	if($graph!="error") {
		$datatype=(count($ticks)==count($posx))?"data-data":"text-data";
		$count=count($ticks);
		$values=array();
		$hastick=1;
		$hasdata=0;
		$last="";
		for($j=0;$j<$count;$j++) {
			$value=array();
			if($datatype=="text-data") {
				$value[]=$ticks[$j];
				$hastick&=$ticks[$j]!="";
			}
			if($datatype=="data-data") {
				$value[]=($last!=$ticks[$j])?$ticks[$j]:"";
				$value[]=$posx[$j];
				$hastick&=$ticks[$j]!="";
				$hastick&=$posx[$j]!="";
				$last=$ticks[$j];
			}
			for($i=1;$i<=$vars;$i++) {
				$value[]=$data[$i][$j];
				$hasdata|=$data[$i][$j]!="";
			}
			$values[]=$value;
		}
		if(!$hastick || !$hasdata) $graph="error";
	}
	// CALCULATE THE PRECISION
	if($graph!="error") {
		$maxvalue=max($data[1]);
		$minvalue=min($data[1]);
		for($i=2;$i<=$vars;$i++) {
			$maxvalue=max(max($data[$i]),$maxvalue);
			$minvalue=min(min($data[$i]),$minvalue);
		}
		$diff=$maxvalue-$minvalue;
		if($diff<=1) $precision=2;
		elseif($diff<=10) $precision=1;
		else $precision=0;
		$maxvalue2=$maxvalue+$diff*0.1;
		$maxvalue=($maxvalue<=1 && $maxvalue2>=1)?1:(($maxvalue<=100 && $maxvalue2>=100)?100:$maxvalue2);
		$minvalue2=$minvalue-$diff*0.1;
		$minvalue=($minvalue>=0 && $minvalue2<=0)?0:$minvalue2;
	}
	// DEFINE CALLBACKS
	function __phplot_callback_for_bars($im,$data,$shape,$row,$column,$x1,$y1,$x2,$y2){
		global $_RESULT;
		$value=$data[$row][$column+1];
		$coords=sprintf("%d,%d,%d,%d",$x1,$y1-5,$x2+5,$y2);
		set_array($_RESULT["map"],"area",array("shape"=>"rect","coords"=>$coords,"value"=>$value));
	}
	function __phplot_callback_for_points($im,$data,$shape,$row,$column,$xc,$yc){
		global $_RESULT;
		$value=$data[$row][$column+1];
		$coords=sprintf("%d,%d,%d",$xc,$yc,5);
		set_array($_RESULT["map"],"area",array("shape"=>"circle","coords"=>$coords,"value"=>$value));
	}
	function __phplot_callback_for_pie($im,$data,$shape,$segment,$unused,$xc,$yc,$wd,$ht,$start_angle,$end_angle){
		global $_RESULT;
		$arc_angle=$start_angle-$end_angle;
		$n_steps=(int)ceil($arc_angle/20);
		$step_angle=$arc_angle/$n_steps;
		$rx=$wd/2+2;
		$ry=$ht/2+2;
		$points=array($xc,$yc);
		$done_angle=deg2rad($start_angle);
		for($i=0;;$i++){
			$theta=min($done_angle,deg2rad($end_angle+$i*$step_angle));
			$points[]=(int)($xc+$rx*cos($theta));
			$points[]=(int)($yc+$ry*sin($theta));
			if($theta>=$done_angle)break;
		}
		$value=$data[$segment][1];
		$coords=implode(",",$points);
		set_array($_RESULT["map"],"area",array("shape"=>"poly","coords"=>$coords,"value"=>$value));
	}
	// MAKE PLOT
	$plot=new PHPlot_truecolor($width,$height);
	$plot->SetFailureImage(false);
	$plot->SetPrintImage(false);
	$plot->SetImageBorderType("plain");
	capture_next_error();
	$plot->SetDataValues($values);
	$graph=get_clear_error()?"error":$graph;
	$font="lib/fonts/DejaVuSans.ttf";
	$font=realpath($font); // NEEDED BY HHVM
	$plot->SetDefaultTTFont($font);
	$plot->SetBgImage("img/defplot.png","centeredtile");
	$plot->SetFailureImage(false);
	// SET THE SIZES OF ALL FONTS
	$elems=array("generic","title","legend","x_label","y_label","x_title","y_title");
	$sizes=array(7,8,7,6,6,7,7);
	foreach($elems as $key=>$elem) $plot->SetFont($elem,"",$sizes[$key]);
	// CALC THE COLORS TO PLOT CURRENT DATA
	$plot->setRGBArray($ColorArray);
	if(count($colors)>0) {
		$intervals=($graph=="pie")?$count:$vars;
		if(count($colors)==2 && $intervals>2) {
			$color1=isset($ColorArray[$colors[0]])?$colors[0]:"white";
			$color2=isset($ColorArray[$colors[1]])?$colors[1]:"black";
			$color1=$ColorArray[$color1];
			$color2=$ColorArray[$color2];
			$colors=color_range($color1,$color2,$intervals);
		}
		$plot->SetDataColors($colors);
	}
	// FOR BARS PLOT
	if($graph=="bars") {
		$plot->SetPlotType("bars");
		$plot->SetDataType($datatype);
		$plot->SetCallback("data_points","__phplot_callback_for_bars",$values);
		$plot->SetTitle($title);
		if(isset($legend[0]) && $legend[0]!="") {
			$plot->SetLegend($legend);
			list($width2,$height2)=$plot->GetLegendSize();
			$plot->SetMarginsPixels(NULL,$width2+10,NULL,NULL);
			$plot->SetLegendPosition(0,0.5,"plot",1,0.5,5,0);
		}
		$plot->SetPlotAreaWorld(NULL,0,NULL,NULL);
		$plot->SetYLabelType("data");
		$plot->SetPrecisionY($precision);
		$plot->SetYDataLabelPos("plotin");
		$plot->SetXTickLabelPos("none");
		$plot->SetXTickPos("none");
		$plot->SetXDataLabelAngle(45);
	}
	// FOR POINTS PLOT
	if($graph=="points") {
		$plot->SetPlotType("linepoints");
		$plot->SetDataType($datatype);
		$plot->SetCallback("data_points","__phplot_callback_for_points",$values);
		$plot->SetTitle($title);
		if(isset($legend[0]) && $legend[0]!="") {
			$plot->SetLegend($legend);
			list($width2,$height2)=$plot->GetLegendSize();
			$plot->SetMarginsPixels(NULL,$width2+10,NULL,NULL);
			$plot->SetLegendPosition(0,0.5,"plot",1,0.5,5,0);
		}
		if($minvalue<$maxvalue) $plot->SetPlotAreaWorld(NULL,$minvalue,NULL,$maxvalue);
		$plot->SetYLabelType("data");
		$plot->SetPrecisionY($precision);
		$plot->SetYDataLabelPos("plotin");
		$plot->SetXTickLabelPos("none");
		$plot->SetXTickPos("none");
		$plot->SetXDataLabelAngle(45);
		$plot->SetLineWidths(2);
		$plot->SetLineStyles("solid");
	}
	// FOR PIE PLOT
	if($graph=="pie") {
		$plot->SetPlotType("pie");
		$plot->SetDataType("text-data-single");
		$plot->SetCallback("data_points","__phplot_callback_for_pie",$values);
		$plot->SetTitle($title);
		foreach($values as $row) $plot->SetLegend($row[0]);
		list($width2,$height2)=$plot->GetLegendSize();
		$plot->SetMarginsPixels(NULL,$width2+10,NULL,NULL);
		$plot->SetLegendPosition(0,0.5,"plot",1,0.5,0,0);
	}
	// FOR LINES PLOT
	if($graph=="lines") {
		$plot->SetPlotType("lines");
		$plot->SetDataType($datatype);
		$plot->SetTitle($title);
		if(isset($legend[0]) && $legend[0]!="") {
			$plot->SetLegend($legend);
			list($width2,$height2)=$plot->GetLegendSize();
			$plot->SetMarginsPixels(NULL,$width2+10,NULL,NULL);
			$plot->SetLegendPosition(0,0.5,"plot",1,0.5,5,0);
		}
		if($minvalue<$maxvalue) $plot->SetPlotAreaWorld(NULL,$minvalue,NULL,$maxvalue);
		$plot->SetYLabelType("data");
		$plot->SetPrecisionY($precision);
		$plot->SetYDataLabelPos("none");
		$plot->SetXTickLabelPos("none");
		$plot->SetXTickPos("none");
		$plot->SetXDataLabelAngle(45);
		$plot->SetLineWidths(2);
		$plot->SetLineStyles("solid");
	}
	// FOR FINANCIAL PLOT
	if($graph=="ohlc") {
		$plot->SetPlotType("candlesticks2");
		$plot->SetDataType($datatype);
		$plot->SetTitle($title);
		if(isset($legend[0]) && $legend[0]!="") {
			$plot->SetLegend($legend);
			list($width2,$height2)=$plot->GetLegendSize();
			$plot->SetMarginsPixels(NULL,$width2+10,NULL,NULL);
			$plot->SetLegendPosition(0,0.5,"plot",1,0.5,5,0);
		}
		$plot->SetDataColors(array("red","DarkGreen","red","DarkGreen"));
		if($minvalue<$maxvalue) $plot->SetPlotAreaWorld(NULL,$minvalue,NULL,$maxvalue);
		$plot->SetYLabelType("data");
		$plot->SetPrecisionY($precision);
		$plot->SetYDataLabelPos("plotin");
		$plot->SetXTickLabelPos("none");
		$plot->SetXTickPos("none");
		$plot->SetXDataLabelAngle(45);
	}
	// MAKE THE IMAGE
	$_RESULT=array("img"=>"","map"=>array());
	if($graph!="error") {
		capture_next_error();
		$plot->DrawGraph();
		if(get_clear_error()) $graph="error";
	}
	if($graph=="error") {
		$plot->SetFont("generic","",10);
		$options=array("draw_background"=>true,"draw_border"=>true,"force_print"=>false,"reset_font"=>false);
		$plot->DrawMessage(LANG("withoutinfo"),$options);
	}
	// MAKE XML
	$_RESULT["img"]=$plot->EncodeImage();
	//$plot->PrintImage(); die();
	$buffer="<?xml version='1.0' encoding='UTF-8' ?>\n";
	$buffer.=array2xml($_RESULT);
	file_put_contents($cache,$buffer);
	chmod_protected($cache,0666);
}
output_file($cache);
?>