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
function __unoconv_pre($array) {
	if(isset($array["input"])) {
		$input=$array["input"];
	} elseif(isset($array["data"]) && isset($array["ext"])) {
		$input=get_temp_file($array["ext"]);
		file_put_contents($input,$array["data"]);
	} else {
		show_php_error(array("phperror"=>"Call to unoconv without valid input"));
	}
	if(isset($array["output"])) {
		$output=$array["output"];
	} else {
		$output=get_temp_file(getDefault("exts/outputext",".out"));
	}
	$type=saltos_content_type($input);
	$ext=strtolower(extension($input));
	$type0=strtok($type,"/");
	return array($input,$output,$type,$ext,$type0);
}

function __unoconv_post($array,$input,$output) {
	if(!isset($array["input"])) {
		unlink($input);
	}
	if(!isset($array["output"]) && file_exists($output)) {
		$result=file_get_contents($output);
		unlink($output);
	} else {
		$result="";
	}
	return $result;
}

function __unoconv_list() {
	if(!check_commands(getDefault("commands/unoconv"),60)) return array();
	$abouts=ob_passthru(getDefault("commands/unoconv")." ".getDefault("commands/__unoconv_about__"),60);
	$abouts=explode("\n",$abouts);
	$exts=array();
	foreach($abouts as $about) {
		$pos1=strpos($about,"[");
		$pos2=strpos($about,"]");
		if($pos1!==false && $pos2!==false) {
			$ext=substr($about,$pos1+1,$pos2-$pos1-1);
			if($ext[0]==".") $ext=substr($ext,1);
			if(!in_array($ext,$exts)) $exts[]=$ext;
		}
	}
	return $exts;
}

function unoconv2pdf($array) {
	list($input,$output,$type,$ext,$type0)=__unoconv_pre($array);
	if($type=="application/pdf") {
		copy($input,$output);
	} elseif((in_array($ext,__unoconv_list()) && !in_array($type,array("audio","video"))) || in_array($type0,array("text","message"))) {
		if(check_commands(getDefault("commands/unoconv"),60)) {
			$cmd=getDefault("commands/unoconv")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($input,$output),getDefault("commands/__unoconv__"));
			if(check_commands(getDefault("commands/timeout"),60)) {
				$cmd=getDefault("commands/timeout")." ".str_replace(array("__TIMEOUT__","__COMMAND__"),array(getDefault("commandtimeout",60),$cmd),getDefault("commands/__timeout__"));
			}
			ob_passthru($cmd);
		}
	}
	return __unoconv_post($array,$input,$output);
}

function unoconv2txt($array) {
	list($input,$output,$type,$ext,$type0)=__unoconv_pre($array);
	if($type=="text/plain") {
		copy($input,$output);
	} elseif($type=="text/html") {
		file_put_contents($output,html2text(file_get_contents($input)));
	} elseif($type=="application/pdf") {
		if(check_commands(getDefault("commands/pdftotext"),60)) {
			ob_passthru(getDefault("commands/pdftotext")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($input,$output),getDefault("commands/__pdftotext__")));
		}
	} elseif((in_array($ext,__unoconv_list()) && !in_array($type0,array("image","audio","video"))) || in_array($type0,array("text","message"))) {
		if(check_commands(array(getDefault("commands/unoconv"),getDefault("commands/pdftotext")),60)) {
			$temp=get_temp_file(getDefault("exts/pdfext",".pdf"));
			$cmd=getDefault("commands/unoconv")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($input,$temp),getDefault("commands/__unoconv__"));
			if(check_commands(getDefault("commands/timeout"),60)) {
				$cmd=getDefault("commands/timeout")." ".str_replace(array("__TIMEOUT__","__COMMAND__"),array(getDefault("commandtimeout",60),$cmd),getDefault("commands/__timeout__"));
			}
			ob_passthru($cmd);
			if(file_exists($temp)) {
				ob_passthru(getDefault("commands/pdftotext")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($temp,$output),getDefault("commands/__pdftotext__")));
				unlink($temp);
			}
		}
	}
	if(file_exists($output)) {
		$temp=file_get_contents($output);
		$temp=getutf8($temp);
		file_put_contents($output,$temp);
	}
	return __unoconv_post($array,$input,$output);
}

