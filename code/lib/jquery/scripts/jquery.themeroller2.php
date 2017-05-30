<?php
$texto="
name h3.ui-accordion-header.ui-state-default{border-color:#123456;}
name div.ui-accordion-content.ui-widget-content{border-color:#123456;}
name h3.ui-accordion-header.ui-state-default{background-color:#234567;}
name h3.ui-accordion-header.ui-state-default a{color:#ffffff;}
name h3.ui-accordion-header.ui-state-default .ui-icon{background-image:url(\"images/ui-icons_ffffff_256x240.png\");}
name h3.ui-accordion-header.ui-state-hover{background:#fffa90;}
name h3.ui-accordion-header.ui-state-hover a{color:#333333;}
name h3.ui-accordion-header.ui-state-hover .ui-icon{background-image:url(\"images/ui-icons_444444_256x240.png\");}
";
$colores=array(
	//~ array("blue","1967be","2780e3"),
	//~ array("green","4d7f17","6bb120"),
	//~ array("red","b62020","cb2424"),
	//~ array("purple","660066","800080"),
	//~ array("facebook","1d3469","3d5c95"),
	//~ array("darkred","560808","7a0a0a"),
	//~ array("gray","333333","444444"),
	array("blue","1967be","2780e3"),
	array("green","379F15","3FB618"),
	array("violet","8D46B0","9954BB"),
	array("orange","FE6600","FF7518"),
	array("red","E60033","FF0039"),
	array("gray","090909","222222"),
);
foreach($colores as $color) {
	$color[0]=".".$color[0];
	echo str_replace(array("name","123456","234567"),$color,$texto);
}
//~ $grupos=array(
	//~ "gestiongeneral",
	//~ "folders",
	//~ "gestioncomercial",
	//~ "gestionproyectos",
	//~ "gestioncontabilidad",
	//~ "gestionadministracion",
	//~ "gestiontipos",
	//~ "gestionsistema",
	//~ "calculator",
	//~ "translate",
//~ );
//~ foreach($grupos as $indice=>$grupo) {
	//~ $indice=($indice+1)%count($colores);
	//~ $color=$colores[$indice];
	//~ $color[0]="#".$grupo;
	//~ echo str_replace(array("name","123456","234567"),$color,$texto);
//~ }
?>