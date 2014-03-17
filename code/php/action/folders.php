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
if($page=="profile") {
	if(!function_exists("update_folders_tree")) {
		function update_folders_tree($id_usuario,$id_parent=0,&$pos=0,$depth=0) {
			$query="SELECT id FROM tbl_folders WHERE id_usuario='${id_usuario}' AND id_parent='${id_parent}' ORDER BY name ASC";
			$result=db_query($query);
			while($row=db_fetch_row($result)) {
				$query="UPDATE tbl_folders SET pos=${pos},depth=${depth} WHERE id_usuario='${id_usuario}' AND id=${row["id"]}";
				db_query($query);
				$pos++;
				update_folders_tree($id_usuario,$row["id"],$pos,$depth+1);
			}
			db_free($result);
		}
	}
	update_folders_tree(current_user());
}
?>