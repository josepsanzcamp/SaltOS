/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2012 by Josep Sanz Campderrós
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

if(typeof(__updateclientes__)=="undefined" && typeof(parent.__updateclientes__)=="undefined") {
	"use strict";
	var __updateclientes__=1;

	function update_clientes(obj) {
		var posiblecli=$("select[name$=id_posiblecli]");
		var cliente=$("select[name$=id_cliente]");
		if(obj!=posiblecli[0]) $(posiblecli).val("0");
		if(obj!=cliente[0]) $(cliente).val("0");
	}

}
