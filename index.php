<!DOCTYPE html>
<html>
<head>
<?php
$appVersion = "1.0";
$isMobile = substr_count(strtolower($_SERVER['HTTP_USER_AGENT']), 'mobile');
?>
<title>Hank's Stopwatch v<?php echo $appVersion; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="http://code.jquery.com/jquery-latest.js"></script>
<style>
@font-face {
    font-family: CRYSTAL;
    src: url("./CRYSTAL.TTF");
}
div {
    text-align: center;
}
table {
    text-align: center;
}
.title {
    opacity: 0;
}
.hrs {
    opacity: 0;
}
.stopwatch {
    width: auto;
    color: green;
    font-size: 13vw;
    font-weight: bold;
    margin: 20px;
    padding: 20px;
    opacity: 0; 
    display: none;
}
.imgChart {
    display: none;
}
.shuffle {
    font-weight: bold;
    margin: 20px 0;
}
.btns {
}
.stats {
    background: #EEE;
    border: 1px solid #555;
    border-radius: 3px;
    padding: 0 10px;
    margin: auto 10px;
}
.recordTable {
    margin-top: 10px;
    margin-bottom: 10px;
    opacity: 0;
}
.record {
    opacity: 0;
    padding: 5px 10px;
}
.isNormal {
    color: black;
    font-weight: normal;
}
.isFastest {
    color: blue;
    font-weight: bold;
}
.isSlowest {
    color: red;
    font-weight: bold;
}
.highlightEst {
    background: #CCC;
}
.noHighlightEst {
    background: white;
}
</style>
</head>
<body onload="initialAll()" onkeydown="timestop(event)" onkeyup="timestart(event)" ontouchstart="timestop(event)" ontouchend="timestart(event)">
<div class="title">@Hank's Stopwatch v<?php echo $appVersion; ?> for Rubik's Cube</div>
<form id="exportJson" action="export.php" method="post">
    <input id="exportJsonField" name="exportJsonField" type="hidden" />
</form>
<form id="chartJson" action="chart.php" method="post">
    <input id="chartJsonField" name="chartJsonField" type="hidden" />
</form>
<hr class="hrs" />
<div class="stopwatch">00:00.000</div>
<?php
if(!$isMobile) {
?>
<div class="imgChart">
    <img id="imgChart" src="" />
</div>
<?php
}
?>
<hr class="hrs" />
<div class="shuffle"></div>
<?php
if($isMobile) {
?>
<table id="tStats" align="center" valign="middle" cellspacing="1" bgcolor="#555" style="margin-top: 20px; margin-bottom: 20px; border-radius: 3px; opacity: 0;">
<tr>
    <td colspan="2" bgcolor="#EEE" align="left" style="padding-left: 10px; padding-right: 10px; ">average: &nbsp;<span id="average" style="font-weight: bold;">-</span> &nbsp;</td>
    <td colspan="2" bgcolor="#EEE" align="left" style="padding-left: 10px; padding-right: 10px; color: blue;">fastest: &nbsp;<span id="fastest" style="font-weight: bold;">-</span> &nbsp;</td>
    <td colspan="2" bgcolor="#EEE" align="left" style="padding-left: 10px; padding-right: 10px; color: red; ">slowest: &nbsp;<span id="slowest" style="font-weight: bold;">-</span> &nbsp;</td>
</tr>
</table>
<table id="tRecord" align="center" valign="middle" cellspacing="1" bgcolor="#555" style="opacity: 0;">
<tr id="tbtitle" bgcolor="#EEE" align="center">
    <td width="50px">id</td>
    <td width="150px">time</td>
</tr>
</table>
<?php
} else {
?>
<div>
    <span id="fBlock" class="stats" style="color: blue; opacity: 0;" onmouseover="focusFastest(); " onmouseout="unfocusFastest(); ">fastest: &nbsp;<span id="fastest" style="font-weight: bold;">-</span> &nbsp;</span>
    <span id="aBlock" class="stats" style="opacity: 0;">average: &nbsp;<span id="average" style="font-weight: bold;">-</span> &nbsp;</span>
    <span id="sBlock" class="stats" style="color: red; opacity: 0;" onmouseover="focusSlowest(); " onmouseout="unfocusSlowest(); ">slowest: &nbsp;<span id="slowest" style="font-weight: bold;">-</span> &nbsp;</span>
</div>
<table id="tRecord1" class="recordTable" align="center" valign="middle" cellspacing="1" bgcolor="#555">
<tr id="tridi1" bgcolor="#EEE" align="center">
    <td id="title_id1" style="padding: 5px 10px;">id</td>
    <?php
    for($i = 1; $i <= 10; $i++) {
?>
    <td width="90px" bgcolor="#FFF" align="center" style="" class="record isNormal" id="id_rec<?php echo $i; ?>"></td>
    <?php
    }
?>
</tr>
<tr id="trtime1" bgcolor="#EEE" align="center">
    <td id="title_time1" style="padding: 5px 10px;">time</td>
    <?php
    for($i = 1; $i <= 10; $i++) {
?>
    <td width="90px" bgcolor="#FFF" align="center" style="" class="record isNormal" id="time_rec<?php echo $i; ?>"></td>
    <?php
    }
?>
</tr>
</table>
<div id="btns" style="opacity: 0;">
<button id="theBtnExport" onclick="exportExcel()" disabled="true" onfocus="$('#theBtnExport').blur()">Export Records</button>
<button id="theBtnChart" onclick="toggleChart()" disabled="true" onfocus="$('theBtnChart').blur()">Show Chart</button>
</div>
<?php
}
?>
</body>
<script>
var isMobile = <?php echo $isMobile; ?>;
var runningStatus = "ready";
var rubikTime = 0;
var flagTimeout = 0;
var recordList = Array();
var shuffleList = Array();
var datetimeList = Array();
var isInited = false;

