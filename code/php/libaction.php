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
function __cache_resolve_path($buffer,$file) {
	// RESOLVE FULL PATH FOR ALL BACKGROUNDS IMAGES
	$dirname_file=dirname($file)."/";
	$pos=strpos($buffer,"url(");
	while($pos!==false) {
		$pos2=strpos($buffer,")",$pos+4);
		$img=substr($buffer,$pos+4,$pos2-$pos-4);
		if(in_array(substr($img,0,1),array("'",'"'))) $img=substr($img,1);
		if(in_array(substr($img,-1,1),array("'",'"'))) $img=substr($img,0,-1);
		$newimg=semi_realpath($dirname_file.$img);
		if(file_exists($newimg)) {
			$buffer=substr_replace($buffer,$newimg,$pos+4,$pos2-$pos-4);
			$pos2=$pos2-strlen($img)+strlen($newimg);
		}
		$pos=strpos($buffer,"url(",$pos2+1);
	}
	return $buffer;
}

function __captcha_color2dec($color,$component) {
	$offset=array("R"=>0,"G"=>2,"B"=>4);
	if(!isset($offset[$component])) show_php_error(array("phperror"=>"Unknown component"));
	return hexdec(substr($color,$offset[$component],2));
}

function __captcha_isprime($num) {
	// SEE www.polprimos.com FOR UNDERSTAND IT
	if($num<2) return false;
	if($num%2==0 && $num!=2) return false;
	if($num%3==0 && $num!=3) return false;
	if($num%5==0 && $num!=5) return false;
	// PRIMER NUMBERS ARE DISTRIBUTED IN 8 COLUMNS
	$div=7;
	$max=intval(sqrt($num));
	while(1) {
		if($num%$div==0 && $num!=$div) return false;
		if($div>=$max) break;
		$div+=4;
		if($num%$div==0 && $num!=$div) return false;
		if($div>=$max) break;
		$div+=2;
		if($num%$div==0 && $num!=$div) return false;
		if($div>=$max) break;
		$div+=4;
		if($num%$div==0 && $num!=$div) return false;
		if($div>=$max) break;
		$div+=2;
		if($num%$div==0 && $num!=$div) return false;
		if($div>=$max) break;
		$div+=4;
		if($num%$div==0 && $num!=$div) return false;
		if($div>=$max) break;
		$div+=6;
		if($num%$div==0 && $num!=$div) return false;
		if($div>=$max) break;
		$div+=2;
		if($num%$div==0 && $num!=$div) return false;
		if($div>=$max) break;
		$div+=6;
	}
	return true;
}

function __excel_dump($query,$page) {
	$result=db_query($query);
	$matrix=array(array());
	for($i=0;$i<db_num_fields($result);$i++) $matrix[0][]=db_field_name($result,$i);
	while($row=db_fetch_row($result)) $matrix[]=array_values($row);
	db_free($result);
	set_include_path("lib/phpexcel:".get_include_path());
	require_once("PHPExcel.php");
	$cacheMethod=PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
	$cacheSettings=array("memoryCacheSize"=>"8MB");
	PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);
	$objPHPExcel=new PHPExcel();
	$objPHPExcel->getProperties()->setCreator(get_name_version_revision());
	$objPHPExcel->getProperties()->setLastModifiedBy(current_datetime());
	$title=ucfirst($page);
	$objPHPExcel->getProperties()->setTitle($title);
	$objPHPExcel->getProperties()->setSubject($title);
	$objPHPExcel->getProperties()->setDescription($title);
	$objPHPExcel->getProperties()->setKeywords($title);
	$objPHPExcel->getProperties()->setCategory($title);
	//~ $objPHPExcel->createSheet();
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->fromArray($matrix,NULL,"A1");
	require_once("php/import.php");
	for($i=0;$i<count($matrix[0]);$i++) $objPHPExcel->getActiveSheet()->getColumnDimension(__import_col2name($i))->setAutoSize(true);
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

function __favicon_color2dec($color,$component) {
	$offset=array("R"=>0,"G"=>2,"B"=>4);
	if(!isset($offset[$component])) show_php_error(array("phperror"=>"Unknown component"));
	return hexdec(substr($color,$offset[$component],2));
}

function __favicon_color2aprox($fgcolor,$bgcolor) {
	$fgcolor=intval($fgcolor/85);
	$bgcolor=intval($bgcolor/85);
	$color=3;
	if($fgcolor==$color) $color--;
	if($bgcolor==$color) $color--;
	if($fgcolor==$color) $color--;
	$color=$color*85;
	return $color;
}