function __unoconv_img2ocr($file) {
	if(!check_commands(getDefault("commands/tesseract"),60)) return "";
	$hocr=str_replace(getDefault("exts/tiffext",".tif"),getDefault("exts/hocrext",".html"),$file);
	$tmp=str_replace(getDefault("exts/tiffext",".tif"),"",$file);
	if(!file_exists($hocr)) {
		$cmd=getDefault("commands/tesseract")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($file,$tmp),getDefault("commands/__tesseract__"));
		if(check_commands(getDefault("commands/timeout"),60)) {
			$cmd=getDefault("commands/timeout")." ".str_replace(array("__TIMEOUT__","__COMMAND__"),array(getDefault("commandtimeout",60),$cmd),getDefault("commands/__timeout__"));
		}
		ob_passthru($cmd);
	}
	$txt=str_replace(getDefault("exts/tiffext",".tif"),getDefault("exts/textext",".txt"),$file);
	//~ if(file_exists($txt)) unlink($txt);
	if(!file_exists($txt)) __unoconv_hocr2txt($hocr,$txt);
	return $txt;
}

function __unoconv_pdf2ocr($pdf) {
	if(!check_commands(array(getDefault("commands/pdfimages"),getDefault("commands/convert")),60)) return "";
	$result=array();
	// EXTRACT ALL IMAGES FROM PDF
	$root=get_directory("dirs/cachedir").md5_file($pdf);
	$files=glob("${root}-*");
	if(!count($files)) ob_passthru(getDefault("commands/pdfimages")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($pdf,$root),getDefault("commands/__pdfimages__")));
	// CONVERT ALL IMAGES TO TIFF
	$files=glob("${root}-*");
	foreach($files as $file) {
		$tif=str_replace(".".extension($file),getDefault("exts/tiffext",".tif"),$file);
		if(!file_exists($tif)) {
			if(!isset($width) && !isset($height)) {
				// GET SIZES OF THE PDF
				$size=ob_passthru(getDefault("commands/convert")." ".str_replace(array("__INPUT__"),array($pdf),getDefault("commands/__convert_getsize__")));
				list($width,$height)=explode(" ",$size);
				$zoom=4.16; // EQUIVALENT TO 300DPI APROX
				$width=intval($width*$zoom);
				$height=intval($height*$zoom);
			}
			// CONTINUE
			ob_passthru(getDefault("commands/convert")." ".str_replace(array("__INPUT__","__WIDTH__","__HEIGHT__","__ANGLE__","__OUTPUT__"),array($file,$width,$height,0,$tif),getDefault("commands/__convert__")));
		}
	}
	// EXTRACT ALL TEXT FROM TIFF
	$files=glob("${root}-*.tif");
	foreach($files as $file) {
		$txt=__unoconv_img2ocr($file);
		$result[]=file_get_contents($txt);
	}
	$result=implode("\n",$result);
	return $result;
}

function __unoconv_histogram($values,$usage) {
	$histo=array();
	foreach($values as $val) {
		$val=intval($val);
		if(!isset($histo[$val])) $histo[$val]=0;
		$histo[$val]++;
	}
	$suma=array_sum($histo);
	$percent=1;
	$incr=pow(10,-round(log($suma,10),0));
	for(;;) {
		$value=0;
		$total=0;
		foreach($histo as $key=>$val) {
			if($val>=$suma*$percent) {
				$value+=$key*$val;
				$total+=$val;
			}
		}
		if($total>=$suma*$usage) break;
		$percent-=$incr;
		if($percent<0) break;
	};
	$value/=$total;
	return $value;
}

function __unoconv_rotate($posx,$posy,$angle) {
	$ang=rad2deg(atan($posy/$posx));
	$mod=sqrt($posx*$posx+$posy*$posy);
	$ang=deg2rad($ang+$angle);
	$posx=$mod*cos($ang);
	$posy=$mod*sin($ang);
	return array($posx,$posy);
}

function __unoconv_node2attr($node) {
	if(strpos($node["#attr"]["title"],"; ")!==false) {
		$temp=explode("; ",$node["#attr"]["title"]);
		foreach($temp as $temp2) if(substr($temp2,0,4)=="bbox") $node["#attr"]["title"]=$temp2;
	}
	$temp=explode("_",$node["#attr"]["id"]);
	$node["#attr"]["id"]=$temp[0];
	$temp=array_merge(array($node["#attr"]["id"]),array_slice(explode(" ",$node["#attr"]["title"]),1));
	return $temp;
}

function __unoconv_node2value($node) {
	while(is_array($node["value"])) $node["value"]=array_pop($node["value"]);
	$node["value"]=trim($node["value"]);
	return $node["value"];
}

