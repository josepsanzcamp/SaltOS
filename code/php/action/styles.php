<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2020 by Josep Sanz Campderrós
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

if(getParam("action")=="styles") {
	$style=getParam("style");
	if(load_style($style)) {
		$stylepre=getDefault("stylepre");
		$stylepost=getDefault("stylepost");
		$revision=getDefault("info/revision");
		$styles=eval_attr(xml2array("xml/styles.xml"));
		$jstree=detect_light_or_dark_from_style($style);
		$jstreepre=getDefault("jstreepre");
		$jstreepost=getDefault("jstreepost");
		foreach($styles as $row) if($style==$row["value"]) break;
		echo __HTML_DOCTYPE__;
		echo "<html>";
		echo "<head>";
		echo "<link href='${stylepre}${style}${stylepost}?r=${revision}' rel='stylesheet' type='text/css'>";
		echo "<link href='css/default.css?r=${revision}' rel='stylesheet' type='text/css'>";
		echo "<link href='css/correo.css?r=${revision}' rel='stylesheet' type='text/css'>";
		echo "<link href='lib/fontawesome/css/fontawesome.min.css?r=${revision}' rel='stylesheet' type='text/css'>";
		echo "<link href='lib/fontawesome/css/solid.min.css?r=${revision}' rel='stylesheet' type='text/css'>";
		echo "<link href='${jstreepre}${jstree}${jstreepost}?r=${revision}' rel='stylesheet' type='text/css'>";
		echo "<script src='lib/jquery/jquery.min.js?r=${revision}'></script>";
		echo "<script>";
		echo "var inputs='a.ui-state-default,input.ui-state-default,li.ui-state-default,h3.ui-state-default'; $(document).on('mouseover',inputs,function() { $(this).addClass('ui-state-hover'); }).on('mouseout',inputs,function() { $(this).removeClass('ui-state-hover'); });";
		echo "var inputs='.tabla td.tbody'; $(document).on('mouseover',inputs,function() { $(this).parent().find('td').addClass('ui-state-highlight'); }).on('mouseout',inputs,function() { $(this).parent().find('td').removeClass('ui-state-highlight'); });";
		echo "</script>";
		echo "</head>";
		echo "<body>";
		echo str_replace("APP_NAME",$row["label"]." - ".getDefault("info/title")." - ".get_name_version_revision(),file_get_contents("xml/common/styles3.xml"));
		echo "</body>";
		echo "</html>";
	} else {
		$revision=getDefault("info/revision");
		$styles=eval_attr(xml2array("xml/styles.xml"));
		echo __HTML_DOCTYPE__;
		echo "<html>";
		echo "<head>";
		echo "<link href='css/default.css?r=${revision}' rel='stylesheet' type='text/css'>";
		echo "<style>";
		echo "iframe{width:900px; height:600px; margin:10px; border:1px solid #333;}";
		echo "</style>";
		echo "</head>";
		echo "<body>";
		$filter=getParam("filter");
		if($filter!="") {
			$filter=explode("|",$filter);
			foreach($styles as $index=>$row) {
				$found=0;
				foreach($filter as $filter2) {
					if(stripos($row["label"],$filter2)!==false) $found=1;
					if(stripos($row["value"],$filter2)!==false) $found=1;
				}
				if(!$found) unset($styles[$index]);
			}
		}
		foreach($styles as $row) {
			echo "<iframe src='?action=styles&amp;style=${row["value"]}' frameborder='0'></iframe>";
		}
		echo "</body>";
		echo "</html>";
	}
	die();
}

?>