// FUNCTION THAT RETURNS THE META ATTRIBUTES
function __favoritos_explode_meta($html) {
	$result=array();
	$len=strlen($html);
	$pos1=strpos($html,"=");
	while($pos1!==false) {
		for($i=$pos1-1;$i>=0;$i--) if($html[$i]!=" ") break;
		for($j=$i;$j>=0;$j--) if($html[$j]==" ") break;
		$pos2=$j;
		for($i=$pos1+1;$i<$len;$i++) if($html[$i]!=" ") break;
		for($j=$i;$j<$len;$j++) if($html[$j]=='"' || $html[$j]=="'") break;
		$pos3=$j;
		for($k=$j+1;$k<$len;$k++) if($html[$j]==$html[$k]) break;
		$pos4=$k;
		$key=substr($html,$pos2+1,$pos1-$pos2-1);
		$val=substr($html,$pos3+1,$pos4-$pos3-1);
		$result[$key]=$val;
		$pos1=strpos($html,"=",$pos1+1);
	}
	return $result;
}

// FUNCTION THAT RETURNS ALL META TAGS
function __favoritos_get_metas($html) {
	$result=array();
	$pos1=stripos($html,"<meta");
	while($pos1!==false) {
		$pos2=stripos($html,">",$pos1);
		if($pos2===false) break;
		$result[]=__favoritos_explode_meta(substr($html,$pos1,$pos2-$pos1+1));
		$pos1=stripos($html,"<meta",$pos2);
	}
	return $result;
}

function __feeds_getnode($path,$array) {
	if(!is_array($path)) $path=explode("/",$path);
	$elem=array_shift($path);
	if(!is_array($array) || !isset($array[$elem])) return null;
	if(count($path)==0) return $array[$elem];
	return __feeds_getnode($path,__feeds_getvalue($array[$elem]));
}

function __feeds_getvalue($array) {
	return (is_array($array) && isset($array["value"]) && isset($array["#attr"]))?$array["value"]:$array;
}

function __feeds_xml2array_helper($xml) {
	require_once("php/import.php");
	$data=xml2struct($xml);
	$data=array_reverse($data);
	$array=__import_struct2array($data);
	return $array;
}

function __feeds_xml2array($xml) {
	capture_next_error();
	$array=__feeds_xml2array_helper($xml);
	$error=get_clear_error();
	if(strpos($error,"Reserved XML Name")!==false) {
		$xml=trim($xml);
		capture_next_error();
		$array=__feeds_xml2array_helper($xml);
		$error=get_clear_error();
	}
	if(strpos($error,"Invalid document")!==false) {
		$xml=remove_script_tag($xml);
		capture_next_error();
		$array=__feeds_xml2array_helper($xml);
		$error=get_clear_error();
	}
	if(strpos($error,"XML_ERR_NAME_REQUIRED")!==false) {
		$xml=str_replace("&","&amp;",$xml);
		capture_next_error();
		$array=__feeds_xml2array_helper($xml);
		$error=get_clear_error();
	}
	if(strpos($error,"EntityRef")!==false) {
		$xml=str_replace("&","&amp;",$xml);
		capture_next_error();
		$array=__feeds_xml2array_helper($xml);
		$error=get_clear_error();
	}
	if(strpos($error,"Invalid character")!==false) {
		$xml=remove_bad_chars($xml);
		capture_next_error();
		$array=__feeds_xml2array_helper($xml);
		$error=get_clear_error();
	}
	if(strpos($error,"Invalid document end")!==false) {
		$error=""; // KNOWN ISSUE
	}
	return array($array,$error);
}

function __feeds_detect($array) {
	$keys=array_keys($array);
	if(isset($keys[0])) {
		if($keys[0]=="rdf:RDF") return "rdf";
		if($keys[0]=="rss") return "rss2";
		if($keys[0]=="feed") return "atom";
	}
	return "unknown";
}

function __feeds_fetchmain($array) {
	$type=__feeds_detect($array);
	$title="";
	$link="";
	$description="";
	$image="img/deffeed.png";
	if($type=="rdf") {
		$title=getutf8(__feeds_getnode("rdf:RDF/channel/title",$array));
		$link=__feeds_getnode("rdf:RDF/channel/link",$array);
		$description=getutf8(__feeds_getnode("rdf:RDF/channel/description",$array));
		$image=__feeds_getnode("rdf:RDF/image/url",$array);
	} elseif($type=="rss2") {
		$title=getutf8(__feeds_getnode("rss/channel/title",$array));
		$link=__feeds_getnode("rss/channel/link",$array);
		$description=getutf8(__feeds_getnode("rss/channel/description",$array));
		$image=__feeds_getnode("rss/channel/image/url",$array);
	} elseif($type=="atom") {
		$title=getutf8(__feeds_getvalue(__feeds_getnode("feed/title",$array)));
		$link=__feeds_getnode("feed/link",$array);
		$count=0;
		while($link!==null) {
			$rel=__feeds_getnode("#attr/rel",$link);
			$type=__feeds_getnode("#attr/type",$link);
			if($rel=="alternate" && $type=="text/html") {
				$link=__feeds_getnode("#attr/href",$link);
				break;
			}
			if($rel=="alternate" && !isset($alternate)) {
				$alternate=__feeds_getnode("#attr/href",$link);
			}
			$count++;
			$link=__feeds_getnode("feed/link#${count}",$array);
		}
		if(!$link && isset($alternate)) $link=$alternate;
		// FIX FOR GOOGLE GROUPS
		if(!$link) $link=__feeds_getnode("feed/id",$array);
		$description=getutf8(__feeds_getvalue(__feeds_getnode("feed/subtitle",$array)));
	}
	$array=array("title"=>$title,"link"=>$link,"description"=>$description,"image"=>$image);
	foreach($array as $key=>$val) $array[$key]=trim($val);
	return $array;
}

