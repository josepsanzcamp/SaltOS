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

function __unoconv_pdf2txt($input,$output) {
	if(!check_commands(getDefault("commands/pdftotext"),60)) return;
	ob_passthru(getDefault("commands/pdftotext")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($input,$output),getDefault("commands/__pdftotext__")));
}

function __unoconv_all2pdf($input,$output) {
	if(!check_commands(getDefault("commands/unoconv"),60)) return;
	$cmd=getDefault("commands/unoconv")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($input,$output),getDefault("commands/__unoconv__"));
	if(check_commands(getDefault("commands/timeout"),60)) {
		$cmd=getDefault("commands/timeout")." ".str_replace(array("__TIMEOUT__","__COMMAND__"),array(getDefault("commandtimeout",60),$cmd),getDefault("commands/__timeout__"));
	}
	ob_passthru($cmd);
}

function unoconv2pdf($array) {
	list($input,$output,$type,$ext,$type0)=__unoconv_pre($array);
	if($type=="application/pdf") {
		copy($input,$output);
	} elseif((in_array($ext,__unoconv_list()) && !in_array($type0,array("audio","video"))) || in_array($type0,array("text","message"))) {
		__unoconv_all2pdf($input,$output);
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
		__unoconv_pdf2txt($input,$output);
		if(!file_exists($output) || trim(file_get_contents($output))=="") {
			file_put_contents($output,__unoconv_pdf2ocr($input));
		}
	} elseif((in_array($ext,__unoconv_list()) && !in_array($type0,array("image","audio","video"))) || in_array($type0,array("text","message"))) {
		$temp=get_temp_file(getDefault("exts/pdfext",".pdf"));
		__unoconv_all2pdf($input,$temp);
		if(file_exists($temp)) {
			__unoconv_pdf2txt($temp,$output);
			unlink($temp);
		}
	} elseif($type0=="image") {
		file_put_contents($output,__unoconv_img2ocr($input));
	}
	if(file_exists($output)) {
		file_put_contents($output,getutf8(file_get_contents($output)));
	}
	return __unoconv_post($array,$input,$output);
}

function __unoconv_img2ocr($file) {
	if(!check_commands(getDefault("commands/convert"),60)) return "";
	$type=saltos_content_type($file);
	if($type!="image/tiff") {
		$tif=get_cache_file(array($file,0),getDefault("exts/tiffext",".tif"));
		if(!file_exists($tif)) {
			ob_passthru(getDefault("commands/convert")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($file,$tif),getDefault("commands/__convert__")));
		}
		$file=$tif;
	}
	$result=__unoconv_tif2ocr($file);
	if($result=="") {
		foreach(array(90,270) as $angle) {
			$file2=get_cache_file(array($file,$angle),getDefault("exts/tiffext",".tif"));
			if(!file_exists($file2)) {
				ob_passthru(getDefault("commands/convert")." ".str_replace(array("__INPUT__","__ANGLE__","__OUTPUT__"),array($file,$angle,$file2),getDefault("commands/__convert_rotate__")));
			}
			$result2=__unoconv_tif2ocr($file2);
			if($result2!="") {
				if($result=="") $result=$result2;
				elseif(mb_strlen($result2,"UTF-8")<mb_strlen($result,"UTF-8")) $result=$result2;
			}
		}
	}
	return $result;
}

function __unoconv_tif2ocr($file) {
	if(!check_commands(getDefault("commands/tesseract"),60)) return "";
	$hocr=get_cache_file($file,getDefault("exts/hocrext",".html"));
	//~ if(file_exists($hocr)) unlink($hocr);
	if(!file_exists($hocr)) {
		$tmp=str_replace(getDefault("exts/hocrext",".html"),"",$hocr);
		$cmd=getDefault("commands/tesseract")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($file,$tmp),getDefault("commands/__tesseract__"));
		if(check_commands(getDefault("commands/timeout"),60)) {
			$cmd=getDefault("commands/timeout")." ".str_replace(array("__TIMEOUT__","__COMMAND__"),array(getDefault("commandtimeout",60),$cmd),getDefault("commands/__timeout__"));
		}
		ob_passthru($cmd);
		if(!file_exists($hocr)) return "";
	}
	$txt=str_replace(getDefault("exts/hocrext",".html"),getDefault("exts/textext",".txt"),$hocr);
	//~ if(file_exists($txt)) unlink($txt);
	if(!file_exists($txt)) file_put_contents($txt,__unoconv_hocr2txt($hocr,$txt));
	return file_get_contents($txt);
}

