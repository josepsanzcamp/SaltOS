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

if(typeof(__viewpdf__)=="undefined" && typeof(parent.__viewpdf__)=="undefined") {
	"use strict";
	var __viewpdf__=1;

	var viewpdf_history=[];

	// ORIGINAL IDEA FROM pdf.js/examples/components/simpleviewer.html
	function viewpdf(data) {
		viewpdf_history.push(data);
		loadingcontent(lang_view2opening());
		var data="action=viewpdf&"+data;
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			beforeSend:function(XMLHttpRequest) {
				make_abort_obj=XMLHttpRequest;
			},
			success:function(response) {
				// CHECK FOR VALID XML STRUCTURE
				if(response["rows"].length==0) {
					var hash=md5(0);
					unloadingcontent();
					var br="<br/>";
					dialog(lang_error(),lang_view2error()+br+br+lang_view2hash()+hash,{});
					return;
				}
				// CONTINUE
				$(response["rows"]).each(function() {
					// GET REQUESTED DATA
					var title=this["title"];
					var hash=this["hash"];
					var data=this["data"];
					// CHECK FOR VALID DATA
					if(!strlen(data)) {
						unloadingcontent();
						var br="<br/>";
						dialog(lang_error(),lang_view2error()+br+br+lang_view2hash()+hash,{});
						return;
					}
					// CREATE PDFDOC
					pdfjsLib.GlobalWorkerOptions.workerSrc="lib/pdfjs/pdf.worker.min.js?r="+current_revision();
					pdfjsLib.getDocument({data:atob(data)}).promise.then(function(pdfDocument) {
						unloadingcontent();
						// CHECK FOR NUMPAGES>0
						if(!pdfDocument.numPages) {
							var br="<br/>";
							dialog(lang_error(),lang_view2error()+br+br+lang_view2hash()+hash,{});
							return;
						}
						// BEGIN OPEN DIALOG
						dialog(lang_view2()+" - "+title);
						var dialog2=$("#dialog");
						$(dialog2).html("<div id='viewerContainer'><div id='viewer' class='pdfViewer'></div></div>");
						// PROGRAM RESIZE EVENT
						$(dialog2).dialog("option","resizeStop",function(event,ui) {
							setIntCookie("saltos_viewpdf_width",$(dialog2).dialog("option","width"));
							setIntCookie("saltos_viewpdf_height",$(dialog2).dialog("option","height"));
							pdfViewer.currentScaleValue="page-width";
						});
						// PROGRAM CLOSE EVENT
						$(dialog2).dialog("option","close",function(event,ui) {
							$(dialog2).dialog("option","resizeStop",function() {});
							$(dialog2).dialog("option","close",function() {});
							$("*",dialog2).each(function() { $(this).remove(); });
							while(viewpdf_history.length>0) viewpdf_history.shift();
							document.removeEventListener("pagesinit",fn1);
							document.removeEventListener("textlayerrendered",fn2);
						});
						// UPDATE SIZE AND POSITION
						var width=getIntCookie("saltos_viewpdf_width");
						if(!width) width=900;
						$(dialog2).dialog("option","width",width);
						var height=getIntCookie("saltos_viewpdf_height");
						if(!height) height=600;
						$(dialog2).dialog("option","height",height);
						// END OPEN DIALOG
						$(dialog2).dialog("option","position",{ my:"center",at:"center",of:window });
						$(dialog2).dialog("open");
						// FOR THE HISTORY BUTTON
						var titlebar=$(".ui-dialog-titlebar");
						while($("button",titlebar).length>1) $("button:last",titlebar).remove();
						if(viewpdf_history.length>1) {
							$(titlebar).append("<button role='button' class='ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close' type='button'><span class='ui-button-icon-primary ui-icon ui-icon-triangle-1-w'></span><span class='ui-button-text'>Back</span></button>");
							$("button:last",titlebar).css("margin-right","22px").on("click",function() {
								viewpdf_history.pop();
								viewpdf(viewpdf_history.pop());
							});
						}
						// PAINT ALL PAGES
						var container=document.getElementById("viewerContainer");
						var pdfViewer=new pdfjsViewer.PDFViewer({
							container:container
						});
						var fn1=function() {
							pdfViewer.currentScaleValue="page-width";
						};
						var fn2=function() {
							$("a",container).each(function() {
								if(substr($(this).attr("href"),0,15)=="http://viewpdf/") {
									if(typeof($(this).attr("onclick"))=="undefined") {
										$(this).attr("onclick","viewpdf('"+substr($(this).attr("href"),15)+"');return false");
									}
								} else {
									if(typeof($(this).attr("target"))=="undefined") {
										$(this).attr("target","_blank");
									}
									if($(this).attr("target")=="") {
										$(this).attr("target","_blank");
									}
								}
							});
						};
						document.addEventListener("pagesinit",fn1);
						document.addEventListener("textlayerrendered",fn2);
						pdfViewer.setDocument(pdfDocument);
						setTimeout(function() {
							$(dialog2).scrollTop(0);
						},100);
					},function(message,exception) {
						errorcontent(0,message);
					});
				});
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

}