function __feeds_fetchitems($array) {
	require_once("php/getmail.php");
	$type=__feeds_detect($array);
	$items=array();
	if($type=="rdf") {
		$item=__feeds_getvalue(__feeds_getnode("rdf:RDF/item",$array));
		$count=0;
		while($item!==null) {
			$title=__feeds_getnode("title",$item);
			if(is_array($title)) {
				$title=__array2xml_write_nodes($title);
				$title=getutf8($title);
				$title=html2text($title);
			} else {
				$title=getutf8($title);
			}
			$link=__feeds_getnode("link",$item);
			if(is_array($link)) $link=array_shift($link);
			$description=__feeds_getnode("description",$item);
			if(is_array($description)) $description=__array2xml_write_nodes($description);
			$description=getutf8($description);
			$pubdate=__feeds_getnode("dc:date",$item);
			if(is_array($pubdate)) $pubdate=array_shift($pubdate);
			if($pubdate) $pubdate=date("Y-m-d H:i:s",strtotime($pubdate));
			$hash=md5(serialize(array($title,$pubdate,$description,$link)));
			if(!$pubdate) $pubdate=current_datetime();
			if($title!="" && $link!="") $items[]=array("title"=>$title,"link"=>$link,"description"=>$description,"pubdate"=>$pubdate,"hash"=>$hash);
			$count++;
			$item=__feeds_getvalue(__feeds_getnode("rdf:RDF/item#${count}",$array));
		}
	} elseif($type=="rss2") {
		$item=__feeds_getvalue(__feeds_getnode("rss/channel/item",$array));
		$count=0;
		while($item!==null) {
			$title=__feeds_getnode("title",$item);
			if(is_array($title)) {
				$title=__array2xml_write_nodes($title);
				$title=getutf8($title);
				$title=html2text($title);
			} else {
				$title=getutf8($title);
			}
			$link=__feeds_getnode("link",$item);
			if(is_array($link)) $link=array_shift($link);
			$description=__feeds_getnode("description",$item);
			if(is_array($description)) $description=__array2xml_write_nodes($description);
			$description=getutf8($description);
			$pubdate=__feeds_getnode("pubDate",$item);
			if(is_array($pubdate)) $pubdate=array_shift($pubdate);
			if($pubdate) $pubdate=date("Y-m-d H:i:s",strtotime($pubdate));
			$hash=md5(serialize(array($title,$pubdate,$description,$link)));
			if(!$pubdate) $pubdate=current_datetime();
			if($title!="" && $link!="") $items[]=array("title"=>$title,"link"=>$link,"description"=>$description,"pubdate"=>$pubdate,"hash"=>$hash);
			$count++;
			$item=__feeds_getvalue(__feeds_getnode("rss/channel/item#${count}",$array));
		}
	} elseif($type=="atom") {
		$item=__feeds_getvalue(__feeds_getnode("feed/entry",$array));
		$count=0;
		while($item!==null) {
			$title=__feeds_getvalue(__feeds_getnode("title",$item));
			if(is_array($title)) {
				$title=__array2xml_write_nodes($title);
				$title=getutf8($title);
				$title=html2text($title);
			} else {
				$title=getutf8($title);
			}
			$link=__feeds_getnode("link",$item);
			$count2=0;
			while($link!==null) {
				$rel=__feeds_getnode("#attr/rel",$link);
				$type=__feeds_getnode("#attr/type",$link);
				if($rel=="alternate" && $type=="text/html") {
					$link=__feeds_getnode("#attr/href",$link);
					break;
				}
				if($rel=="alternate" && !isset($alternate)) {
					$alternate=__feeds_getnode("#attr/href",$link);
				}
				$count2++;
				$link=__feeds_getnode("link#${count2}",$item);
			}
			if(!$link && isset($alternate)) $link=$alternate;
			// FIX FOR GOOGLE GROUPS
			if(!$link) $link=__feeds_getnode("#attr/href",__feeds_getnode("link",$item));
			if(is_array($link)) $link=array_shift($link);
			// GET CONTENT (AND SUMMARY IS OPTIONAL IN SOME FEEDS)
			$summary=__feeds_getvalue(__feeds_getnode("summary",$item));
			if(is_array($summary)) $summary=__array2xml_write_nodes($summary);
			$summary=trim(getutf8($summary));
			$content=__feeds_getvalue(__feeds_getnode("content",$item));
			if(is_array($content)) $content=__array2xml_write_nodes($content);
			$content=trim(getutf8($content));
			// FIX SOME ISSUES ABOUT SOME HTML WITH NO TEXT CONTENT
			$summary_plain=trim(strip_tags($summary));
			if(!$summary_plain) $summary="";
			$content_plain=trim(strip_tags($content));
			if(!$content_plain) $content="";
			// IF THE SUMMARY IS A PREVIEW OF THE CONTENT, REMOVE IT
			if($summary_plain && $content_plain && strncmp($summary_plain,$content_plain,min(strlen($summary_plain),strlen($content_plain)))==0) {
				if(strlen($summary_plain)<strlen($content_plain)) $summary="";
				else $content="";
			}
			// TRUE, PREPARE THE DESCRIPTION TO USE IN APPLICATION
			$description="";
			if($summary && $content) $description=$summary.__HTML_SEPARATOR__.$content;
			if($summary && !$content) $description=$summary;
			if(!$summary && $content) $description=$content;
			// CONTINUE
			$pubdate=__feeds_getnode("updated",$item);
			if(is_array($pubdate)) $pubdate=array_shift($pubdate);
			if($pubdate) $pubdate=date("Y-m-d H:i:s",strtotime($pubdate));
			$hash=md5(serialize(array($title,$pubdate,$description,$link)));
			if(!$pubdate) $pubdate=current_datetime();
			if($title!="" && $link!="") $items[]=array("title"=>$title,"link"=>$link,"description"=>$description,"pubdate"=>$pubdate,"hash"=>$hash);
			$count++;
			$item=__feeds_getvalue(__feeds_getnode("feed/entry#${count}",$array));
		}
	}
	foreach($items as $key=>$val) foreach($val as $key2=>$val2) $items[$key][$key2]=trim($val2);
	return $items;
}