function initialAll() {
    $(".title").animate({opacity: "1"}, function() {
        $(".hrs").animate({opacity: "1"}, function() {
            $(".stopwatch").text("00:00.000");
            $(".stopwatch").slideDown("normal", function() {
                $(".stopwatch").animate({opacity: "1"}, function() {
                    refreshShuffle()
                })
            });
        })
    });
}

function timestop(evt) {
    if((evt.type == "keydown" && evt.keyCode == 32) || (evt.type == "touchstart")) {
        if(runningStatus == "running") {
            clearTimeout(flagTimeout);
            runningStatus = "stopping";
        }
        if(!isInited) {
            if(isMobile) {
                $("#tStats").animate({opacity: "1"}, function() {
                    $("#tRecord").animate({opacity: "1"})
                });
            } else {
                $("#fBlock").animate({opacity: "1"}, function() {
                    $("#aBlock").animate({opacity: "1"}, function() {
                        $("#sBlock").animate({opacity: "1"}, function() {
                            $("#tRecord1").animate({opacity: "1"}, function() {
                                $("#btns").animate({opacity: "1"})
                            });
                        })
                    })
                });
            }
            isInited = true;
        }
    }
}

function timestart(evt) {
    if((evt.type == 'keyup' && evt.keyCode == 32) || (evt.type =="touchend")) {
        if(runningStatus == "ready") {
            rubikTime = new Date().getTime();
            runningStatus = "running";
            liveTime();
            if(recordList.length % 10 == 0 && recordList.length > 1) {
                createNewTable((recordList.length / 10 + 1));
            }
        }
        if(runningStatus == "stopping") {
            runningStatus = "adding";
            return;
        }
        if(runningStatus == "adding"){
            addRecord($(".stopwatch").text());
            refreshStopwatch();
            runningStatus = "ready";
        }
    }
}

function zeroFill(stat, fullLength) {
    while(stat.length < fullLength) {
        stat = "0" + stat;
    }
    return stat;
}

function getDuration() {
    var nowtime = new Date().getTime();
    var timediff = nowtime - rubikTime;
    return timediff;
}

function duration2Time(duration) {
    var timestr = '';
    var second = duration % 60000;
    timestr = zeroFill(((duration - second) / 60000).toString(), 2) + ":" + zeroFill((second / 1000).toFixed(3).toString(), 6);
    return timestr;
}

function time2Duration(timeval) {
    var spl = timeval.split(":");
    var dur = parseInt(spl[0]) * 60000 + parseFloat(spl[1] * 1000);
    return dur;
}

function liveTime() {
    var duration = getDuration();
    var timestr = duration2Time(duration);
    $(".stopwatch").text(timestr);
    flagTimeout = setTimeout("liveTime()", 20);
}

