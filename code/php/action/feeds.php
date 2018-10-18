<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2018 by Josep Sanz Campderrós
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
if(!check_user()) action_denied();
if(getParam("action")=="feeds") {
	// FOR ACTION2
	if(getParam("action2")) {
		$ids=check_ids(getParam("id"));
		if($ids) {
			$numids=count(explode(",",$ids));
			$query="SELECT id FROM tbl_feeds a WHERE id IN (${ids}) AND id IN (SELECT id_registro FROM tbl_registros WHERE id_aplicacion='".page2id("feeds")."' AND id_registro=a.id AND first=1 AND id_usuario='".current_user()."')";
			$result=execute_query_array($query);
			$numresult=count($result);
			if($numresult==$numids) {
				$action2=explode("=",getParam("action2"));
				if($action2[0]=="leidos") {
					// BUSCAR CUANTOS REGISTROS SE VAN A MODIFICAR
					$query="SELECT COUNT(*) FROM tbl_feeds WHERE id IN (${ids}) AND state_new!='${action2[1]}'";
					$numids=execute_query($query);
					// PONER STATE_NEW=0 EN LOS POSTS SELECCIONADOS
					$query=make_update_query("tbl_feeds",array(
						"state_new"=>$action2[1]
					),"id IN (${ids}) AND state_new!='${action2[1]}'");
					db_query($query);
					// MOSTRAR RESULTADO
					session_alert(LANG($action2[1]?"msgnumnoleidos":"msgnumsileidos","feeds").$numids.LANG("message".min($numids,2),"feeds"));
				} elseif($action2[0]=="wait") {
					// BUSCAR CUANTOS REGISTROS SE VAN A MODIFICAR
					$query="SELECT COUNT(*) FROM tbl_feeds WHERE id IN (${ids}) AND state_wait!='${action2[1]}'";
					$numids=execute_query($query);
					// PONER STATE_WAIT=1 EN LOS POSTS SELECCIONADOS
					$query=make_update_query("tbl_feeds",array(
						"state_new"=>"0",
						"state_wait"=>$action2[1]
					),"id IN (${ids}) AND state_wait!='${action2[1]}'");
					db_query($query);
					// MOSTRAR RESULTADO
					session_alert(LANG($action2[1]?"msgnumsiwait":"msgnumnowait","feeds").$numids.LANG("message".min($numids,2),"feeds"));
				} elseif($action2[0]=="cool") {
					// BUSCAR CUANTOS REGISTROS SE VAN A MODIFICAR
					$query="SELECT COUNT(*) FROM tbl_feeds WHERE id IN (${ids}) AND state_cool!='${action2[1]}'";
					$numids=execute_query($query);
					// PONER STATE_cool=1 EN LOS POSTS SELECCIONADOS
					$query=make_update_query("tbl_feeds",array(
						"state_new"=>"0",
						"state_cool"=>$action2[1]
					),"id IN (${ids}) AND state_cool!='${action2[1]}'");
					db_query($query);
					// MOSTRAR RESULTADO
					session_alert(LANG($action2[1]?"msgnumsicool":"msgnumnocool","feeds").$numids.LANG("message".min($numids,2),"feeds"));
				} elseif($action2[0]=="delete") {
					// CREAR DATOS EN TABLA DE POSTS BORRADOS
					$query=make_insert_query("tbl_feeds_d",make_select_query("tbl_feeds",array(
						"id_feed",
						"link",
						"'".current_datetime()."'"
					),"id IN (${ids})"),array(
						"id_feed",
						"link",
						"datetime"
					));
					db_query($query);
					// BORRAR POSTS
					$query=make_delete_query("tbl_feeds","id IN (${ids})");
					db_query($query);
					// BORRAR REGISTRO DE LOS POSTS
					make_control(page2id("feeds"),$ids);
					make_indexing(page2id("feeds"),$ids);
					// BORRAR FOLDERS RELACIONADOS
					$query=make_delete_query("tbl_folders_a","id_registro IN (${ids}) AND id_aplicacion='".page2id("correo")."'");
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
	// NORMAL CODE
	require_once("php/libaction.php");
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
						require_once("lib/httpbuildurl/http_build_url.php");
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
		// PREPARE THE OUTPUT
		$_RESULT["rows"]=array_values($_RESULT["rows"]);
		$buffer=json_encode($_RESULT);
		// CONTINUE
		output_handler(array(
			"data"=>$buffer,
			"type"=>"application/json",
			"cache"=>false
		));
	}
	if(getParam("id")) {
		require_once("php/getmail.php");
		$id=intval(getParam("id"));
		$query="SELECT *,(SELECT title FROM tbl_usuarios_f WHERE id=id_feed) feed,(SELECT link FROM tbl_usuarios_f WHERE id=id_feed) link2 FROM tbl_feeds WHERE id='${id}'";
		$row=execute_query($query);
		if(!$row) die();
		$row["description"]=saltos_make_clickable($row["description"]);
		$row["description"]=href_replace($row["description"]);
		$cid=getParam("cid");
		if($cid=="body") {
			// MARCAR FEED COMO LEIDO SI ES EL PROPIETARIO
			$query=make_update_query("tbl_feeds",array(
				"state_new"=>"0"
			),"id=(SELECT id_registro FROM tbl_registros WHERE id_aplicacion='".page2id("feeds")."' AND id_registro='${id}' AND id_usuario='".current_user()."' AND first=1)");
			db_query($query);
			// CONTINUE
			$buffer="";
			$buffer.=__HTML_PAGE_OPEN__;
			$buffer.=__HTML_TEXT_OPEN__;
			$buffer.=$row["description"];
			$buffer.=__HTML_TEXT_CLOSE__;
			$buffer.=__HTML_PAGE_CLOSE__;
			output_handler(array(
				"data"=>$buffer,
				"type"=>"text/html",
				"cache"=>false,
				"extra"=>array("x-frame-options: SAMEORIGIN")
			));
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
			output_handler(array(
				"data"=>$buffer,
				"type"=>"text/html",
				"cache"=>false,
				"extra"=>array("x-frame-options: SAMEORIGIN")
			));
		}
		die();
	}
	if(eval_bool(getDefault("debug/cancelfeeds"))) die();
	// CHECK THE SEMAPHORE
	$semaphore=array(getParam("action"),current_user());
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
	$id_aplicacion=page2id("feeds");
	$newfeeds=0;
	$modifiedfeeds=0;
	$voice_ids=array();
	$datetime_d=current_datetime(-86400*intval(CONFIG("feeds_timeout")));
	$unixtime_d=strtotime($datetime_d);
	foreach($result as $row) {
		if(time_get_usage()>getDefault("server/percentstop")) break;
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
							if(time_get_usage()>getDefault("server/percentstop")) break;
							$link=$item["link"];
							$hash=$item["hash"];
							if(!in_array($link,array_merge($result2,$result4))) {
								// SI NO ESTA EL LINK DESCARGADO EN LOS LINKS EXISTENTES NI EN LOS MARCADOS COMO BORRADOS
								$query=make_insert_query("tbl_feeds",array(
									"id_feed"=>$id_feed,
									"title"=>$item["title"],
									"pubdate"=>$item["pubdate"],
									"description"=>$item["description"],
									"link"=>$link,
									"hash"=>$hash,
									"state_new"=>1,
									"state_modified"=>0,
									"state_wait"=>0,
									"state_cool"=>0
								));
								db_query($query);
								$query="SELECT MAX(id) FROM tbl_feeds WHERE id_feed='${id_feed}'";
								$last_id=execute_query($query);
								make_control($id_aplicacion,$last_id);
								make_indexing($id_aplicacion,$last_id);
								$newfeeds++;
								$voice_ids[]=$last_id;
							} elseif(in_array($link,$result2) && !in_array($hash,$result3)) {
								// SI ESTA EL LINK DESCARGADO PERO NO SE ENCUENTRA EL HASH, ES QUE SE HA MODIFICADO
								$link=addslashes($link);
								$query="SELECT id FROM tbl_feeds WHERE id_feed='${id_feed}' AND link='${link}'";
								$last_id=execute_query($query);
								// TO PREVENT SOME SPURIOUS BUG
								if(is_array($last_id)) {
									$last_id=array_pop($last_id);
									$query=make_delete_query("tbl_feeds","id_feed='${id_feed}' AND link='${link}' AND id!=${last_id}");
									db_query($query);
								}
								// CONTINUE
								$query=make_update_query("tbl_feeds",array(
									"title"=>$item["title"],
									"pubdate"=>$item["pubdate"],
									"description"=>$item["description"],
									"hash"=>$hash,
									"state_new"=>1,
									"state_modified"=>1
								),"id='${last_id}'");
								db_query($query);
								make_control($id_aplicacion,$last_id);
								make_indexing($id_aplicacion,$last_id);
								$modifiedfeeds++;
							}
						}
						// BORRAR REGISTROS DE LA TABLA DE FEEDS BORRADOS QUE NO EXISTEN YA
						$query=make_delete_query("tbl_feeds_d","id_feed='${id_feed}' AND NOT link IN (${links}) AND UNIX_TIMESTAMP(datetime)<='${unixtime_d}'");
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
		if($newfeeds>0) javascript_template("notify_voice('".$newfeeds.LANG_ESCAPE("msgnewfeedsok".min($newfeeds,2),"feeds")."')","typeof(saltos_voice)=='function' && saltos_voice()");
		if($modifiedfeeds>0) javascript_template("notify_voice('".$modifiedfeeds.LANG_ESCAPE("msgmodifiedfeedsok".min($modifiedfeeds,2),"feeds")."')","typeof(saltos_voice)=='function' && saltos_voice()");
	}
	if(count($voice_ids)) {
		$query="SELECT CONCAT((SELECT title FROM tbl_usuarios_f WHERE id=tbl_feeds.id_feed),'. ',title) reader FROM tbl_feeds WHERE id IN (".implode(",",$voice_ids).") ORDER BY id DESC";
		$result=execute_query_array($query);
		foreach($result as $reader) javascript_template("notify_voice('".str_replace(array("'","\n","\r")," ",$reader)."')","typeof(saltos_voice)=='function' && saltos_voice()");
	}
	// RELEASE SEMAPHORE
	semaphore_release($semaphore);
	javascript_headers();
	die();
}
?>