function __folders_update_tree($id_usuario,$id_parent=0,&$pos=0,$depth=0) {
	$query="SELECT id FROM tbl_folders WHERE id_usuario='${id_usuario}' AND id_parent='${id_parent}' ORDER BY name ASC";
	$result=db_query($query);
	while($row=db_fetch_row($result)) {
		$id=$row["id"];
		$query=make_update_query("tbl_folders",array(
			"pos"=>$pos,
			"depth"=>$depth
		),"id_usuario='${id_usuario}' AND id=${id}");
		db_query($query);
		$pos++;
		__folders_update_tree($id_usuario,$row["id"],$pos,$depth+1);
	}
	db_free($result);
}

// FUNCTIONS FOR THE NEW API V3
function __gcalendar_getattr($html,$attr) {
	$pos1=stripos($html,$attr);
	if($pos1===false) return "";
	$len=strlen($attr);
	$pos2=$pos1+$len;
	$max=strlen($html);
	while($pos2<$max && $html[$pos2]!='=') $pos2++;
	if($pos2==$max) return "";
	while($pos2<$max && $html[$pos2]!='"' && $html[$pos2]!="'") $pos2++;
	if($pos2==$max) return "";
	$pos3=$pos2+1;
	while($pos3<$max && $html[$pos3]!='"' && $html[$pos3]!="'") $pos3++;
	if($pos3==$max) return "";
	return substr($html,$pos2+1,$pos3-$pos2-1);
}

function __gcalendar_getnode($html,$name) {
	$pos1=stripos($html,"<${name}");
	if($pos1===false) return "";
	$len=strlen($name);
	$pos2=strpos($html,">",$pos1);
	if($pos2===false || $pos2<$pos1) return "";
	$pos3=stripos($html,"</${name}>",$pos2);
	if($pos3!==false) {
		if($pos3<$pos2) return "";
		$node=substr($html,$pos1,$pos3+$len+3-$pos1);
	} else {
		$node=substr($html,$pos1,$pos2+1-$pos1);
	}
	return $node;
}

function __gcalendar_parse($html) {
	$html=remove_script_tag($html);
	$html=remove_style_tag($html);
	$html=__gcalendar_getnode($html,"form");
	$method=__gcalendar_getattr($html,"method");
	$action=__gcalendar_getattr($html,"action");
	$inputs=array();
	for(;;) {
		$input=__gcalendar_getnode($html,"input");
		if($input=="") break;
		$html=str_replace($input,"",$html);
		$inputs[__gcalendar_getattr($input,"name")]=__gcalendar_getattr($input,"value");
	}
	return array("method"=>$method,"action"=>$action,"inputs"=>$inputs);
}

