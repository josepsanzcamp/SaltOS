/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2021 by Josep Sanz Campderrós
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

    // ORIGINAL IDEA FROM pdf.js/examples/components/simpleviewer.html
    function viewpdf(data) {
        hide_popupdialog();
        loadingcontent(lang_view2opening());
        var data2="action=viewpdf&"+data;
        $.ajax({
            url:"index.php",
            data:data2,
            type:"get",
            beforeSend:function(XMLHttpRequest) {
                make_abort_obj=XMLHttpRequest;
            },
            success:function(response) {
                // CHECK FOR VALID JSON STRUCTURE
                if(!is_array(response) || !count(response) || !isset(response["title"]) || !isset(response["hash"]) || !isset(response["data"])) {
                    var hash=md5(0);
                    unloadingcontent();
                    var br="<br/>";
                    dialog(lang_error(),lang_view2error()+br+br+lang_view2hash()+hash,{});
                    return;
                }
                // GET REQUESTED DATA
                var title=response["title"];
                var hash=response["hash"];
                var data3=response["data"];
                // CHECK FOR VALID DATA
                if(!strlen(data3)) {
                    unloadingcontent();
                    var br="<br/>";
                    dialog(lang_error(),lang_view2error()+br+br+lang_view2hash()+hash,{});
                    return;
                }
                // CREATE PDFDOC
                pdfjsLib.GlobalWorkerOptions.workerSrc="lib/pdfjs/pdf.worker.min.js?r="+current_revision();
                pdfjsLib.getDocument({data:atob(data3)}).promise.then(function(pdfDocument) {
                    unloadingcontent();
                    // CHECK FOR NUMPAGES>0
                    if(!pdfDocument.numPages) {
                        var br="<br/>";
                        dialog(lang_error(),lang_view2error()+br+br+lang_view2hash()+hash,{});
                        return;
                    }
                    // BEGIN OPEN DIALOG
                    dialog(lang_view2()+" - "+title,"",[{
                        text:lang_download(),
                        icon:icon_download(),
                        click:function() {
                            openurl("index.php?"+data2+"&download=1");
                        }
                    },{
                        text:lang_print(),
                        icon:icon_print(),
                        click:function() {
                            openwin("index.php?"+data2+"&print=1");
                        }
                    }]);
                    // TO PREVENT FOCUS IN THE BUTTONS
                    $(dialog).find(".fa.ui-icon").parent().attr("tabindex","-1");
                    // TO ALLOW FONT AWESOME INSTEOAD OF JQUERY UI ICONS
                    $(dialog).find(".fa.ui-icon").removeClass("ui-icon");
                    // CONTINUE
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
                        document.removeEventListener("pagesinit",fn1);
                        document.removeEventListener("textlayerrendered",fn2);
                        unmake_focus();
                        hide_tooltips();
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
                    // PAINT ALL PAGES
                    var container=document.getElementById("viewerContainer");
                    var eventBus=new pdfjsViewer.EventBus();
                    var pdfViewer=new pdfjsViewer.PDFViewer({
                        container:container,
                        eventBus:eventBus,
                    });
                    var fn1=function() {
                        pdfViewer.currentScaleValue="page-width";
                        $("#viewerContainer").scrollTop(0);
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
                    eventBus.on("pagesinit",fn1);
                    eventBus.on("textlayerrendered",fn2);
                    pdfViewer.setDocument(pdfDocument);
                },function(message,exception) {
                    errorcontent(0,message);
                });
            },
            error:function(XMLHttpRequest,textStatus,errorThrown) {
                errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
            }
        });
    }

}
