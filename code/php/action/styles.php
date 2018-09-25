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
if(getParam("action")=="styles") {
	$style=getParam("style");
	if(load_style($style)) {
		$stylepre=getDefault("stylepre");
		$stylepost=getDefault("stylepost");
		$revision=getDefault("info/revision");
		$styles=eval_attr(xml2array("xml/common/styles.xml"));
		foreach($styles["rows"] as $row) if($style==$row["value"]) break;
		echo "<!DOCTYPE html>";
		echo "<html>";
		echo "<head>";
		echo "<link href='${stylepre}${style}${stylepost}?r=${revision}' rel='stylesheet' type='text/css'>";
		echo "<link href='css/default.css?r=${revision}' rel='stylesheet' type='text/css'>";
		echo "<style>";
		echo "ul{list-style:none;padding-left:0;font-weight:bold;}";
		echo ".tabla td{border-top:none;}";
		echo "</style>";
		echo "</head>";
		echo "<body>";
		echo "<table class='width100 none' cellpadding='0' cellspacing='0' border='0'>";
		echo "<tr>";
		echo "<td colspan='2'>";
		echo "<div id='tabs'>";
		echo "<ul>";
		echo "<li><a href='#tabs-1'>".$row["label"]."</a></li>";
		echo "<li><a href='#tabs-2'>".get_name_version_revision()."</a></li>";
		echo "<li><a href='#tabs-3'>".getDefault("info/title")."</a></li>";
		echo "<div id='tabs-1'></div>";
		echo "<div id='tabs-2'></div>";
		echo "<div id='tabs-3'></div>";
		echo "</ul>";
		echo "</div>";
		echo "</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td valign='top' style='padding-left:2px;' width='33%'>";
		echo "<div id='accordion'>";
		echo "<h3>Section 1</h3>";
		echo "<div style='padding:0 15px;'>";
		echo "<ul>";
		echo "<li>List item one</li>";
		echo "<li>List item two</li>";
		echo "<li>List item three</li>";
		echo "</ul>";
		echo "</div>";
		echo "<h3>Section 2</h3>";
		echo "<div style='padding:0 15px;'>";
		echo "<ul>";
		echo "<li>List item one</li>";
		echo "<li>List item two</li>";
		echo "<li>List item three</li>";
		echo "</ul>";
		echo "</div>";
		echo "<h3>Section 3</h3>";
		echo "<div style='padding:0 15px;'>";
		echo "<ul>";
		echo "<li>List item one</li>";
		echo "<li>List item two</li>";
		echo "<li>List item three</li>";
		echo "</ul>";
		echo "</div>";
		echo "<h3>Section 4</h3>";
		echo "<div style='padding:0 15px;'>";
		echo "<ul>";
		echo "<li>List item one</li>";
		echo "<li>List item two</li>";
		echo "<li>List item three</li>";
		echo "</ul>";
		echo "</div>";
		echo "</div>";
		echo "</td>";
		echo "<td valign='top' style='padding:2px;' width='66%'>";
		echo "<table class='tabla width100 ui-widget'>";
		echo "<tr>";
		echo "<td class='thead ui-widget-header ui-corner-tl'>Field 1</td>";
		echo "<td class='thead ui-widget-header'>Field 2</td>";
		echo "<td class='thead ui-widget-header ui-corner-tr'>Field 3</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td class='tbody ui-widget-content'>asdfsdf</td>";
		echo "<td class='tbody ui-widget-content'>fdhfghssgh</td>";
		echo "<td class='tbody ui-widget-content'>23452345234</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td class='tbody ui-state-default'>asdf</td>";
		echo "<td class='tbody ui-state-default'>hsfgsfghsfg</td>";
		echo "<td class='tbody ui-state-default'>23452345</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td class='tbody ui-widget-content'>sdf</td>";
		echo "<td class='tbody ui-widget-content'>dfshg</td>";
		echo "<td class='tbody ui-widget-content'>23523452</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td class='tbody ui-state-default'>ghdjd</td>";
		echo "<td class='tbody ui-state-default'>sdfgg</td>";
		echo "<td class='tbody ui-state-default'>23452345</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td class='tbody ui-widget-content'>shgsdfh</td>";
		echo "<td class='tbody ui-widget-content'>erwsdf</td>";
		echo "<td class='tbody ui-widget-content'>32542354</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td class='tbody ui-state-default'>fghs</td>";
		echo "<td class='tbody ui-state-default'>shsgsfg</td>";
		echo "<td class='tbody ui-state-default'>234534245234</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td class='tbody ui-widget-content'>sfhsfghsdfh</td>";
		echo "<td class='tbody ui-widget-content'>sfhg</td>";
		echo "<td class='tbody ui-widget-content'>523452345</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td class='tbody ui-state-default'>sdfgsdfg</td>";
		echo "<td class='tbody ui-state-default'>fgsdfgsdf</td>";
		echo "<td class='tbody ui-state-default'>2542345234</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td class='tbody ui-widget-content'>sdfgsdf</td>";
		echo "<td class='tbody ui-widget-content'>sdfgsdfg</td>";
		echo "<td class='tbody ui-widget-content'>23452345234</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td class='tbody ui-state-default ui-corner-bl'>sdfgsdfgs</td>";
		echo "<td class='tbody ui-state-default'>ghdfghd</td>";
		echo "<td class='tbody ui-state-default ui-corner-br'>235423452354</td>";
		echo "</tr>";
		echo "</table>";
		//~ echo "<div id='datepicker'></div>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<script type='text/javascript' src='lib/jquery/jquery.min.js?r=${revision}'></script>";
		echo "<script type='text/javascript' src='lib/jquery/jquery-ui.min.js?r=${revision}'></script>";
		echo "<script type='text/javascript'>$(function(){";
		echo "$('table.none').removeClass('none');";
		echo "$('#tabs').tabs();";
		echo "$('#accordion').accordion({ icons:{ header:'ui-icon-circle-arrow-e', activeHeader:'ui-icon-circle-arrow-s' }});";
		//~ echo "$('#datepicker').datepicker();";
		echo "});</script>";
		echo "</body>";
		echo "</html>";
	} else {
		$styles=eval_attr(xml2array("xml/common/styles.xml"));
		$revision=getDefault("info/revision");
		echo "<!DOCTYPE html>";
		echo "<html>";
		echo "<head>";
		echo "<link href='css/default.css?r=${revision}' rel='stylesheet' type='text/css'>";
		echo "<style>";
		echo "iframe{width:600px; height:300px; margin:10px; border:1px solid #333;}";
		echo "</style>";
		echo "</head>";
		echo "<body>";
		foreach($styles["rows"] as $row) {
			echo "<iframe src='?action=styles&amp;style=${row["value"]}' frameborder='0'></iframe>";
		}
		echo "</body>";
		echo "</html>";
	}
	die();
}
?>