function __gcalendar_request($url,$method="",$values=array(),$cookies=array(),$referer="") {
	require_once("lib/phpclasses/http.php");
	$http=new http_class;
	$http->user_agent=get_name_version_revision();
	$http->follow_redirect=1;
	if(count($cookies)) $http->RestoreCookies($cookies);
	$arguments=array();
	$error=$http->GetRequestArguments($url,$arguments);
	if($error!="") show_php_error(array("phperror"=>$error));
	$error=$http->Open($arguments);
	if($error!="") show_php_error(array("phperror"=>$error));
	if($method!="") $arguments["RequestMethod"]=strtoupper($method);
	if(count($values)) $arguments["PostValues"]=$values;
	if($referer!="") $arguments["Referer"]=$referer;
	$error=$http->SendRequest($arguments);
	if($error!="") show_php_error(array("phperror"=>$error));
	$headers=array();
	$error=$http->ReadReplyHeaders($headers);
	if($error!="") show_php_error(array("phperror"=>$error));
	$body="";
	$error=$http->ReadWholeReplyBody($body);
	if($error!="") show_php_error(array("phperror"=>$error));
	$http->Close();
	//~ echo $body;die();
	//~ echo "<pre>".sprintr($headers)."</pre>";die();
	$cookies=array();
	$http->SaveCookies($cookies);
	return array($body,$headers,$cookies);
}

function __gcalendar_token1($client,$login,$password) {
	$url=$client->createAuthUrl();
	list($body,$header,$cookies)=__gcalendar_request($url);
	// PROCESS LOGIN PAGE
	$parsed=__gcalendar_parse($body);
	$parsed["inputs"]["Email"]=$login;
	$parsed["inputs"]["Passwd"]=$password;
	$parsed["inputs"]["continue"]=str_replace("&amp;","&",$parsed["inputs"]["continue"]);
	//~ echo "<pre>".sprintr($parsed)."</pre>";die();
	list($body,$header,$cookies)=__gcalendar_request($parsed["action"],$parsed["method"],$parsed["inputs"],$cookies,$url);
	// PROCESS ACCEPT PAGE
	$url=$parsed["action"];
	$parsed=__gcalendar_parse($body);
	$parsed["action"]=str_replace("&amp;","&",$parsed["action"]);
	$parsed["inputs"]["submit_access"]="true";
	//~ echo "<pre>".sprintr($parsed)."</pre>";die();
	list($body,$header,$cookies)=__gcalendar_request($parsed["action"],$parsed["method"],$parsed["inputs"],$cookies,$url);
	// PROCESS TOKEN PAGE
	$html=__gcalendar_getnode($body,"input");
	$token1=__gcalendar_getattr($html,"value");
	return $token1;
}

function __gcalendar_connect($login,$password) {
	$client=new Google_Client();
	$client->setAuthConfigFile("lib/google/saltos.json");
	$client->setRedirectUri("urn:ietf:wg:oauth:2.0:oob");
	$client->addScope("https://www.googleapis.com/auth/calendar");
	$client->setAccessType("offline");
	$token2=execute_query(make_select_query("tbl_gcalendar","token2",make_where_query(array(
		"id_usuario"=>current_user()
	))));
	if($token2!="") {
		$client->setAccessToken(base64_decode($token2));
		if($client->getAccessToken()) return $client;
	}
	$token1=__gcalendar_token1($client,$login,$password);
	if($token1=="") return null;
	$client->authenticate($token1);
	$token2=$client->getAccessToken();
	if(!$token2) return null;
	$query=make_update_query("tbl_gcalendar",array(
		"token2"=>base64_encode($token2)
	),"id_usuario='".current_user()."'");
	db_query($query);
	return $client;
}

// MODIFIED FUNCTIONS
function __gcalendar_format($datetime) {
	return date("Y-m-d\TH:i:sP",strtotime($datetime));
}

function __gcalendar_unformat($datetime) {
	return date("Y-m-d H:i:s",strtotime($datetime));
}

function __gcalendar_insert($client,$title,$content,$where,$dstart,$dstop) {
	if($client===null) return false;
	$service=new Google_Service_Calendar($client);
	$event=new Google_Service_Calendar_Event();
	$event->setSummary($title);
	$event->setDescription($content);
	$event->setLocation($where);
	$start=new Google_Service_Calendar_EventDateTime();
	$start->setDateTime(__gcalendar_format($dstart));
	$event->setStart($start);
	$end=new Google_Service_Calendar_EventDateTime();
	$end->setDateTime(__gcalendar_format($dstop));
	$event->setEnd($end);
	$createdEvent=$service->events->insert("primary",$event);
	return $createdEvent->getId();
}

function __gcalendar_update($client,$id,$title,$content,$where,$dstart,$dstop) {
	if($client===null) return false;
	$service=new Google_Service_Calendar($client);
	$event=$service->events->get("primary",$id);
	$event->setSummary($title);
	$event->setDescription($content);
	$event->setLocation($where);
	$start = new Google_Service_Calendar_EventDateTime();
	$start->setDateTime(__gcalendar_format($dstart));
	$event->setStart($start);
	$end = new Google_Service_Calendar_EventDateTime();
	$end->setDateTime(__gcalendar_format($dstop));
	$event->setEnd($end);
	$updatedEvent=$service->events->update("primary",$id,$event);
	return true;
}

