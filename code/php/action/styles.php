<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2017 by Josep Sanz Campderrós
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
		echo "<html>";
		echo "<head>";
		echo "<link href='${stylepre}${style}${stylepost}?r=${revision}' rel='stylesheet' type='text/css'>";
		echo "<link href='css/default.css?r=${revision}' rel='stylesheet' type='text/css'>";
		echo "<style>";
		//~ echo "body{background:#fff;}";
		echo "table{font-size:10px;}";
		echo "#datepicker{margin:20px 0 0 0;}";
		echo "ul{padding-left:0;}";
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
		echo "<td valign='top'>";
		echo "<div id='accordion'>";
		echo "<h3>Section 1</h3>";
		echo "<div>";
		echo "<ul>";
		echo "<li>List item one</li>";
		echo "<li>List item two</li>";
		echo "<li>List item three</li>";
		echo "</ul>";
		echo "</div>";
		echo "<h3>Section 2</h3>";
		echo "<div>";
		echo "<ul>";
		echo "<li>List item one</li>";
		echo "<li>List item two</li>";
		echo "<li>List item three</li>";
		echo "</ul>";
		echo "</div>";
		echo "<h3>Section 3</h3>";
		echo "<div>";
		echo "<ul>";
		echo "<li>List item one</li>";
		echo "<li>List item two</li>";
		echo "<li>List item three</li>";
		echo "</ul>";
		echo "</div>";
		echo "<h3>Section 4</h3>";
		echo "<div>";
		echo "<ul>";
		echo "<li>List item one</li>";
		echo "<li>List item two</li>";
		echo "<li>List item three</li>";
		echo "</ul>";
		echo "</div>";
		echo "</div>";
		echo "</td>";
		echo "<td valign='top' align='center'>";
		echo "<div id='datepicker'></div>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<script type='text/javascript' src='lib/jquery/jquery.min.js?r=${revision}'></script>";
		echo "<script type='text/javascript' src='lib/jquery/jquery-ui.min.js?r=${revision}'></script>";
		echo "<script type='text/javascript'>$(function(){";
		echo "$('table.none').removeClass('none');";
		echo "$('#tabs').tabs();";
		echo "$('#accordion').accordion({ icons:{ header:'ui-icon-circle-arrow-e', activeHeader:'ui-icon-circle-arrow-s' }});";
		echo "$('#datepicker').datepicker();";
		echo "});</script>";
		echo "</body>";
		echo "</html>";
	} else {
		$styles=eval_attr(xml2array("xml/common/styles.xml"));
		$revision=getDefault("info/revision");
		echo "<html>";
		echo "<head>";
		echo "<link href='css/default.css?r=${revision}' rel='stylesheet' type='text/css'>";
		echo "<style>";
		echo "iframe{width:450px; height:300px; margin:10px; border:1px solid #333;}";
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