<?php
if($isMobile) {
?>
// Mobile version
function addRecord(timestr) {
    var recordCount = $(".record").length;
    var lastItem = '';
    if(recordCount == 0) {
        lastItem = $("#tbtitle");
        lastItem.after("<tr id='rec" + (recordCount + 1).toString() + "' class='record' bgcolor='#FFF' align='center' style='opacity: 0;'><td>" + (recordCount + 1).toString() + "</td><td>" + timestr + "</td></tr>");
    } else {
        lastItem = $("#rec" + recordCount.toString());
        lastItem.before("<tr id='rec" + (recordCount + 1).toString() + "' class='record' bgcolor='#FFF' align='center' style='opacity: 0;'><td>" + (recordCount + 1).toString() + "</td><td>" + timestr + "</td></tr>");
    }
    $("#rec" + (recordCount + 1).toString()).animate({opacity: "1"});
    recordList.push(time2Duration(timestr));
    refreshStats();
}
<?php
} else {
?>
// PC version
function addRecord(timestr) {
    recordList.push(time2Duration(timestr));
    var recordCount = recordList.length;
    if(recordCount == 1) {
        $("#theBtnExport").attr('disabled', false);
        $("#theBtnChart").attr('disabled', false);
    }
    $("#id_rec" + recordCount.toString()).text(recordCount);
    $("#time_rec" + recordCount.toString()).text(timestr);
    $("#id_rec" + recordCount.toString()).animate({opacity: "1"});
    $("#time_rec" + recordCount.toString()).animate({opacity: "1"});
    refreshStats();
    shuffleList.push($(".shuffle").text());
    datetimeList.push(getNowFormatDate());
    refreshChart();
}
<?php
}
?>

<?php
if($isMobile) {
?>
// Mobile version
function refreshStats() {
    for (var sum = i = 0; i < recordList.length; i++) {
        sum += recordList[i];
    }
    var avg = (sum / recordList.length).toFixed(3);
    avg = duration2Time(avg);
    $("#average").text(avg);
    minmax = arrayMaxMin(recordList);
    $("#fastest").text(duration2Time(minmax[1]));
    $("#slowest").text(duration2Time(minmax[0]));
    $(".record").removeClass("isSlowest");
    $(".record").removeClass("isFastest");
    $(".record").addClass("isNormal");
    $("#rec" + (minmax[3] + 1).toString()).removeClass("isNormal");
    $("#rec" + (minmax[3] + 1).toString()).addClass("isFastest");
    $("#rec" + (minmax[2] + 1).toString()).removeClass("isNormal");
    $("#rec" + (minmax[2] + 1).toString()).addClass("isSlowest");
    
}
<?php
} else {
?>
// PC version
function refreshStats() {
    for (var sum = i = 0; i < recordList.length; i++) {
        sum += recordList[i];
    }
    var avg = (sum / recordList.length).toFixed(3);
    avg = duration2Time(avg);
    $("#average").text(avg);
    var minmax = arrayMaxMin(recordList);
    $("#fastest").text(duration2Time(minmax[1]));
    $("#slowest").text(duration2Time(minmax[0]));
    $(".record").removeClass("isFastest");
    $(".record").removeClass("isSlowest");
    $(".record").addClass("isNormal");
    $("#time_rec" + (minmax[3] + 1).toString()).removeClass("isNormal");
    $("#time_rec" + (minmax[2] + 1).toString()).removeClass("isNormal");
    $("#id_rec" + (minmax[3] + 1).toString()).removeClass("isNormal");
    $("#id_rec" + (minmax[2] + 1).toString()).removeClass("isNormal");
    $("#time_rec" + (minmax[3] + 1).toString()).addClass("isFastest");
    $("#time_rec" + (minmax[2] + 1).toString()).addClass("isSlowest");
    $("#id_rec" + (minmax[3] + 1).toString()).addClass("isFastest");
    $("#id_rec" + (minmax[2] + 1).toString()).addClass("isSlowest");
}
<?php
}
?>

function arrayMaxMin(arr) {
    if(arr.length >= 1) {
        var maxElem = arr[0];
        var minElem = arr[0];
        var maxKey = 0;
        var minKey = 0;
        for(var i = 1; i < arr.length; i++) {
            if(arr[i] > maxElem) {
                maxElem = arr[i];
                maxKey = i;
            }
            if(arr[i] < minElem) {
                minElem = arr[i];
                minKey = i;
            }
        }
        var minmax = Array(maxElem, minElem, maxKey, minKey);
        return minmax;
    }
}

function shuffleSteps() {
    var atoms0 = ["U", "U", "D"];
    var atoms1 = ["R", "R", "L"];
    var atoms2 = ["F", "F", "B"]
    var steps = '';
    for(var i = 0; i < 20; i++) {
        var ran = Math.random();
        if(i % 3 == 0) {
            steps += atoms0[Math.floor(ran * 3)];
        } else if(i % 3 == 1){
            steps += atoms1[Math.floor(ran * 3)];
        } else {
            steps += atoms2[Math.floor(ran * 3)];
        }
        if(Math.round(ran * 10000) % 3 == 0) {
            steps += "'";
        } else if(Math.round(ran * 10000) % 3 == 1) {
            steps += "2"
        }
        steps += " ";
    }
    return steps;
}

function refreshStopwatch() {
    $(".stopwatch").fadeToggle("fast", function() {
        $(".stopwatch").text("00:00.000");
    });
    $(".stopwatch").fadeToggle("fast", function() {
        refreshShuffle()
    });
}

