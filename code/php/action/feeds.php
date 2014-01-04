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
if(getParam("action")=="feeds") {
	// FOR ACTION2
	if(getParam("action2")) {
		$ids=check_ids(getParam("id"));
		if($ids) {
			$numids=count(explode(",",$ids));
			$query="SELECT id FROM tbl_feeds a WHERE id IN ($ids) AND id IN (SELECT id_registro FROM tbl_registros_i WHERE id_aplicacion='".page2id("feeds")."' AND id_registro=a.id AND id_usuario='".current_user()."')";
			$result=execute_query_array($query);
			$numresult=count($result);
			if($numresult==$numids) {
				$action2=explode("=",getParam("action2"));
				if($action2[0]=="leidos") {
					// BUSCAR CUANTOS REGISTROS SE VAN A MODIFICAR
					$query="SELECT COUNT(*) FROM tbl_feeds WHERE id IN ($ids) AND state_new!='${action2[1]}'";
					$numids=execute_query($query);
					// PONER STATE_NEW=0 EN LOS POSTS SELECCIONADOS
					$query="UPDATE tbl_feeds SET state_new='${action2[1]}' WHERE id IN ($ids) AND state_new!='${action2[1]}'";
					db_query($query);
					// MOSTRAR RESULTADO
					session_alert(LANG($action2[1]?"msgnumnoleidos":"msgnumsileidos","feeds").$numids.LANG("message".min($numids,2),"feeds"));
				} elseif($action2[0]=="wait") {
					// BUSCAR CUANTOS REGISTROS SE VAN A MODIFICAR
					$query="SELECT COUNT(*) FROM tbl_feeds WHERE id IN ($ids) AND state_wait!='${action2[1]}'";
					$numids=execute_query($query);
					// PONER STATE_WAIT=1 EN LOS POSTS SELECCIONADOS
					$query="UPDATE tbl_feeds SET state_new='0',state_wait='${action2[1]}' WHERE id IN ($ids) AND state_wait!='${action2[1]}'";
					db_query($query);
					// MOSTRAR RESULTADO
					session_alert(LANG($action2[1]?"msgnumsiwait":"msgnumnowait","feeds").$numids.LANG("message".min($numids,2),"feeds"));
				} elseif($action2[0]=="cool") {
					// BUSCAR CUANTOS REGISTROS SE VAN A MODIFICAR
					$query="SELECT COUNT(*) FROM tbl_feeds WHERE id IN ($ids) AND state_cool!='${action2[1]}'";
					$numids=execute_query($query);
					// PONER STATE_cool=1 EN LOS POSTS SELECCIONADOS
					$query="UPDATE tbl_feeds SET state_new='0',state_cool='${action2[1]}' WHERE id IN ($ids) AND state_cool!='${action2[1]}'";
					db_query($query);
					// MOSTRAR RESULTADO
					session_alert(LANG($action2[1]?"msgnumsicool":"msgnumnocool","feeds").$numids.LANG("message".min($numids,2),"feeds"));
				} elseif($action2[0]=="delete") {
					// CREAR DATOS EN TABLA DE POSTS BORRADOS
					$query="INSERT INTO tbl_feeds_d SELECT NULL id,id_feed,link,'".current_datetime()."' FROM tbl_feeds WHERE id IN ($ids)";
					db_query($query);
					// BORRAR POSTS
					$query="DELETE FROM tbl_feeds WHERE id IN ($ids)";
					db_query($query);
					// BORRAR REGISTRO DE LOS POSTS
					$query="DELETE FROM tbl_registros_i WHERE id_registro IN ($ids) AND id_aplicacion='".page2id("feeds")."'";
					db_query($query);
					$query="DELETE FROM tbl_registros_u WHERE id_registro IN ($ids) AND id_aplicacion='".page2id("feeds")."'";
					db_query($query);
					// BORRAR FOLDERS RELACIONADOS
					$query="DELETE FROM tbl_folders_a WHERE id_registro IN ($ids) AND id_aplicacion='".page2id("correo")."'";
					db_query($query);
					// MOSTRAR RESULTADO
					session_alert(LANG("msgnumdelete","feeds").$numids.LANG("message".min($numids,2),"feeds"));
				}
			} else {
				session_error(LANG("msgpropietario","feeds"));
			}
		} else {
			session_error(LANG("msgnotfound","feeds"));
		}
		javascript_history(-1);
		die();
	}
	// FUNCTIONS
	function __feeds_struct2array(&$data) {
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
				$value=__feeds_struct2array($data);
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

	function __feeds_getnode($path,$array) {
		if(!is_array($path)) $path=explode("/",$path);
		$elem=array_shift($path);
		if(!is_array($array) || !isset($array[$elem])) return null;
		if(count($path)==0) return $array[$elem];
		return __feeds_getnode($path,__feeds_getvalue($array[$elem]));
	}

	function __feeds_getvalue($array) {
		return (is_array($array) && isset($array["value"]))?$array["value"]:$array;
	}

	function __feeds_getutf8($temp) {
		require_once("php/getmail.php");
		return __getmail_getutf8($temp);
	}

	function __feeds_html2text($html) {
		require_once("php/getmail.php");
		return __getmail_html2text($html);
	}

	function __feeds_xml2array_helper($xml) {
		$data=xml2struct($xml);
		$data=array_reverse($data);
		$array=__feeds_struct2array($data);
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
			$xml=__feeds_removescripts($xml);
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
			$xml=__feeds_remove_bad_chars($xml);
			capture_next_error();
			$array=__feeds_xml2array_helper($xml);
			$error=get_clear_error();
		}
		return array($array,$error);
	}

	function __feeds_array2xml($array) {
		return __array2xml_write_nodes($array);
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
			$title=__feeds_getutf8(__feeds_getnode("rdf:RDF/channel/title",$array));
			$link=__feeds_getnode("rdf:RDF/channel/link",$array);
			$description=__feeds_getutf8(__feeds_getnode("rdf:RDF/channel/description",$array));
			$image=__feeds_getnode("rdf:RDF/image/url",$array);
		} elseif($type=="rss2") {
			$title=__feeds_getutf8(__feeds_getnode("rss/channel/title",$array));
			$link=__feeds_getnode("rss/channel/link",$array);
			$description=__feeds_getutf8(__feeds_getnode("rss/channel/description",$array));
			$image=__feeds_getnode("rss/channel/image/url",$array);
		} elseif($type=="atom") {
			$title=__feeds_getutf8(__feeds_getvalue(__feeds_getnode("feed/title",$array)));
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
			$description=__feeds_getutf8(__feeds_getvalue(__feeds_getnode("feed/subtitle",$array)));
		}
		$array=array("title"=>$title,"link"=>$link,"description"=>$description,"image"=>$image);
		foreach($array as $key=>$val) $array[$key]=trim($val);
		return $array;
	}

	function __feeds_fetchitems($array) {
		$type=__feeds_detect($array);
		$items=array();
		if($type=="rdf") {
			$item=__feeds_getvalue(__feeds_getnode("rdf:RDF/item",$array));
			$count=0;
			while($item!==null) {
				$title=__feeds_getnode("title",$item);
				if(is_array($title)) {
					$title=__feeds_array2xml($title);
					$title=__feeds_getutf8($title);
					$title=__feeds_html2text($title);
				} else {
					$title=__feeds_getutf8($title);
				}
				$link=__feeds_getnode("link",$item);
				$description=__feeds_getnode("description",$item);
				if(is_array($description)) $description=__feeds_array2xml($description);
				$description=__feeds_getutf8($description);
				$pubdate=__feeds_getnode("dc:date",$item);
				if($pubdate) $pubdate=date("Y-m-d H:i:s",strtotime($pubdate));
				$hash=md5(serialize(array($title,$pubdate,$description,$link)));
				if(!$pubdate) $pubdate=current_datetime();
				$items[]=array("title"=>$title,"link"=>$link,"description"=>$description,"pubdate"=>$pubdate,"hash"=>$hash);
				$count++;
				$item=__feeds_getvalue(__feeds_getnode("rdf:RDF/item#${count}",$array));
			}
		} elseif($type=="rss2") {
			$item=__feeds_getvalue(__feeds_getnode("rss/channel/item",$array));
			$count=0;
			while($item!==null) {
				$title=__feeds_getnode("title",$item);
				if(is_array($title)) {
					$title=__feeds_array2xml($title);
					$title=__feeds_getutf8($title);
					$title=__feeds_html2text($title);
				} else {
					$title=__feeds_getutf8($title);
				}
				$link=__feeds_getnode("link",$item);
				$description=__feeds_getnode("description",$item);
				if(is_array($description)) $description=__feeds_array2xml($description);
				$description=__feeds_getutf8($description);
				$pubdate=__feeds_getnode("pubDate",$item);
				if($pubdate) $pubdate=date("Y-m-d H:i:s",strtotime($pubdate));
				$hash=md5(serialize(array($title,$pubdate,$description,$link)));
				if(!$pubdate) $pubdate=current_datetime();
				$items[]=array("title"=>$title,"link"=>$link,"description"=>$description,"pubdate"=>$pubdate,"hash"=>$hash);
				$count++;
				$item=__feeds_getvalue(__feeds_getnode("rss/channel/item#${count}",$array));
			}
		} elseif($type=="atom") {
			$item=__feeds_getvalue(__feeds_getnode("feed/entry",$array));
			$count=0;
			while($item!==null) {
				$title=__feeds_getvalue(__feeds_getnode("title",$item));
				if(is_array($title)) {
					$title=__feeds_array2xml($title);
					$title=__feeds_getutf8($title);
					$title=__feeds_html2text($title);
				} else {
					$title=__feeds_getutf8($title);
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
				// GET CONTENT (AND SUMMARY IS OPTIONAL IN SOME FEEDS)
				$summary=__feeds_getvalue(__feeds_getnode("summary",$item));
				if(is_array($summary)) $summary=__feeds_array2xml($summary);
				$summary=trim(__feeds_getutf8($summary));
				$content=__feeds_getvalue(__feeds_getnode("content",$item));
				if(is_array($content)) $content=__feeds_array2xml($content);
				$content=trim(__feeds_getutf8($content));
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
				if($pubdate) $pubdate=date("Y-m-d H:i:s",strtotime($pubdate));
				$hash=md5(serialize(array($title,$pubdate,$description,$link)));
				if(!$pubdate) $pubdate=current_datetime();
				$items[]=array("title"=>$title,"link"=>$link,"description"=>$description,"pubdate"=>$pubdate,"hash"=>$hash);
				$count++;
				$item=__feeds_getvalue(__feeds_getnode("feed/entry#${count}",$array));
			}
		}
		foreach($items as $key=>$val) foreach($val as $key2=>$val2) $items[$key][$key2]=trim($val2);
		return $items;
	}

	function __feeds_href_replace($temp) {
		require_once("php/getmail.php");
		return __getmail_href_replace($temp);
	}

	function __feeds_removescripts($temp) {
		require_once("php/getmail.php");
		return __getmail_removescripts($temp);
	}

	function __feeds_make_clickable($temp) {
		require_once("php/getmail.php");
		return __getmail_make_clickable($temp);
	}

	function __feeds_remove_bad_chars($temp) {
		return remove_bad_chars($temp);
	}
	// NORMAL CODE
	if(getParam("url")) {
		$url=getParam("url");
		if($url=="null") $url="";
		$url2=$url;
		$scheme=parse_url($url,PHP_URL_SCHEME);
		if(!$scheme) $url2="http://".$url2;
		capture_next_error();
		$xml=url_get_contents($url2);
		$error=get_clear_error();
		if(!$error) {
			list($array,$error)=__feeds_xml2array($xml);
			if(!$error) {
				$type=__feeds_detect($array);
				if($type=="unknown") $error=1;
				if(!$error) $url=$url2;
			}
			if($error) {
				$posatom=strpos($xml,"application/atom+xml");
				$posrss=strpos($xml,"application/rss+xml");
				if($posatom) {
					$posopen=strrpos(substr($xml,0,$posatom),"<",0);
					$posclose=strpos($xml,">",$posatom);
					$tagfeed=substr($xml,$posopen,$posclose-$posopen+1);
				} elseif($posrss) {
					$posopen=strrpos(substr($xml,0,$posrss),"<",0);
					$posclose=strpos($xml,">",$posrss);
					$tagfeed=substr($xml,$posopen,$posclose-$posopen+1);
				}
				if(isset($tagfeed)) {
					$poshref=strpos($tagfeed,"href");
					$posigual=strpos($tagfeed,"=",$poshref);
					$possimple1=strpos($tagfeed,"'",$posigual);
					$possimple2=strpos($tagfeed,"'",$possimple1+1);
					$posdoble1=strpos($tagfeed,'"',$posigual);
					$posdoble2=strpos($tagfeed,'"',$posdoble1+1);
					if($possimple1!==false && $possimple2!==false) {
						$url3=substr($tagfeed,$possimple1+1,$possimple2-$possimple1-1);
					} elseif($posdoble1!==false && $posdoble2!==false) {
						$url3=substr($tagfeed,$posdoble1+1,$posdoble2-$posdoble1-1);
					}
					if(isset($url3)) {
						$array=parse_url($url2);
						$array2=parse_url($url3);
						if(!isset($array2["scheme"])) $array2["scheme"]=$array["scheme"];
						if(!isset($array2["host"])) $array2["host"]=$array["host"];
						if(!isset($array2["port"]) && isset($array["port"])) $array2["port"]=$array["port"];
						if(!isset($array2["path"])) {
							$array2["path"]="/";
						} elseif(substr($array2["path"],0,1)!="/") {
							if(isset($array["path"])) $array2["path"]=$array["path"]."/".$array2["path"];
							if(!isset($array["path"])) $array2["path"]="/".$array2["path"];
						}
						require_once("lib/wordpress/http_build_url.php");
						$url3=http_build_url($url3,$array2);
						unset($array);
						unset($array2);
						capture_next_error();
						$xml=url_get_contents($url3);
						$error=get_clear_error();
						if(!$error) {
							list($array,$error)=__feeds_xml2array($xml);
							if(!$error) {
								$type=__feeds_detect($array);
								if($type=="unknown") $error=1;
								if(!$error) $url=$url3;
							}
						}
					}
				}
			}
			if(!$error) {
				$array=__feeds_fetchmain($array);
				//~ echo "<pre>".sprintr($array)."</pre>"; die();
				capture_next_error();
				url_get_contents($array["image"]);
				$error2=get_clear_error();
				if($error2) $array["image"]="img/deffeed.png";
			}
		}
		if($error) $array=array();
		if(!isset($array)) $array=array();
		$row=array_merge(array("url"=>$url),$array);
		$_RESULT=array("rows"=>array());
		set_array($_RESULT["rows"],"row",$row);
		$buffer="<?xml version='1.0' encoding='UTF-8' ?>\n";
		$buffer.=array2xml($_RESULT);
		// FLUSH THE OUTPUT
		output_buffer($buffer,"text/xml");
	}
	if(getParam("id")) {
		require_once("php/getmail.php");
		$id=intval(getParam("id"));
		$query="SELECT *,(SELECT title FROM tbl_usuarios_f WHERE id=id_feed) feed,(SELECT link FROM tbl_usuarios_f WHERE id=id_feed) link2 FROM tbl_feeds WHERE id='${id}'";
		$row=execute_query($query);
		if(!$row) die();
		$row["description"]=__feeds_make_clickable($row["description"]);
		$row["description"]=__feeds_href_replace($row["description"]);
		$cid=getParam("cid");
		if($cid=="body") {
			$buffer="";
			$buffer.=__HTML_PAGE_OPEN__;
			$buffer.=__HTML_TEXT_OPEN__;
			$buffer.=$row["description"];
			$buffer.=__HTML_TEXT_CLOSE__;
			$buffer.=__HTML_PAGE_CLOSE__;
			ob_start_protected(getDefault("obhandler"));
			header_powered();
			header_expires(false);
			header("Content-Type: text/html");
			header("x-frame-options: SAMEORIGIN");
			echo $buffer;
			ob_end_flush();
			die();
		} elseif($cid=="full") {
			$buffer="";
			$lista=array(
				"title"=>array("lang"=>LANG("title","feeds"),"link"=>""),
				"pubdate"=>array("lang"=>LANG("pubdate","feeds"),"link"=>""),
				"feed"=>array("lang"=>LANG("feed","feeds"),"link"=>"link2"),
				"link"=>array("lang"=>LANG("link","feeds"),"link"=>"link"),
			);
			$buffer.=__HTML_PAGE_OPEN__;
			$buffer.=__HTML_BOX_OPEN__;
			$buffer.=__HTML_TABLE_OPEN__;
			foreach($lista as $key=>$val) {
				$buffer.=__HTML_ROW_OPEN__;
				$buffer.=__HTML_RCELL_OPEN__;
				$buffer.=__HTML_TEXT_OPEN__;
				$buffer.=$val["lang"].":";
				$buffer.=__HTML_TEXT_CLOSE__;
				$buffer.=__HTML_CELL_CLOSE__;
				$buffer.=__HTML_CELL_OPEN__;
				$buffer.=__HTML_TEXT_OPEN__;
				if($val["link"]!="") $buffer.="<a onclick='parent.openwin(this.href);return false' href='".addslashes($row[$val["link"]])."'>";
				$buffer.="<b>".$row[$key]."</b>";
				if($val["link"]!="") $buffer.="</a>";
				$buffer.=__HTML_TEXT_CLOSE__;
				$buffer.=__HTML_CELL_CLOSE__;
				$buffer.=__HTML_ROW_CLOSE__;
			}
			$buffer.=__HTML_TABLE_CLOSE__;
			$buffer.=__HTML_BOX_CLOSE__;
			$buffer.=__HTML_SEPARATOR__;
			$buffer.=__HTML_TEXT_OPEN__;
			$buffer.=$row["description"];
			$buffer.=__HTML_TEXT_CLOSE__;
			$buffer.=__HTML_PAGE_CLOSE__;
			ob_start_protected(getDefault("obhandler"));
			header_powered();
			header_expires(false);
			header("Content-Type: text/html");
			header("x-frame-options: SAMEORIGIN");
			echo $buffer;
			ob_end_flush();
			die();
		}
		die();
	}
	if(eval_bool(getDefault("debug/cancelfeeds"))) die();
	// CHECK THE SEMAPHORE
	$semaphore=get_cache_file(array(getParam("action"),current_user()),getDefault("exts/semext",".sem"));
	if(!semaphore_acquire($semaphore,getDefault("semaphoretimeout",100000))) {
		if(!getParam("ajax")) {
			session_error(LANG("msgerrorsemaphore").getParam("action"));
			javascript_history(-1);
		} else {
			javascript_error(LANG("msgerrorsemaphore").getParam("action"));
		}
		die();
	}
	// DATOS FEEDS
	$query="SELECT * FROM tbl_usuarios_f WHERE id_usuario='".current_user()."' AND disabled='0'";
	$result=execute_query_array($query);
	if(!count($result)) {
		if(!getParam("ajax")) {
			session_error(LANG("msgnotfeeds","feeds"));
			javascript_history(-1);
		}
		semaphore_release($semaphore);
		javascript_headers();
		die();
	}
	// BEGIN THE LOOP
	$id_usuario=current_user();
	$id_aplicacion=page2id("feeds");
	$datetime=current_datetime();
	$newfeeds=0;
	$modifiedfeeds=0;
	$voice_ids=array();
	$datetime_d=current_datetime(-86400*intval(CONFIG("feeds_timeout")));
	$unixtime_d=strtotime($datetime_d);
	foreach($result as $row) {
		$id_feed=$row["id"];
		$url=$row["url"];
		$title=$row["title"];
		capture_next_error();
		$xml=url_get_contents($url);
		$error=get_clear_error();
		if(!$error) {
			list($array,$error)=__feeds_xml2array($xml);
			if(!$error) {
				$type=__feeds_detect($array);
				if($type=="unknown") $error=1;
				if(!$error) {
					$array=__feeds_fetchitems($array);
					//~ echo "<pre>".sprintr($array)."</pre>"; die();
					if(count($array)) {
						$array=array_reverse($array);
						// LISTA DE LINKS DESCARGADOS
						$links=array();
						foreach($array as $item) $links[]=addslashes($item["link"]);
						$links="'".implode("','",$links)."'";
						// LISTA DE HASHS DESCARGADOS
						$hashs=array();
						foreach($array as $item) $hashs[]=$item["hash"];
						$hashs="'".implode("','",$hashs)."'";
						// BUSCAR LINKS QUE YA EXISTEN
						$query="SELECT link FROM tbl_feeds WHERE id_feed='${id_feed}' AND link IN (${links})";
						$result2=execute_query_array($query);
						// BUSCAR HASHS QUE YA EXISTEN
						$query="SELECT hash FROM tbl_feeds WHERE id_feed='${id_feed}' AND hash IN (${hashs})";
						$result3=execute_query_array($query);
						// BUSCAR LINKS MARCADOS COMO BORRADOS
						$query="SELECT link FROM tbl_feeds_d WHERE id_feed='${id_feed}' AND link IN (${links})";
						$result4=execute_query_array($query);
						// ITERAR PARA CADA ITEM DEL RSS
						foreach($array as $item) {
							$link=$item["link"];
							$hash=$item["hash"];
							if(!in_array($link,array_merge($result2,$result4))) {
								// SI NO ESTA EL LINK DESCARGADO EN LOS LINKS EXISTENTES NI EN LOS MARCADOS COMO BORRADOS
								$link=addslashes($link);
								$title=addslashes($item["title"]);
								$pubdate=$item["pubdate"];
								$description=addslashes($item["description"]);
								$query="INSERT INTO tbl_feeds(id,id_feed,title,pubdate,description,link,hash,state_new,state_modified,state_wait,state_cool) VALUES(NULL,'${id_feed}','${title}','${pubdate}','${description}','${link}','${hash}','1','0','0','0')";
								db_query($query);
								$query="SELECT MAX(id) FROM tbl_feeds WHERE id_feed='${id_feed}'";
								$oldcache=set_use_cache("false");
								$last_id=execute_query($query);
								set_use_cache($oldcache);
								$query="INSERT INTO tbl_registros_i(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`) VALUES(NULL,'${id_aplicacion}','${last_id}','${id_usuario}','${datetime}')";
								db_query($query);
								$newfeeds++;
								$voice_ids[]=$last_id;
							} elseif(in_array($link,$result2) && !in_array($hash,$result3)) {
								// SI ESTA EL LINK DESCARGADO PERO NO SE ENCUENTRA EL HASH, ES QUE SE HA MODIFICADO
								$link=addslashes($link);
								$query="SELECT id FROM tbl_feeds WHERE id_feed='${id_feed}' AND link='${link}'";
								$last_id=execute_query($query);
								$title=addslashes($item["title"]);
								$pubdate=$item["pubdate"];
								$description=addslashes($item["description"]);
								$query="UPDATE tbl_feeds SET title='${title}',pubdate='${pubdate}',description='${description}',hash='${hash}',state_new='1',state_modified='1' WHERE id='${last_id}'";
								db_query($query);
								$query="INSERT INTO tbl_registros_u(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`) VALUES(NULL,'${id_aplicacion}','${last_id}','${id_usuario}','${datetime}')";
								db_query($query);
								$modifiedfeeds++;
							}
						}
						// BORRAR REGISTROS DE LA TABLA DE FEEDS BORRADOS QUE NO EXISTEN YA
						$query="DELETE FROM tbl_feeds_d WHERE id_feed='${id_feed}' AND NOT link IN (${links}) AND UNIX_TIMESTAMP(`datetime`)<='${unixtime_d}'";
						db_query($query);
					}
				}
			}
		}
		if($error) {
			if(!getParam("ajax")) {
				session_error(LANG("msgerrorfeed","feeds").$title);
			} else {
				javascript_error(LANG("msgerrorfeed","feeds").$title);
			}
		}
	}
	if(!getParam("ajax")) {
		if($newfeeds+$modifiedfeeds>0) {
			if($newfeeds>0) session_alert($newfeeds.LANG("msgnewfeedsok".min($newfeeds,2),"feeds"));
			if($modifiedfeeds>0) session_alert($modifiedfeeds.LANG("msgmodifiedfeedsok".min($modifiedfeeds,2),"feeds"));
		} else {
			session_alert(LANG("msgnewfeedsko","feeds"));
		}
		javascript_history(-1);
	} else {
		if($newfeeds+$modifiedfeeds>0) {
			$gotofeeds=" [<a href='javascript:void(0)' onclick='gotofeeds()'>".LANG("msggotofeeds","feeds")."</a>]";
			$condition="update_feeds_list()";
			if($newfeeds>0) {
				javascript_alert($newfeeds.LANG("msgnewfeedsok".min($newfeeds,2),"feeds"),$condition);
				javascript_alert($newfeeds.LANG("msgnewfeedsok".min($newfeeds,2),"feeds").$gotofeeds,"!($condition)");
			}
			if($modifiedfeeds>0) {
				javascript_alert($modifiedfeeds.LANG("msgmodifiedfeedsok".min($modifiedfeeds,2),"feeds"),$condition);
				javascript_alert($modifiedfeeds.LANG("msgmodifiedfeedsok".min($modifiedfeeds,2),"feeds").$gotofeeds,"!($condition)");
			}
			$query="SELECT COUNT(*) FROM tbl_feeds WHERE state_new='1' AND id_feed IN (SELECT id FROM tbl_usuarios_f WHERE id_usuario='".current_user()."')";
			$count=execute_query($query);
			if($count) javascript_template("number_feeds($count);");
			if($count) javascript_template("favicon_animate($count);");
			javascript_history(0,$condition);
		}
	}
	// VOICE FEATURES
	if($newfeeds+$modifiedfeeds>0) {
		if($newfeeds>0) javascript_template("notify_voice('".$newfeeds.LANG_ESCAPE("msgnewfeedsok".min($newfeeds,2),"feeds")."')","saltos_voice()");
		if($modifiedfeeds>0) javascript_template("notify_voice('".$modifiedfeeds.LANG("msgmodifiedfeedsok".min($modifiedfeeds,2),"feeds")."')","saltos_voice()");
	}
	if(count($voice_ids)) {
		$query="SELECT CONCAT((SELECT title FROM tbl_usuarios_f WHERE id=tbl_feeds.id_feed),'. ',title) reader FROM tbl_feeds WHERE id IN (".implode(",",$voice_ids).") ORDER BY id DESC";
		$result=execute_query_array($query);
		foreach($result as $reader) javascript_template("notify_voice('".str_replace(array("'","\n","\r")," ",$reader)."')","saltos_voice()");
	}
	// RELEASE SEMAPHORE
	semaphore_release($semaphore);
	javascript_headers();
	die();
}
?>