function __gcalendar_feed($client) {
	// CHECK FOR A VALID SERVICE
	if($client===null) return false;
	// CONTINUE
	$service=new Google_Service_Calendar($client);
	$events=$service->events->listEvents("primary");
	$result=array();
	while(true) {
		foreach($events->getItems() as $event) {
			$temp=array(
				"id"=>$event->getId(),
				"title"=>$event->getSummary(),
				"content"=>$event->getDescription(),
				"where"=>$event->getLocation(),
				"dstart"=>__gcalendar_unformat($event->getStart()->getDateTime()),
				"dstop"=>__gcalendar_unformat($event->getEnd()->getDateTime())
			);
			foreach($temp as $key=>$val) $temp[$key]=str_replace("\r","",trim($val));
			$temp["hash"]=md5(serialize(array($temp["title"],$temp["content"],$temp["where"],$temp["dstart"],$temp["dstop"])));
			$result[]=$temp;
		}
		$pageToken=$events->getNextPageToken();
		if($pageToken) {
			$optParams=array("pageToken"=>$pageToken);
			$events=$service->events->listEvents("primary",$optParams);
		} else {
			break;
		}
	}
	return $result;
}

function __incidencias_packreport($campos,$tipos,$row) {
	$body="";
	$count=count($campos);
	for($i=0;$i<$count;$i++) {
		$campo=$campos[$i];
		$tipo=$tipos[$i];
		$label=LANG($campo);
		$value=$row[$campo];
		switch($tipo) {
			case "text": $body.=__report_text($label,$value); break;
			case "textarea": $body.=__report_textarea($label,$value); break;
		}
	}
	return $body;
}

function __incidencias_codigo($id) {
	return substr(str_repeat("0",CONFIG("zero_padding_digits")).$id,-CONFIG("zero_padding_digits"),CONFIG("zero_padding_digits"));
}

function __number_color2dec($color,$component) {
	$offset=array("R"=>0,"G"=>2,"B"=>4);
	if(!isset($offset[$component])) show_php_error(array("phperror"=>"Unknown component"));
	return hexdec(substr($color,$offset[$component],2));
}

function __number_color2incr($color,$incr) {
	return min(max($color*$incr,0),255);
}

