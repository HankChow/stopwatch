<?php
$json = object_array(json_decode($_POST['jsonField']));
header("Content-type:application/vnd.ms-excel");
header("Content-Disposition:filename=record" . date('YmdHis', time()) . ".xls");
echo "id\t";
echo "record\t";
echo "datetime\t";
echo "shuffle\t\n";
for($i = 0; $i < (count($json) / 2); $i++) {
    echo ($i + 1) . "\t";
    echo "{$json[$i]['record']}\t";
    echo "{$json[$i]['datetime']}\t";
    echo "{$json[$i]['shuffle']}\t\n";
}

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
