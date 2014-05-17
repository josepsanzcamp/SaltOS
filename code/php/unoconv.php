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
function unoconv2pdf($input) {
	$input=fix_file($input);
	$output=get_cache_file($input,getDefault("exts/pdfext",".pdf"));
	if(!file_exists($output)) {
		$type=saltos_content_type($input);
		$ext=strtolower(extension($input));
		$type0=saltos_content_type0($type);
		if($type=="application/pdf") {
			copy($input,$output);
		} elseif((in_array($ext,__unoconv_list()) && !in_array($type0,array("audio","video"))) || in_array($type0,array("text","message"))) {
			__unoconv_all2pdf($input,$output);
		}
		if(!file_exists($output)) {
			file_put_contents($output,"");
		}
	}
	return file_get_contents($output);
}

function unoconv2txt($input) {
	$input=fix_file($input);
	$output=get_cache_file($input,getDefault("exts/textext",".txt"));
	if(!file_exists($output)) {
		$type=saltos_content_type($input);
		$ext=strtolower(extension($input));
		$type0=saltos_content_type0($type);
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
			$pdf=get_cache_file($input,getDefault("exts/pdfext",".pdf"));
			if(!file_exists($pdf)) {
				__unoconv_all2pdf($input,$pdf);
			}
			if(file_exists($pdf)) {
				__unoconv_pdf2txt($pdf,$output);
			}
		} elseif($type0=="image") {
			file_put_contents($output,__unoconv_img2ocr($input));
		}
		if(!file_exists($output)) {
			file_put_contents($output,"");
		} else {
			file_put_contents($output,getutf8(file_get_contents($output)));
		}
	}
	return file_get_contents($output);
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
	$input=fix_file($input);
	if(!check_commands(getDefault("commands/pdftotext"),60)) return;
	ob_passthru(getDefault("commands/pdftotext")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($input,$output),getDefault("commands/__pdftotext__")));
}

function __unoconv_all2pdf($input,$output) {
	$input=fix_file($input);
	if(!check_commands(getDefault("commands/unoconv"),60)) return;
	ob_passthru(__unoconv_timeout(getDefault("commands/unoconv")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($input,$output),getDefault("commands/__unoconv__"))));
}

function __unoconv_timeout($cmd) {
	if(check_commands(getDefault("commands/timeout"),60)) {
		$cmd=getDefault("commands/timeout")." ".str_replace(array("__TIMEOUT__","__COMMAND__"),array(getDefault("commandtimeout",60),$cmd),getDefault("commands/__timeout__"));
	}
	return $cmd;
}

function __unoconv_img2ocr($file) {
	if(!check_commands(array(getDefault("commands/convert"),getDefault("commands/tesseract")),60)) return "";
	$file=fix_file($file);
	$type=saltos_content_type($file);
	if($type!="image/tiff") {
		$tiff=get_cache_file($file,getDefault("exts/tiffext",".tif"));
		//~ if(file_exists($tiff)) unlink($tiff);
		if(!file_exists($tiff)) {
			ob_passthru(getDefault("commands/convert")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($file,$tiff),getDefault("commands/__convert__")));
			if(!file_exists($tiff)) return "";
		}
		$file=$tiff;
	}
	$hocr=get_cache_file($file,getDefault("exts/hocrext",".html"));
	//~ if(file_exists($hocr)) unlink($hocr);
	if(!file_exists($hocr)) {
		$tmp=str_replace(getDefault("exts/hocrext",".html"),"",$hocr);
		ob_passthru(__unoconv_timeout(getDefault("commands/tesseract")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($file,$tmp),getDefault("commands/__tesseract__"))));
	}
	if(isset($tiff)) file_put_contents($tiff,"");
	if(!file_exists($hocr)) return "";
	$txt=str_replace(getDefault("exts/hocrext",".html"),getDefault("exts/textext",".txt"),$hocr);
	if(!file_exists($txt)) file_put_contents($txt,__unoconv_hocr2txt($hocr));
	return file_get_contents($txt);
}

function __unoconv_pdf2ocr($pdf) {
	if(!check_commands(getDefault("commands/pdftoppm"),60)) return "";
	// EXTRACT ALL IMAGES FROM PDF
	$pdf=fix_file($pdf);
	$root=get_directory("dirs/cachedir").md5_file($pdf);
	$files=glob("${root}-*");
	if(!count($files)) ob_passthru(getDefault("commands/pdftoppm")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($pdf,$root),getDefault("commands/__pdftoppm__")));
	// EXTRACT ALL TEXT FROM TIFF
	$files=glob("${root}-*");
	$result=array();
	foreach($files as $file) {
		$result[]=__unoconv_img2ocr($file);
		file_put_contents($file,"");
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
	$ang=rad2deg(atan2($posy,$posx));
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
			if($line[5]=="") {
				// AS MAKEBOX FEATURE
				$bias=($line[3]-$line[1])/2;
				$posx=round(($line[1]+$bias)/$width,0);
				if(!isset($matrix[$posy][$posx])) $matrix[$posy][$posx]="~";
			} else {
				// AS DEFAULT FEATURE
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
	}
	return $matrix;
}

function __unoconv_fixline($line,$pos1,$pos2,$pos3,$pos4) {
	$temp=$line;
	$line[1]=$temp[$pos1];
	$line[2]=$temp[$pos2];
	$line[3]=$temp[$pos3];
	$line[4]=$temp[$pos4];
	return $line;
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
	$angles=array();
	foreach($lines as $line) {
		if($line[0]=="line") {
			$pos1=null;
		}
		if($line[0]=="word") {
			$pos2=array(($line[3]+$line[1])/2,($line[4]+$line[2])/2);
			if(is_array($pos1)) {
				$incrx=$pos2[0]-$pos1[0];
				$incry=$pos2[1]-$pos1[1];
				$angles[]=rad2deg(atan2($incry,$incrx));
			}
			$pos1=$pos2;
		}
	}
	$angle=count($angles)?__unoconv_histogram($angles,0.25,0):0;
	//~ echo "<pre>".sprintr(array($angle))."</pre>";
	// APPLY ANGLE CORRECTION
	foreach($lines as $index=>$line) {
		if($line[1]!=0 && $line[2]!=0) list($line[1],$line[2])=__unoconv_rotate($line[1],$line[2],-$angle);
		if($line[3]!=0 && $line[4]!=0) list($line[3],$line[4])=__unoconv_rotate($line[3],$line[4],-$angle);
		if($index==0) {
			$incrx=$line[3]-$line[1];
			$incry=$line[4]-$line[2];
			if($incrx>=0 && $incry>=0) $quadrant=0;
			elseif($incrx>=0 && $incry<0) $quadrant=1;
			elseif($incrx<0 && $incry<0) $quadrant=2;
			elseif($incrx<0 && $incry>=0) $quadrant=3;
			//~ echo "<pre>".sprintr(array($incrx,$incry,$quadrant))."</pre>";
		}
		if($quadrant==1) $line=__unoconv_fixline($line,1,4,3,2);
		elseif($quadrant==2) $line=__unoconv_fixline($line,3,4,1,2);
		elseif($quadrant==3) $line=__unoconv_fixline($line,3,2,1,4);
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