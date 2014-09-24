<?php

//header("Content-type: text/xml");
/*
==================================================================

MySQL to XML feed.

==================================================================
*/
$host = ******;
$user = ******;
$pass = ******;
$database = ******;
$table = ******;

$linkID = mysql_connect($host, $user, $pass) or die("Could not connect to host.");

mysql_select_db($database, $linkID) or die("Could not find database.");

$query = "SELECT * FROM $table WHERE `id` > 10001 AND `price` > 0 AND `base_match` = 'm' AND `do_not_sell` = 0"

$resultID = mysql_query($query, $linkID) or die("Data not found.");



$xml_output = "<update_inventory>\n";
$xml_output .= 	"\t<distribution_center_code>DREAM</distribution_center_code>\n";
$xml_output .= 	"\t<items>\n";



 for($x = 0 ; $x < mysql_num_rows($resultID) ; $x++){
    $row = mysql_fetch_assoc($resultID);
    if ($row['id'] > 0 && $row['price'] > 0 && $row['do_not_sell'] == 0) {
		$xml_output .= "\t\t<item>\n";
		$xml_output .= "\t\t\t<item_id>" . $row['id'] . "</item_id>\n";
		$xml_output .= "\t\t\t<quantity>" . $row['available'] . "</quantity>\n";
		$xml_output .= "\t\t</item>\n";

	}
}

$xml_output .= "\t</items>\n";
$xml_output .= "</update_inventory>\n";




$fh = fopen('./updateInventory.xml','w') or die($php_errormsg);
fwrite($fh, $xml_output) or die($php_errormsg);
$merchId = "******";
$login = "******";
$password = "******";
$function = "UpdateInventory";
$xmlUrl = "******";
$xml = file_get_contents($xmlUrl);
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, "https://secure.ultracart.com/cgi-bin/UCApi");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "merchantId=".$merchId."&login=".$login."&password=".$password."&function=".$function."&Inventory=".$xml);
$content=curl_exec($ch);
curl_close($ch);
echo htmlspecialchars($content);

//echo $xml_output;

?> 