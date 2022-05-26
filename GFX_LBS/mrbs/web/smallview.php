<?php
require_once "defaultincludes.inc";
require_once "mincals.inc";
require_once "theme.inc";
require_once "adodb5/adodb.inc.php";

	$room = $_GET['name'];
	
	$connect = ADONewConnection("mysql");
	$connect->Connect($db_host, $db_login, $db_password, $db_database);
	
	$query =
			"SELECT * FROM SystemStatusView
				WHERE SystemName LIKE ?";
	$newQuery = $connect->Prepare($query);
	$recordSet = $connect->Execute($newQuery, array($room));
	
	while (!$recordSet->EOF) 
	{
		echo 'System Info'.'<br/><br/>';
		echo "Name: ".$recordSet->fields['SystemName'].'<br/>';
		print 'GT Die Config: '.$recordSet->fields['GTLevel'].'<br/>';
		print 'Chipset Name: '.$recordSet->fields['ChipsetName'].'<br/>';
		print 'North GFX Stepping: '.$recordSet->fields['Stepping'].'<br/>';
		print 'EDRAM: '.$recordSet->fields['HasEDRAM'].'<br/>';
		print 'Memory Type: '.$recordSet->fields['MemoryType'].'<br/>';
		print 'South Chipset: '.$recordSet->fields['PCH'].'<br/>';
		print 'PCH Stepping: '.$recordSet->fields['PCHStepping'].'<br/>';
		print "Platform: ".$recordSet->fields['Platform'].'<br/>';
		$recordSet->MoveNext();
	}
?>