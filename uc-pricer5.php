<?php

//header("Content-type: text/xml");
/*
==================================================================

Due to limitations enforced by ultracart I was forced to submit 
one XML feed per API call. This is a slight workaround that creates
an XML file for each item and submits it before moving on to the 
next one.

==================================================================
*/
$host = ******;
$user = ******;
$pass = ******;
$database = ******;
$table = ******;

$linkID = mysql_connect($host, $user, $pass) or die("Could not connect to host.");

mysql_select_db($database, $linkID) or die("Could not find database.");

$query = "SELECT DISTINCT 
`id`,
`link`, 
`price`,
`type`, 
`shipping_weight`, 
`shipping_length`, 
`shipping_width`, 
`shipping_height`, 
`title`, `brand`, 
`inventory`, 
`available`, 
`min_order_qty`, 
`image`, 
`call`, 
`allow_in_cart`, 
`allow_backorder` 
FROM $table WHERE `id` > 10001 AND `price` > 0 AND `base_match` = 'm' AND `do_not_sell` = 0";

$resultID = mysql_query($query, $linkID) or die(mysql_error());
	$x =-1;
	while($row = mysql_fetch_assoc($resultID)){
		$x++;
		$itemArray[$x]['id'] = $row['id'];
		$itemArray[$x]['link'] = $row['link'];
		$itemArray[$x]['price'] = $row['price'];
		$itemArray[$x]['type'] = $row['type'];
		$itemArray[$x]['shipping_weight'] = $row['shipping_weight'];
		$itemArray[$x]['shipping_length'] = $row['shipping_length'];
		$itemArray[$x]['shipping_width'] = $row['shipping_width'];
		$itemArray[$x]['shipping_height'] = $row['shipping_height'];
		$itemArray[$x]['title'] = $row['title'];
		$itemArray[$x]['brand'] = $row['brand'];
		$itemArray[$x]['inventory'] = $row['inventory'];
		$itemArray[$x]['available'] = $row['available'];
		$itemArray[$x]['min_order_qty'] = $row['min_order_qty'];
		$itemArray[$x]['image'] = $row['image'];
		$itemArray[$x]['call'] = $row['call'];
		$itemArray[$x]['allow_in_cart'] = $row['allow_in_cart'];
		$itemArray[$x]['allow_backorder'] = $row['allow_backorder'];


		$desc = htmlspecialchars_decode($itemArray[$x]['title']);
    	$desc = str_replace('"',"'", $desc);
    	$desc = str_replace('&',"and", $desc);
    	$desc = str_replace('%'," %25", $desc);


    	$track_inventory = ($itemArray[$x]['inventory'] == "i")? "true" : "false";

    	//if the item is marked call, or the item is marked allow in cart no, or the item is marked i for track inventory AND availability is 0 or less
    	//mark the part in ultracart to inactive.
    	//ultracart sucks and doesnt send any information that the part is marked inactive
    	//So when the add to cart button is pushed an AJAX call is made to check these same conditions
    	//items meeting these conditions in the AJAX call show user a msg "Sorry, This Item is Currently Unavailable..."
    	//If item is marked allow backorder disregard tracking inventory and number available
    	if ($track_inventory == "true" && $itemArray[$x]['available'] <= 0 || $itemArray[$x]['call'] == 1 || $itemArray[$x]['allow_in_cart'] == 0) {
    		if ($itemArray[$x]['allow_backorder'] == 1) {
    			$inactive = "false";
    		} else {
    		$inactive = "true";
    		}
    	} else {
    		$inactive = "false"; 
    	};

    	$xml_output = "\t<item>\n";
		$xml_output .= "\t\t<merchant_item_id>".$itemArray[$x]['id']."</merchant_item_id>\n";
		$xml_output .= "\t\t<description>".$desc."</description>\n";
		$xml_output .= "\t\t<view_url>".htmlspecialchars($itemArray[$x]['link'])."</view_url>\n";
		$xml_output .= "\t\t<cost>".number_format($itemArray[$x]['price'], 2, '.', '')."</cost>\n";
		$xml_output .= "\t\t<uom_weight>LB</uom_weight>\n";
		$xml_output .= "\t\t<weight>".number_format($itemArray[$x]['shipping_weight'], 2, '.', '')."</weight>\n";
		$xml_output .= "\t\t<inactive>".$inactive."</inactive>\n";
		$xml_output .= "\t\t<minimum_quantity>".$itemArray[$x]['min_order_qty']."</minimum_quantity>\n";	
		$xml_output .= "\t\t<inventory_quantity>".$itemArray[$x]['available']."</inventory_quantity>\n";	
		$xml_output .= "\t\t<track_inventory>$track_inventory</track_inventory>\n";		
		$xml_output .= "\t\t<manufacturer_name>".htmlspecialchars($itemArray[$x]['brand'])."</manufacturer_name>\n";
		$xml_output .= "\t\t<manufacturer_sku></manufacturer_sku>\n";
		$xml_output .= "\t\t<uom_distance>IN</uom_distance>\n";
		
		// if its a solar panel or if it weighs over 70lbs remove dimensions
		//add per dustins request - "should fix panel shipping errors"
		if (strtolower($itemArray[$x]['type']) === "solar panel" || $itemArray[$x]['shipping_weight'] >= 70) {
			$xml_output .= "\t\t<length>0</length>\n";
			$xml_output .= "\t\t<width>0</width>\n";
			$xml_output .= "\t\t<height>0</height>\n";	
		} else {
			$xml_output .= "\t\t<length>".number_format($itemArray[$x]['shipping_length'], 2, '.', '')."</length>\n";
			$xml_output .= "\t\t<width>".number_format($itemArray[$x]['shipping_width'], 2, '.', '')."</width>\n";
			$xml_output .= "\t\t<height>".number_format($itemArray[$x]['shipping_height'], 2, '.', '')."</height>\n";
		}
		$xml_output .= "\t\t<froogle>\n";
		$xml_output .= "\t\t\t<image_url>".$itemArray[$x]['image']."</image_url>\n";
		$xml_output .= "\t\t</froogle>\n";
		
		// $xml_output .= "\t\t<image_url>".$row['image']."</image_url>\n";
		$xml_output .= "\t</item>\n";



	$fh = fopen('./uc-pricer5.xml','w') or die($php_errormsg);
	fwrite($fh, $xml_output) or die($php_errormsg);
	$merchId = "**********";
	$login = "*********";
	$password = "*********";
	$function = "createItem";
	$xmlUrl = "********";
	$xml = file_get_contents($xmlUrl);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, "https://secure.ultracart.com/cgi-bin/UCApi");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "merchantId=".$merchId."&login=".$login."&password=".$password."&function=".$function."&Item=".$xml);
	$content=curl_exec($ch);
	curl_close($ch);
	echo htmlspecialchars($content ." " . $itemArray[$x]['id'])."<br />";
	
}


?> 