function refreshShuffle() {
    $(".shuffle").fadeToggle("fast", function() {
        $(".shuffle").text(shuffleSteps());
    });
    $(".shuffle").fadeToggle("fast");
}

function focusFastest() {
    if(recordList.length > 0) {
        var minmax = arrayMaxMin(recordList);
        $("#time_rec" + (minmax[3] + 1).toString()).addClass("highlightEst");
        $("#id_rec" + (minmax[3] + 1).toString()).addClass("highlightEst");
    }
}

function focusSlowest() {
    if(recordList.length > 0) {
        var minmax = arrayMaxMin(recordList);
        $("#time_rec" + (minmax[2] + 1).toString()).addClass("highlightEst");
        $("#id_rec" + (minmax[2] + 1).toString()).addClass("highlightEst");
    }
}

function unfocusFastest() {
    if(recordList.length > 0) {
        var minmax = arrayMaxMin(recordList);
        $("#time_rec" + (minmax[3] + 1).toString()).removeClass("highlightEst");
        $("#id_rec" + (minmax[3] + 1).toString()).removeClass("highlightEst");
    }
}

function unfocusSlowest() {
    if(recordList.length > 0) {
        var minmax = arrayMaxMin(recordList);
        $("#time_rec" + (minmax[2] + 1).toString()).removeClass("highlightEst");
        $("#id_rec" + (minmax[2] + 1).toString()).removeClass("highlightEst");
    }
}

function createNewTable(num) {
    var tableCount = $(".recordTable").length;
    var lastTable = $("#tRecord" + tableCount.toString());
    var newTableHTML = '';
    newTableHTML += '<table id="tRecord' + num.toString() + '" class="recordTable" align="center" valign="middle" cellspacing="1" bgcolor="#555">';
    newTableHTML += '<tr id="trid' + num.toString() + '" bgcolor="#EEE" align="center">';
    newTableHTML += '    <td id="title_id' + num.toString() + '" style="padding: 5px 10px;">id</td>';
    <?php
    for($i = 1; $i <= 10; $i++) {
?>
    newTableHTML += '    <td width="90px" bgcolor="#FFF" align="center" style="" class="record isNormal" id="id_rec' + ((num - 1) * 10 + <?php echo $i; ?>).toString() + '"></td>';
    <?php
    }
?>
    newTableHTML += '</tr>';
    newTableHTML += '<tr id="trtime' + num.toString() + '" bgcolor="#EEE" align="center">';
    newTableHTML += '    <td id="title_time' + num.toString() + '" style="padding: 5px 10px;">time</td>';
    <?php
    for($i = 1; $i <= 10; $i++) {
?>
    newTableHTML += '    <td width="90px" bgcolor="#FFF" align="center" style="" class="record isNormal" id="time_rec' + ((num - 1) * 10 + <?php echo $i; ?>).toString() + '"></td>';
    <?php
    }
?>
    newTableHTML += '</tr>';
    newTableHTML += '</table>';  
    lastTable.before(newTableHTML);
    $("#tRecord" + num.toString()).animate({opacity: "1"});
}

function getNowFormatDate() {
    var nowdate = new Date();
    var nfd = nowdate.getFullYear().toString() + "/" + zeroFill((nowdate.getMonth() + 1).toString(), 2) + "/" + zeroFill(nowdate.getDate().toString(), 2); 
    nfd += " ";
    nfd += zeroFill(nowdate.getHours().toString(), 2) + ":" + zeroFill(nowdate.getMinutes().toString(), 2) + ":" + zeroFill(nowdate.getSeconds().toString(), 2);
    return nfd;
}

function exportExcel() {
    if(recordList.length == 0) {
        alert("There is no records yet...");
        return;
    }
    excelData = {};
    for(var i = 0; i < recordList.length; i++) {
        excelData[i] = {"duration": recordList[i], "record": duration2Time(recordList[i]), "shuffle": shuffleList[i], "datetime": datetimeList[i]};
    }
    $("#exportJsonField").val(JSON.stringify(excelData));
    $("#exportJson").submit();
}

function toggleChart() {
    if(recordList.length < 1) {
        alert("There is no records yet...");
        return;
    }
    if($(".stopwatch").css("display") == "block") {
        $(".stopwatch").slideToggle("normal", function() {
            $(".imgChart").slideToggle("normal");
        });
    } else {
        $(".imgChart").slideToggle("normal", function() {;
            $(".stopwatch").slideToggle("normal");
        });
        
    }
}

function refreshChart() {
    recordData = '';
    for(var i = 0; i < recordList.length; i++) {
        if(i > 0) {
            recordData += ',';
        }
        recordData += recordList[i];
    }
    $("#imgChart").attr("src", "chart.php?record=" + recordData);
}

</script>
</html>
