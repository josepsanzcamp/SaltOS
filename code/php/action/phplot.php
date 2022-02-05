<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2013 by Josep Sanz Campderrós
More information in https://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

if (!check_user()) {
    action_denied();
}

// GET DATA FROM QUERYSTRING
$width = intval(getParam("width"));
$height = intval(getParam("height"));
$title = getParam("title");
$legend = explode("|", getParam("legend"));
$vars = intval(getParam("vars"));
$colors = explode("|", getParam("colors"));
$graph = getParam("graph");
$ticks = explode("|", getParam("ticks"));
$posx = explode("|", getParam("posx"));
$data = array();
for ($i = 1; $i <= $vars; $i++) {
    $data[$i] = explode("|", getParam("data$i"));
}
// LOADING CONTROL
$format = getParam("format", "json");
if (!in_array($format, array("png","json"))) {
    action_denied();
}
$loading = getParam("loading");
// CACHE CONTROL
$cache = get_cache_file(
    array($width,$height,$title,$legend,$vars,$colors,$graph,$ticks,$posx,$data,$format,$loading),
    $format
);
//if(file_exists($cache)) unlink($cache); // ONLY FOR TESTS PURPOSES AND TODO REMOVED
if (!file_exists($cache)) {
    // BEGIN THE PHPLOT WRAPPER
    require_once "lib/phplot/phplot.php";
    require_once "lib/phplot/contrib/color_range.php";
    require_once "lib/phplot/rgb.inc.php";
    // REPAIR SOME DATA
    if ($legend[count($legend) - 1] == "") {
        array_pop($legend);
    }
    if ($colors[count($colors) - 1] == "") {
        array_pop($colors);
    }
    if ($ticks[count($ticks) - 1] == "") {
        array_pop($ticks);
    }
    if ($posx[count($posx) - 1] == "") {
        array_pop($posx);
    }
    for ($i = 1; $i <= $vars; $i++) {
        if ($data[$i][count($data[$i]) - 1] == "") {
            array_pop($data[$i]);
        }
    }
    // REMOVE SOME DATA IF GRAPH IS PIE
    if ($graph == "pie") {
        $newticks = array();
        $newdata = array();
        for ($i = 1; $i <= $vars; $i++) {
            $newdata[$i] = array();
        }
        foreach ($data[1] as $key => $val) {
            if (floatval($val) > 0) {
                $newticks[] = $ticks[$key];
                for ($i = 1; $i <= $vars; $i++) {
                    $newdata[$i][] = $data[$i][$key];
                }
            }
        }
        if (count($newticks)) {
            $ticks = $newticks;
            for ($i = 1; $i <= $vars; $i++) {
                $data[$i] = $newdata[$i];
            }
        } else {
            $graph = "error";
        }
    }
    // MOUNT THE CORRECT DATA STRUCT
    if ($graph != "error") {
        $datatype = (count($ticks) == count($posx)) ? "data-data" : "text-data";
        $count = count($ticks);
        $values = array();
        $hastick = 1;
        $hasdata = 0;
        $last = "";
        for ($j = 0; $j < $count; $j++) {
            $value = array();
            if ($datatype == "text-data") {
                $value[] = $ticks[$j];
                $hastick &= $ticks[$j] != "";
            }
            if ($datatype == "data-data") {
                $value[] = ($last != $ticks[$j]) ? $ticks[$j] : "";
                $value[] = $posx[$j];
                $hastick &= $ticks[$j] != "";
                $hastick &= $posx[$j] != "";
                $last = $ticks[$j];
            }
            for ($i = 1; $i <= $vars; $i++) {
                $value[] = $data[$i][$j];
                $hasdata |= $data[$i][$j] != "";
            }
            $values[] = $value;
        }
        if (!$hastick || !$hasdata) {
            $graph = "error";
        }
    }
    // CALCULATE THE PRECISION
    if ($graph != "error") {
        $maxvalue = max($data[1]);
        $minvalue = min($data[1]);
        for ($i = 2; $i <= $vars; $i++) {
            $maxvalue = max(max($data[$i]), $maxvalue);
            $minvalue = min(min($data[$i]), $minvalue);
        }
        $diff = $maxvalue - $minvalue;
        if ($diff <= 1) {
            $precision = 2;
        } elseif ($diff <= 10) {
            $precision = 1;
        } else {
            $precision = 0;
        }
        $maxvalue2 = $maxvalue + $diff * 0.1;
        $maxvalue = ($maxvalue <= 1 && $maxvalue2 >= 1) ? 1 :
            (($maxvalue <= 100 && $maxvalue2 >= 100) ? 100 : $maxvalue2);
        $minvalue2 = $minvalue - $diff * 0.1;
        $minvalue = ($minvalue >= 0 && $minvalue2 <= 0) ? 0 : $minvalue2;
    }
    // MAKE PLOT
    require_once "php/libaction.php";
    $plot = new PHPlot_truecolor($width, $height);
    $plot->SetFailureImage(false);
    $plot->SetPrintImage(false);
    $plot->SetImageBorderType("plain");
    if (!isset($values)) {
        $values = array();
    }
    capture_next_error();
    $plot->SetDataValues($values);
    $graph = get_clear_error() ? "error" : $graph;
    $font = getcwd() . "/lib/fonts/DejaVuSans.ttf";
    $plot->SetDefaultTTFont($font);
    $plot->SetBgImage("img/defplot.png", "centeredtile");
    $plot->SetFailureImage(false);
    // SET THE SIZES OF ALL FONTS
    $elems = array("generic","title","legend","x_label","y_label","x_title","y_title");
    $sizes = array(7,8,7,6,6,7,7);
    foreach ($elems as $key => $elem) {
        $plot->SetFont($elem, "", $sizes[$key]);
    }
    // CALC THE COLORS TO PLOT CURRENT DATA
    $plot->setRGBArray($ColorArray);
    if (count($colors) > 0) {
        $intervals = ($graph == "pie") ? $count : $vars;
        if (count($colors) == 2 && $intervals > 2) {
            $color1 = isset($ColorArray[$colors[0]]) ? $colors[0] : "white";
            $color2 = isset($ColorArray[$colors[1]]) ? $colors[1] : "black";
            $color1 = $ColorArray[$color1];
            $color2 = $ColorArray[$color2];
            $colors = color_range($color1, $color2, $intervals);
        }
        $plot->SetDataColors($colors);
    }
    // FOR BARS PLOT
    if ($graph == "bars") {
        $plot->SetPlotType("bars");
        $plot->SetDataType($datatype);
        $plot->SetCallback("data_points", "__phplot_callback_for_bars", $values);
        $plot->SetTitle($title);
        if (isset($legend[0]) && $legend[0] != "") {
            $plot->SetLegend($legend);
            list($width2,$height2) = $plot->GetLegendSize();
            $plot->SetMarginsPixels(null, $width2 + 10, null, null);
            $plot->SetLegendPosition(0, 0.5, "plot", 1, 0.5, 5, 0);
        }
        $plot->SetPlotAreaWorld(null, 0, null, null);
        $plot->SetYLabelType("data");
        $plot->SetPrecisionY($precision);
        $plot->SetYDataLabelPos("plotin");
        $plot->SetXTickLabelPos("none");
        $plot->SetXTickPos("none");
        $plot->SetXDataLabelAngle(45);
    }
    // FOR POINTS PLOT
    if ($graph == "points") {
        $plot->SetPlotType("linepoints");
        $plot->SetDataType($datatype);
        $plot->SetCallback("data_points", "__phplot_callback_for_points", $values);
        $plot->SetTitle($title);
        if (isset($legend[0]) && $legend[0] != "") {
            $plot->SetLegend($legend);
            list($width2,$height2) = $plot->GetLegendSize();
            $plot->SetMarginsPixels(null, $width2 + 10, null, null);
            $plot->SetLegendPosition(0, 0.5, "plot", 1, 0.5, 5, 0);
        }
        if ($minvalue < $maxvalue) {
            $plot->SetPlotAreaWorld(null, $minvalue, null, $maxvalue);
        }
        $plot->SetYLabelType("data");
        $plot->SetPrecisionY($precision);
        $plot->SetYDataLabelPos("plotin");
        $plot->SetXTickLabelPos("none");
        $plot->SetXTickPos("none");
        $plot->SetXDataLabelAngle(45);
        $plot->SetLineWidths(2);
        $plot->SetLineStyles("solid");
    }
    // FOR PIE PLOT
    if ($graph == "pie") {
        $plot->SetPlotType("pie");
        $plot->SetDataType("text-data-single");
        $plot->SetCallback("data_points", "__phplot_callback_for_pie", $values);
        $plot->SetTitle($title);
        foreach ($values as $row) {
            $plot->SetLegend($row[0]);
        }
        list($width2,$height2) = $plot->GetLegendSize();
        $plot->SetMarginsPixels(null, $width2 + 10, null, null);
        $plot->SetLegendPosition(0, 0.5, "plot", 1, 0.5, 0, 0);
    }
    // FOR LINES PLOT
    if ($graph == "lines") {
        $plot->SetPlotType("lines");
        $plot->SetDataType($datatype);
        $plot->SetTitle($title);
        if (isset($legend[0]) && $legend[0] != "") {
            $plot->SetLegend($legend);
            list($width2,$height2) = $plot->GetLegendSize();
            $plot->SetMarginsPixels(null, $width2 + 10, null, null);
            $plot->SetLegendPosition(0, 0.5, "plot", 1, 0.5, 5, 0);
        }
        if ($minvalue < $maxvalue) {
            $plot->SetPlotAreaWorld(null, $minvalue, null, $maxvalue);
        }
        $plot->SetYLabelType("data");
        $plot->SetPrecisionY($precision);
        $plot->SetYDataLabelPos("none");
        $plot->SetXTickLabelPos("none");
        $plot->SetXTickPos("none");
        $plot->SetXDataLabelAngle(45);
        $plot->SetLineWidths(2);
        $plot->SetLineStyles("solid");
    }
    // FOR FINANCIAL PLOT
    if ($graph == "ohlc") {
        $plot->SetPlotType("candlesticks2");
        $plot->SetDataType($datatype);
        $plot->SetTitle($title);
        if (isset($legend[0]) && $legend[0] != "") {
            $plot->SetLegend($legend);
            list($width2,$height2) = $plot->GetLegendSize();
            $plot->SetMarginsPixels(null, $width2 + 10, null, null);
            $plot->SetLegendPosition(0, 0.5, "plot", 1, 0.5, 5, 0);
        }
        $plot->SetDataColors(array("red","DarkGreen","red","DarkGreen"));
        if ($minvalue < $maxvalue) {
            $plot->SetPlotAreaWorld(null, $minvalue, null, $maxvalue);
        }
        $plot->SetYLabelType("data");
        $plot->SetPrecisionY($precision);
        $plot->SetYDataLabelPos("plotin");
        $plot->SetXTickLabelPos("none");
        $plot->SetXTickPos("none");
        $plot->SetXDataLabelAngle(45);
    }
    // MAKE THE IMAGE
    $_RESULT = array("img" => "","map" => array());
    if ($graph != "error") {
        capture_next_error();
        $plot->DrawGraph();
        if (get_clear_error()) {
            $graph = "error";
        }
    }
    if ($graph == "error") {
        $plot->SetFont("generic", "", 10);
        $options = array(
            "draw_background" => true,
            "draw_border" => true,
            "force_print" => false,
            "reset_font" => false
        );
        $plot->DrawMessage(LANG($loading ? "loading" : "withoutinfo"), $options);
    }
    if ($format == "png") {
        ob_start();
        $plot->PrintImage();
        $buffer = ob_get_clean();
    }
    if ($format == "json") {
        $_RESULT["img"] = $plot->EncodeImage();
        //$plot->PrintImage(); die();
        $_RESULT["map"] = array_values($_RESULT["map"]);
        $buffer = json_encode($_RESULT);
    }
    file_put_contents($cache, $buffer);
    chmod($cache, 0666);
}
output_handler(array(
    "file" => $cache,
    "cache" => true
));
