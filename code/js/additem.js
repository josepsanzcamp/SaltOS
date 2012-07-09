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

if(typeof(__additem__)=="undefined" && typeof(parent.__additem__)=="undefined") {
	"use strict";
	var __additem__=1;

	function additem(obj) {
		var padre=$(obj).parent().parent().parent().parent().parent();
		var table=$("table.tabla",padre);
		var limit=$("tr",table).length;
		var num=$("tr:visible2",table).length;
		if(num+1<=limit) {
			$("tr:eq("+num+")",table).show();
			make_tables(padre);
			if(num+1==limit) $(obj).addClass("ui-state-disabled");
		}
	}

}

"use strict";
$(document).ready(function() {
	$(".init_additem").each(function() {
		var padre=$(this).parent().parent().parent().parent().parent();
		var temp=$("input[type=hidden][name$=id]",padre);
		var total=0;
		$(temp).each(function() {
			if(is_numeric(this.value)) {
				$(this).parent().show();
				total++;
			}
		});
		if(!total) additem(this);
	});
});