function __unoconv_pdf2ocr($pdf) {
	if(!check_commands(array(getDefault("commands/pdftoppm"),getDefault("commands/convert")),60)) return "";
	$result=array();
	// EXTRACT ALL IMAGES FROM PDF
	$root=get_directory("dirs/cachedir").md5_file($pdf);
	$files=glob("${root}-*");
	if(!count($files)) ob_passthru(getDefault("commands/pdftoppm")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($pdf,$root),getDefault("commands/__pdftoppm__")));
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
			ob_passthru(getDefault("commands/convert")." ".str_replace(array("__INPUT__","__WIDTH__","__HEIGHT__","__OUTPUT__"),array($file,$width,$height,$tif),getDefault("commands/__convert_resize__")));
		}
	}
	// EXTRACT ALL TEXT FROM TIFF
	$files=glob("${root}-*".getDefault("exts/tiffext",".tif"));
	foreach($files as $file) {
		$result[]=__unoconv_img2ocr($file);
	}
	$result=implode("\n\n",$result);
	return $result;
}

function __unoconv_histogram($values,$usage1,$usage2) {
	$histo=array();
	foreach($values as $val) {
		$val=round($val,0);
		if(!isset($histo[$val])) $histo[$val]=0;
		$histo[$val]++;
	}
	//~ echo "<pre>";
	//~ arsort($histo);
	//~ print_r($histo);
	//~ echo "</pre>";
	//~ die();
	$count1=count($values);
	$count2=count($histo);
	$percent=1;
	$incr=0.01;
	for(;;) {
		$value=0;
		$total=0;
		$used=0;
		foreach($histo as $key=>$val) {
			if($val>=$count1*$percent) {
				$value+=$key*$val;
				$total+=$val;
				$used++;
			}
		}
		if($total>=$count1*$usage1 && $used>=$count2*$usage2) break;
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

function __unoconv_lines2matrix($lines,$width,$height) {
	$matrix=array();
	foreach($lines as $index=>$line) {
		if($line[0]=="line") {
			$posy=round((($line[4]+$line[2])/2)/$height,0);
			if(!isset($matrix[$posy])) $matrix[$posy]=array();
		}
		if($line[0]=="word") {
			if($line[5]=="") $line[5]="~"; // AS MAKEBOX FEATURE
			$len=mb_strlen($line[5],"UTF-8");
			$bias=($line[3]-$line[1])/($len*2);
			$posx=round(($line[1]+$bias)/$width,0);
			for($i=0;$i<$len;$i++) {
				if(isset($matrix[$posy][$posx])) return $index;
				$matrix[$posy][$posx]=mb_substr($line[5],$i,1,"UTF-8");
				$posx++;
			}
		}
	}
	return $matrix;
}

function __unoconv_hocr2txt($hocr) {
	require_once("php/import.php");
	// LOAD XML
	$array=__import_xml2array($hocr);
	$array=__import_getnode("html/body",$array);
	// PARTE XML
	$lines=array();
	$words=0;
	if(is_array($array)) {
		foreach($array as $page) {
			$lines[]=__unoconv_node2attr($page);
			if(is_array($page["value"])) {
				foreach($page["value"] as $block) {
					$lines[]=__unoconv_node2attr($block);
					if(is_array($block["value"])) {
						foreach($block["value"] as $par) {
							$lines[]=__unoconv_node2attr($par);
							if(is_array($par["value"])) {
								foreach($par["value"] as $line) {
									$lines[]=__unoconv_node2attr($line);
									if(is_array($line["value"])) {
										foreach($line["value"] as $word) {
											$lines[]=array_merge(__unoconv_node2attr($word),array(__unoconv_node2value($word)));
											$words++;
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
	if($words<1) return "";
	//~ echo "<pre>".sprintr($lines)."</pre>";
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
	$angle=count($angles)?__unoconv_histogram($angles,0.5,0.5):0;
	// APPLY ANGLE CORRECTION
	foreach($lines as $index=>$line) {
		if($line[1]!=0 && $line[2]!=0) list($line[1],$line[2])=__unoconv_rotate($line[1],$line[2],-$angle);
		if($line[3]!=0 && $line[4]!=0) list($line[3],$line[4])=__unoconv_rotate($line[3],$line[4],-$angle);
		$lines[$index]=$line;
	}
	// COMPUTE MATRIX
	$matrix=null;
	for($size=10;$size<1000;$size+=10) {
		$width=($lines[0][3]-$lines[0][1])/$size;
		$height=($lines[0][4]-$lines[0][2])/$size;
		$matrix=__unoconv_lines2matrix($lines,$width,$height);
		if(is_array($matrix)) break;
	}
	//~ echo "<pre>".sprintr(array($size,$width,$height))."</pre>";
	if(!is_array($matrix)) return "";
	// MAKE OUTPUT
	$buffer=array();
	$minx=round($lines[0][1]/$width,0);
	$maxx=round($lines[0][3]/$width,0);
	$miny=round($lines[0][2]/$height,0);
	$maxy=round($lines[0][4]/$height,0);
	for($y=$miny;$y<=$maxy;$y++) {
		$temp=array();
		for($x=$minx;$x<=$maxx;$x++) {
			$temp[]=isset($matrix[$y][$x])?$matrix[$y][$x]:" ";
		}
		$buffer[]=implode("",$temp);
	}
	$buffer=implode("\n",$buffer);
	return $buffer;
}
?>