function __unoconv_hocr2txt($hocr,$txt) {
	require_once("php/import.php");
	// LOAD XML
	$array=__import_xml2array($hocr);
	$array=__import_getnode("html/body",$array);
	// PARTE XML
	$lines=array();
	foreach($array as $page) {
		$lines[]=__unoconv_node2attr($page);
		foreach($page["value"] as $block) {
			$lines[]=__unoconv_node2attr($block);
			foreach($block["value"] as $par) {
				$lines[]=__unoconv_node2attr($par);
				foreach($par["value"] as $line) {
					$lines[]=__unoconv_node2attr($line);
					foreach($line["value"] as $word) {
						$lines[]=array_merge(__unoconv_node2attr($word),array(__unoconv_node2value($word)));
					}
				}
			}
		}
	}
	// COMPUTE ANGLE
	$pos1=array(($lines[0][3]+$lines[0][1])/2,($lines[0][4]+$lines[0][2])/2);
	$angles=array();
	foreach($lines as $line) {
		if($line[0]=="word" && $line[5]!="") {
			$pos2=array(($line[3]+$line[1])/2,($line[4]+$line[2])/2);
			$incrx=$pos2[0]-$pos1[0];
			$incry=$pos2[1]-$pos1[1];
			if($incrx>0) $angles[]=rad2deg(atan($incry/$incrx));
			$pos1=$pos2;
		}
	}
	$angle=__unoconv_histogram($angles,0.5);
	// APPLY ANGLE CORRECTION
	foreach($lines as $index=>$line) {
		if($line[1]!=0 && $line[2]!=0) list($line[1],$line[2])=__unoconv_rotate($line[1],$line[2],-$angle);
		if($line[3]!=0 && $line[4]!=0) list($line[3],$line[4])=__unoconv_rotate($line[3],$line[4],-$angle);
		$lines[$index]=$line;
	}
	// COMPUTE SIZE
	$sizes=array();
	foreach($lines as $line) {
		if($line[0]=="line") {
			$posy=($line[4]+$line[2])/2;
			if(!isset($sizes[$posy])) $sizes[$posy]=array(0,0,0,0,0);
			$sizes[$posy][1]=min($sizes[$posy][1],$line[2]);
			$sizes[$posy][3]=max($sizes[$posy][3],$line[4]);
		}
		if($line[0]=="word" && $line[5]!="") {
			if($sizes[$posy][0]==0) $sizes[$posy][0]=$line[1];
			if($sizes[$posy][1]==0) $sizes[$posy][1]=$line[2];
			$sizes[$posy][0]=min($sizes[$posy][0],$line[1]);
			$sizes[$posy][2]=max($sizes[$posy][2],$line[3]);
			$sizes[$posy][4]+=mb_strlen($line[5],"UTF-8");
		}
	}
	$widths=array();
	$heights=array();
	foreach($sizes as $key=>$val) {
		if($val[4]!=0) {
			$incrx=($val[2]-$val[0])/$val[4];
			$incry=$val[3]-$val[1];
			for($i=0;$i<$val[4];$i++) {
				$widths[]=$incrx;
				$heights[]=$incry;
			}
		}
	}
	$width=__unoconv_histogram($widths,0.5)*0.9;
	$height=__unoconv_histogram($heights,0.5)*0.9;
	// COMPUTE MATRIX
	$matrix=array();
	foreach($lines as $line) {
		if($line[0]=="line") {
			$posy=round((($line[4]+$line[2])/2)/$height,0);
			if(!isset($matrix[$posy])) $matrix[$posy]=array();
		}
		if($line[0]=="word") {
			$posx=round($line[1]/$width,0);
			if(!isset($matrix[$posy][$posx])) $matrix[$posy][$posx]="";
			if($line[5]=="") $line[5]="~"; // AS MAKEBOX FEATURE
			$matrix[$posy][$posx].=$line[5];
		}
	}
	// MAKE OUTPUT
	$buffer=array();
	$minx=round($lines[0][1]/$width,0);
	$maxx=round($lines[0][3]/$width,0);
	$miny=round($lines[0][2]/$height,0);
	$maxy=round($lines[0][4]/$height,0);
	for($y=$miny;$y<=$maxy;$y++) {
		$temp=array();
		$extra=0;
		for($x=$minx;$x<=$maxx;$x++) {
			if(isset($matrix[$y][$x])) {
				$temp[]=$matrix[$y][$x];
				$extra+=max(mb_strlen($matrix[$y][$x],"UTF-8")-1,0);
			} elseif($extra>0) {
				$extra--;
			} else {
				$temp[]=" ";
			}
		}
		$buffer[]=implode("",$temp);
	}
	$buffer=implode("\n",$buffer)."\n";
	// WRITE TO FILE
	file_put_contents($txt,$buffer);
}
?>