<?php
/* CAT:Spline chart */

/* pChart library inclusions */
include("pChart2.1.4/class/pData.class.php");
include("pChart2.1.4/class/pDraw.class.php");
include("pChart2.1.4/class/pImage.class.php");

$points = explode(',', $_GET['record']);

$canvasHeight = 618;
$canvasWidth = 1000;
$lrMargin = 60;
$times = array();
for($i = 0; $i < count($points); $i++) {
    $points[$i] /=  1000;
    $times[] = ($i + 1);
}

/* Create and populate the pData object */
$MyData = new pData();  
$MyData->addPoints($points,"Probe 1");
$MyData->setAxisName(0,"Time");
$MyData->setAxisUnit(0,"s");
$MyData->addPoints($times,"Labels");
$MyData->setSerieDescription("Labels","Times");
$MyData->setAbscissa("Labels");

/* Create the pChart object */
$myPicture = new pImage($canvasWidth,$canvasHeight,$MyData);

/* Draw the background */
$Settings = array("R"=>50, "G"=>87, "B"=>183);
$myPicture->drawFilledRectangle(0,0,$canvasWidth,$canvasHeight,$Settings);

/* Add a border to the picture */
$myPicture->drawRectangle(0,0,($canvasWidth - 1),($canvasHeight - 1),array("R"=>0,"G"=>0,"B"=>0));
 
/* Write the chart title */ 
$myPicture->setFontProperties(array("FontName"=>"pChart2.1.4/fonts/Forgotte.ttf","FontSize"=>11));
$myPicture->drawText(($canvasWidth / 2),($canvasHeight / 16),"My Rubik's Cube Records",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE, "R"=>255, "G"=>255, "B"=>255));

/* Draw the scale and the 1st chart */
$myPicture->setGraphArea($lrMargin,($canvasHeight / 10),($canvasWidth - $lrMargin),($canvasHeight - $lrMargin));
$myPicture->drawFilledRectangle($lrMargin,($canvasHeight / 10),($canvasWidth - $lrMargin),($canvasHeight - $lrMargin),array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10));
$myPicture->drawScale(array("DrawSubTicks"=>TRUE));
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
$myPicture->setFontProperties(array("FontName"=>"pChart2.1.4/fonts/Forgotte.ttf","FontSize"=>20));
$myPicture->drawSplineChart(array("DisplayValues"=>False,"DisplayColor"=>DISPLAY_MANUAL));
$myPicture->setShadow(FALSE);

/* Render the picture (choose the best way) */
$myPicture->autoOutput("pictures/example.drawSplineChart.png");
 
// Convert stdClass Object to array
function object_array($array){
    if(is_object($array)){
        $array = (array)$array;
    }
    if(is_array($array)){
        foreach($array as $key=>$value){
            $array[$key] = object_array($value);
        }
    }
    return $array;
}
?>