function __number_color2aprox($fgcolor,$bgcolor1,$bgcolor2) {
	$color=255;
	while($color==$fgcolor || $color==$bgcolor1 || $color==$bgcolor2) $color--;
	return $color;
}

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
		__pdf_eval_pdftag($this->arr_header,$this->row_header);
		$this->check_y_enable($oldenable);
	}

	function Footer() {
		$oldenable=$this->check_y_enable(false);
		__pdf_eval_pdftag($this->arr_footer,$this->row_footer);
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

function __pdf_eval_value($input,$row) {
	return eval_protected($input,array("row"=>$row));
}

function __pdf_eval_array($array,$row) {
	foreach($array as $key=>$val) {
		$array[$key]=__pdf_eval_value($val,$row);
	}
	return $array;
}

function __pdf_eval_explode($separator,$str,$limit=0) {
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

function __pdf_eval_pdftag($array,$row=array()) {
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
					$booleval=__pdf_eval_value($val,$row);
					break;
				case "constructor":
					if(!$booleval) break;
					$temp=__pdf_eval_array(__pdf_eval_explode(",",$val),$row);
					$pdf=new PDF($temp[0],$temp[1],$temp[2]);
					$pdf->SetCreator(get_name_version_revision());
					$pdf->setRTL($dir=="rtl");
					$pdf->Init();
					break;
				case "margins":
					if(!$booleval) break;
					$temp=__pdf_eval_array(__pdf_eval_explode(",",$val),$row);
					$pdf->SetMargins($temp[3],$temp[0],$temp[1]);
					$pdf->SetAutoPageBreak(true,$temp[2]);
					break;
				case "query":
					if(!$booleval) break;
					$query=__pdf_eval_value($val,$row);
					break;
				case "foreach":
					if(!$booleval) break;
					if(!isset($query)) show_php_error(array("phperror"=>"Foreach without query!!!"));
					$result=db_query($query);
					$count=0;
					while($row2=db_fetch_row($result)) {
						$row2["__ROW_NUMBER__"]=++$count;
						__pdf_eval_pdftag($val,$row2);
					}
					db_free($result);
					break;
				case "output":
					if(!$booleval) break;
					$name=__pdf_eval_value($val,$row);
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
					if($val) $pdf->AddPage(__pdf_eval_value($val,$row));
					else $pdf->AddPage();
					break;
				case "font":
					if(!$booleval) break;
					$temp2=__pdf_eval_array(__pdf_eval_explode(",",$val,4),$row);
					$temp=array($temp2[0],$temp2[1],$temp2[2],color2dec($temp2[3],"R"),color2dec($temp2[3],"G"),color2dec($temp2[3],"B"));
					$pdf->SetFont($fonts[$temp[0]],$temp[1],$temp[2]);
					$pdf->SetTextColor($temp[3],$temp[4],$temp[5]);
					break;
				case "image":
					if(!$booleval) break;
					$temp=__pdf_eval_array(__pdf_eval_explode(",",$val,6),$row);
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
					$temp=__pdf_eval_array(__pdf_eval_explode(",",$val,4),$row);
					if(isset($temp[3])) $pdf->StartTransform();
					if(isset($temp[3])) $pdf->Rotate($temp[3],$temp[0],$temp[1]);
					$pdf->SetXY($temp[0],$temp[1]);
					$pdf->Cell(0,0,$temp[2]);
					if(isset($temp[3])) $pdf->StopTransform();
					break;
				case "textarea":
					if(!$booleval) break;
					$temp=__pdf_eval_array(__pdf_eval_explode(",",$val,7),$row);
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
					$temp2=__pdf_eval_array(__pdf_eval_explode(",",$val,2),$row);
					$temp=array(color2dec($temp2[0],"R"),color2dec($temp2[0],"G"),color2dec($temp2[0],"B"),color2dec($temp2[1],"R"),color2dec($temp2[1],"G"),color2dec($temp2[1],"B"));
					$pdf->SetDrawColor($temp[0],$temp[1],$temp[2]);
					$pdf->SetFillColor($temp[3],$temp[4],$temp[5]);
					break;
				case "rect":
					if(!$booleval) break;
					$temp=__pdf_eval_array(__pdf_eval_explode(",",$val,7),$row);
					if(isset($temp[5])) $pdf->SetLineWidth($temp[5]);
					if(isset($temp[6])) {
						$pdf->RoundedRect($temp[0],$temp[1],$temp[2],$temp[3],$temp[6],"1111",$temp[4]);
					} else {
						$pdf->Rect($temp[0],$temp[1],$temp[2],$temp[3],$temp[4]);
					}
					break;
				case "line":
					if(!$booleval) break;
					$temp=__pdf_eval_array(__pdf_eval_explode(",",$val,5),$row);
					if(isset($temp[4])) $pdf->SetLineWidth($temp[4]);
					$pdf->Line($temp[0],$temp[1],$temp[2],$temp[3]);
					break;
				case "setxy":
					if(!$booleval) break;
					$temp=__pdf_eval_array(__pdf_eval_explode(",",$val,2),$row);
					$pdf->SetXY($temp[0],$temp[1]);
					$pdf->check_y();
					break;
				case "getxy":
					if(!$booleval) break;
					$temp=__pdf_eval_array(__pdf_eval_explode(",",$val,2),$row);
					$row[$temp[0]]=$pdf->GetX();
					$row[$temp[1]]=$pdf->GetY();
					break;
				case "pageno":
					if(!$booleval) break;
					$temp=__pdf_eval_array(__pdf_eval_explode(",",$val,7),$row);
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
					$temp=__pdf_eval_array(__pdf_eval_explode(",",$val,1),$row);
					$pdf->check_y($temp[0]);
					break;
				case "link":
					if(!$booleval) break;
					$temp=__pdf_eval_array(__pdf_eval_explode(",",$val,4),$row);
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

function __phpthumb_imagecreatefromtiff($src) {
	if(extension_loaded('imagick')) {
		$im2=new Imagick();
		$im2->readImage($src);
		$im2->setImageFormat('png');
		$im=imagecreatefromstring($im2->getImageBlob());
		$im2->destroy();
	} else {
		$file=get_temp_file(getDefault("exts/pngext",".png"));
		ob_passthru("convert ${src} ${file}");
		if(!file_exists($file)) show_php_error(array("phperror"=>"ImageMagick failed using convert command line"));
		$im=imagecreatefrompng($file);
		unlink($file);
	}
	return $im;
}

function __signature_getfile($id) {
	if(!$id) return null;
	$query="SELECT * FROM tbl_usuarios_c WHERE id='$id'";
	$row=execute_query($query);
	if(!$row) return null;
	if(!$row["email_signature_file"]) return null;
	$id=$row["id"];
	$name=$row["email_signature"];
	$file=$row["email_signature_file"];
	$type=$row["email_signature_type"];
	$size=$row["email_signature_size"];
	$data=file_get_contents(get_directory("dirs/filesdir").$file);
	$alt=$row["email_name"]." (".$row["email_from"].")";
	return array("id"=>$id,"name"=>$name,"file"=>$file,"type"=>$type,"size"=>$size,"data"=>$data,"alt"=>$alt);
}

function __signature_getauto($file) {
	if(!$file) return null;
	if(!$file["file"]) return null;
	if($file["type"]=="text/plain") {
		$file["auto"]=trim($file["data"]);
		$file["auto"]=htmlentities($file["auto"],ENT_COMPAT,"UTF-8");
		$file["auto"]=str_replace(array(" ","\t","\n"),array("&nbsp;",str_repeat("&nbsp;",8),"<br/>"),$file["auto"]);
	} elseif($file["type"]=="text/html") {
		$file["auto"]=trim($file["data"]);
	} elseif(substr($file["type"],0,6)=="image/") {
		if(eval_bool(getDefault("cache/useimginline"))) {
			$data=base64_encode($file["data"]);
			$file["src"]="data:image/png;base64,${data}";
		} else {
			$file["src"]="?action=signature&id=${file["id"]}";
		}
		$file["auto"]="<img alt=\"${file["alt"]}\" border=\"0\" src=\"${file["src"]}\" />";
	} else {
		$file["auto"]="Name: ${file["name"]}"."<br/>"."Type: ${file["type"]}"."<br/>"."Size: ${file["size"]}";
	}
	require_once("php/getmail.php");
	$file["auto"]=__SIGNATURE_OPEN__."--".__HTML_NEWLINE__.$file["auto"].__SIGNATURE_CLOSE__;
	return $file;
}

function __themeroller_calibrate($r,$g,$b) {
	return 0.299*$r+0.587*$g+0.114*$b;
}

function __themeroller_colorize($color,$rgb,$mult=1,$incr=0) {
	list($r,$g,$b)=__themeroller_components($color,true);
	$z=__themeroller_calibrate($r,$g,$b)*$mult+$incr;
	//~ echo "r=$r, g=$g, b=$b, z=$z\n";
	list($r,$g,$b)=__themeroller_components($rgb,true);
	$iter=0;
	for(;;) {
		$z2=__themeroller_calibrate($r,$g,$b);
		//~ echo "r=$r, g=$g, b=$b, z2=$z2\n";
		if(max($r,$g,$b)>1) { $r*=0.99; $g*=0.99; $b*=0.99; }
		elseif($iter>1000) break;
		elseif(abs($z2-$z)<0.01) break;
		elseif($z2<$z) { $r+=0.01; $g+=0.01; $b+=0.01; }
		elseif($z2>$z) { $r*=0.99; $g*=0.99; $b*=0.99; }
		$iter++;
	}
	//~ echo "iter=$iter\n";
	$z=sprintf("%02x%02x%02x",$r*255,$g*255,$b*255);
	return $z;
}

function __themeroller_csstrick($file,$csstrick) {
	foreach($csstrick as $key=>$val) if(strpos($file,$key)!==false) return explode(",",$val);
	show_php_error(array("phperror"=>"Mask '${file}' not found"));
}

function __themeroller_components($color,$normalize=false) {
	$len=strlen($color);
	if($len==3) { $size=1; $div=15; $mult=16; }
	elseif($len==6) { $size=2; $div=255; $mult=1; }
	else show_php_error(array("phperror"=>"Invalid color: '$color'"));
	$r=hexdec(substr($color,0*$size,$size));
	$g=hexdec(substr($color,1*$size,$size));
	$b=hexdec(substr($color,2*$size,$size));
	list($r,$g,$b)=$normalize?array($r/$div,$g/$div,$b/$div):array($r*$mult,$g*$mult,$b*$mult);
	return array($r,$g,$b);
}

function __translate_get_options($filter="") {
	if(!is_array($filter)) $filter=explode(",",$filter);
	if($filter[0]=="") unset($filter[0]);
	$options=array();
	$langs=__translate_get_apertium_langs();
	if(count($langs)>0) {
		$options[]="<option value='' reverse=''>".LANG("translate","translate")."</option>";
		foreach($langs as $key=>$val) {
			$temp=explode("-",$val);
			if(!count($filter) || in_array($temp[0],$filter)) {
				$val2=implode("-",array(LANG($temp[0],"translate"),LANG($temp[1],"translate")));
				$val3=implode("-",array($temp[1],$temp[0]));
				$val3=in_array($val3,$langs)?$val3:"";
				$options[]="<option value='$val' reverse='$val3'>- $val2</option>";
			}
		}
	}
	$langs=__translate_get_aspell_langs();
	if(count($langs)>0) {
		$options[]="<option value='' reverse=''>".LANG("corrector","translate")."</option>";
		foreach($langs as $key=>$val) {
			if(!count($filter) || in_array($val,$filter)) {
				$val3=implode("-",array($val,$val));
				$val2=LANG($val,"translate");
				$options[]="<option value='$val3' reverse='$val3'>- $val2</option>";
			}
		}
	}
	$options=implode("\n",$options);
	return $options;
}

function __default_eval_querytag($array) {
	foreach($array as $key=>$val) {
		if(is_array($val)) {
			$array[$key]=__default_eval_querytag($val);
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

function __default_process_querytag($query,&$go,&$commit) {
	$rows=array();
	foreach($query as $key=>$val) {
		if(is_array($val)) {
			set_array($rows,$key,__default_process_querytag($val,$go,$commit));
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
?>