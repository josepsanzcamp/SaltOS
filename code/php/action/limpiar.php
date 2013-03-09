<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2013 by Josep Sanz Campderrós
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
if(getParam("action")=="limpiar") {
	$id_usuario=current_user();
	$id_folder=intval(getParam("id_folder"));
	$arguments="?page=$page&limpiar=1";
	if($page=="correo") $arguments.="&id_usuario=$id_usuario";
	if($page=="agenda") $arguments.="&id_asignado=$id_usuario";
	if($page=="feeds") $arguments.="&id_usuario=$id_usuario";
	if($page=="ficheros") $arguments.="&id_usuario=$id_usuario";
	if($page=="folders") $arguments.="&id_folder=$id_folder";
	if($page=="buscador") $arguments.="&order=datetime+DESC";
	javascript_location_base($arguments);
	die();
}
?>