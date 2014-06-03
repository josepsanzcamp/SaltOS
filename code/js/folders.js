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

if(typeof(__folders__)=="undefined" && typeof(parent.__folders__)=="undefined") {
	"use strict";
	var __folders__=1;

	function addregfolder(id_folder,page,id_registro) {
		var data='action=ajax&query=addregfolder&id_folder='+id_folder+'&page='+page+'&id_registro='+id_registro;
		$.ajax({
			url:'',
			data:data,
			type:"get",
			success:function(response) {
				notice(lang_alert(),lang_addtofolderok());
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

	function delregfolder(id_folder,page,id_registro) {
		var data='action=ajax&query=delregfolder&id_folder='+id_folder+'&page='+page+'&id_registro='+id_registro;
		$.ajax({
			url:'',
			data:data,
			type:"get",
			success:function(response) {
				notice(lang_alert(),lang_delfromfolderok());
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

	function delappfolder(id_folder,page) {
		var data='action=ajax&query=delappfolder&id_folder='+id_folder+'&page='+page;
		$.ajax({
			url:'',
			data:data,
			type:"get",
			success:function(response) {
				notice(lang_alert(),lang_delfromfolderok());
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

	function swapregfolder(obj) {
		var id=$("input[name$=id]",obj.parentNode.parentNode).val();
		if(obj.checked) addregfolder(id,getParam("page"),getParam("id")); else delregfolder(id,getParam("page"),getParam("id"));
	}

	function dropregfolder(id_registro,id_folder) {
		var page=getParam("page");
		if(in_array(page,new Array("folders","ficheros"))) return;
		addregfolder(id_folder,page,id_registro);
	}

}
