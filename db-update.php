<?php

//header("Content-type: text/xml");
//File is responsible for pushing a single item's data to ultracart from mysql

/*
==================================================================

Determine the freight class of our products according to certian
fields filled out in our database

==================================================================
*/



$host = ******;
$user = ******;
$pass = ******;
$database = ******;
$table = ******;

$linkID = mysql_connect($host, $user, $pass) or die("Could not connect to host.");
mysql_select_db($database, $linkID) or die("Could not find database.");

    $data = array();

	$fh = fopen('./freight-class2.csv','w') or die($php_errormsg);
	fputcsv($fh, array('ID', 'Freight Class'));


$query = "SELECT DISTINCT `id`,`type`, `weight_batteries`,`weight_controller_parts`,`weight_rails`,`weight_solar_panels`,`hazmat` FROM $table WHERE `id` >1002 AND `base_match` = 'm' AND `do_not_sell` = 0";
$resultID = mysql_query($query, $linkID) or die(mysql_error());
    

	$x =-1;
	while($row = mysql_fetch_assoc($resultID)){
    $x++;

        
        $temp_array = array();
        $freight_class_array = array();
        $fallback = array();
        $type = "";


		$itemArray[$x]['id'] = $row['id'];
        $itemArray[$x]['type'] = $row['type'];
		$itemArray[$x]['weight_batteries'] = $row['weight_batteries'];
		$itemArray[$x]['weight_controller_parts'] = $row['weight_controller_parts'];
		$itemArray[$x]['weight_rails'] = $row['weight_rails'];
		$itemArray[$x]['weight_solar_panels'] = $row['weight_solar_panels'];
		$itemArray[$x]['hazmat'] = $row['hazmat'];

        $partNumber = $itemArray[$x]['id'];


		
    // Inverters, Battery Inverter
    
    
    $type = strtolower($itemArray[$x]['type']);
    $split_string = explode("," ,$type);
    $trimmed_array = array_map('trim', $split_string);
    
    if (in_array("build", $trimmed_array, TRUE)) {
        array_push($fallback, 85);
    };       
    if (in_array("off grid solar system", $trimmed_array, TRUE)) {
        array_push($fallback, 85);
    };
    if (in_array("grid tie solar system", $trimmed_array, TRUE)) {
        array_push($fallback, 85);
    };  
    if (in_array("inverters", $trimmed_array, TRUE)) {
        array_push($fallback, 775);
    };
    if (in_array("grid tie inverters", $trimmed_array, TRUE)) {
        array_push($fallback, 775);
    };
    if (in_array("off grid inverters", $trimmed_array, TRUE)) {
        array_push($fallback, 775);
    };          
    if (in_array("meters", $trimmed_array, TRUE)) {
       array_push($fallback, 775);
    };
    if (in_array("solar panel", $trimmed_array, TRUE)) {
        array_push($fallback, 85);
    };
    if (in_array("battery inverter", $trimmed_array, TRUE)) {
        array_push($fallback, 775);
    };
    if (in_array("disconnects", $trimmed_array, TRUE)) {
        array_push($fallback, 775);
    };
    if (in_array("charge controller", $trimmed_array, TRUE)) {
        array_push($fallback, 775);
    };
    if (in_array("wind", $trimmed_array, TRUE)) {
        array_push($fallback, 775);
    };
    if (in_array("wire", $trimmed_array, TRUE)) {
       array_push($fallback, 775);
    };
    if (in_array("racks", $trimmed_array, TRUE)) {
        array_push($fallback, 925);
    };
    if (in_array("batteries", $trimmed_array, TRUE)) {
        array_push($fallback, 70);
    };
    if (in_array("systems", $trimmed_array, TRUE)) {
        array_push($fallback, 85);
    };

    $fallback2 = array_unique($fallback);


	if($itemArray[$x]['weight_batteries'] > 0){
		if ($itemArray[$x]['hazmat'] == 1){
			array_push($freight_class_array, 70);
		} else {
			array_push($freight_class_array, 60);
		}
	};

	if ($itemArray[$x]['weight_controller_parts'] > 0) {
		array_push($freight_class_array, 775);
	};

	if ($itemArray[$x]['weight_rails'] > 0) {
		array_push($freight_class_array, 925);
	};

	if ($itemArray[$x]['weight_solar_panels'] > 0) {
		array_push($freight_class_array, 85);
	};

	if (count($freight_class_array) == 0 ){
        if (count($fallback) == 0) {
		    $freight_class = 'NULL';
        }elseif(count($fallback) == 1) {
            $freight_class = $fallback[0];
        } elseif(count($fallback) > 1 && count($fallback2) == 1){
            $freight_class = $fallback2[0];
        }elseif (count($fallback) >1 && count($fallback2) >1) {
            $freight_class = 85;
        }else {
            $freight_class = 0;
        }
	} elseif (count($freight_class_array) > 1) {
		$freight_class = 85;
	} else {
		$freight_class = $freight_class_array[0];
	};

    	array_push($temp_array, $partNumber, $freight_class);
    	array_push($data, $temp_array);

    fputcsv($fh, $temp_array);

	}

echo "done" . $x;

?> 