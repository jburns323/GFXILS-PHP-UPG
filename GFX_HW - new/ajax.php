<?php
require 'includes.inc.php';
header('Cache-Control: no-cache');
header('Content-Type: text/plain');

ob_start();
print_r($_GET);
$AJAX_Log = ob_get_contents() . "\n";
ob_end_clean();

if (empty($_GET['action']))
	die('ERROR: missing action');

switch ($_GET['action']){
	case 'getlist':
		foreach (array('Table', 'Column') as $Field)
			if (empty($_GET[$Field]))
				die('ERROR: missing ' . $Field);

		if (empty($_GET['term'])){
			$sth = $db->query('SELECT DISTINCT `' . mysql_escape_string($_GET['Column']) . '` FROM `' . mysql_escape_string($_GET['Table']) . '` ORDER BY `' . mysql_escape_string($_GET['Column']) . '`');
			$_GET['term'] = '';
		}
		else
			$sth = $db->query('SELECT DISTINCT `' . mysql_escape_string($_GET['Column']) . '` FROM `' . mysql_escape_string($_GET['Table']) . '` WHERE `' . mysql_escape_string($_GET['Column']) . '` LIKE "%' . mysql_escape_string(str_replace('%', '\%', $_GET['term'])) . '%" ORDER BY `' . mysql_escape_string($_GET['Column']) . '`');

		$Values = $sth->fetchAll(PDO::FETCH_NUM);

		$Output = array();
		foreach ($Values as $Value){
			if (!empty($Value[0])){
				$Line = '{ ';
					$Line .= '"id":"' . $Value[0] . '",';
					$Line .= '"label":"' . $Value[0] . '",';
					$Line .= '"value":"' . $Value[0] . '"';
				$Line .= ' }';
				$Output[] = $Line;
			}
		}

		echo '[ ';
		echo implode(',', $Output);
		echo ' ]';

		break;
}

$AJAX_Log .= ob_get_contents();
file_put_contents('AJAX_Log.txt', $AJAX_Log);
