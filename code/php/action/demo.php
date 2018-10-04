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
if(getParam("action")=="demo") {
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
		echo "<link href='?action=cache&files=${stylepre}${style}${stylepost}&r=${revision}' rel='stylesheet' type='text/css'>";
		echo "<link href='?action=cache&files=css/default.css,css/correo.css,lib/fontawesome/css/fontawesome.min.css,lib/fontawesome/css/solid.min.css,lib/fontawesome/css/v4-shims.min.css&r=${revision}' rel='stylesheet' type='text/css'>";
		echo "</head>";
		echo "<body>";
		echo str_replace("App name - Suite de Gesti&oacute;n Empresarial - SaltOS v3.7 r8716",$row["label"]." - ".getDefault("info/title")." - ".get_name_version_revision(),file_get_contents("xml/common/demo.xml"));
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
		echo "iframe{width:900px; height:600px; margin:10px; border:1px solid #333;}";
		echo "</style>";
		echo "</head>";
		echo "<body>";
		foreach($styles["rows"] as $row) {
			echo "<iframe src='?action=demo&amp;style=${row["value"]}' frameborder='0'></iframe>";
		}
		echo "</body>";
		echo "</html>";
	}
	die();
}
?>