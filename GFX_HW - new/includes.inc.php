<?php
ob_start();
// Terry's GFX Database
//$DSN = 'mysql:host=tldodsox-mobl2;dbname=gfx_inventory';
// Martins Fake GFX Database
$DSN = 'mysql:host=mconnifx-mobl;dbname=gfx';

$db = new PDO($DSN, 'autoclient', 'gr@ph1c$');

if (empty($_POST['Form_Name'])) $_POST['Form_Name'] = '';
if (empty($_GET['Form_Name'])) $_GET['Form_Name'] = '';

foreach ($_GET as $k=>$v) $_GET[$k] = trim($v);
foreach ($_POST as $k=>$v) $_POST[$k] = trim($v);
foreach ($_REQUEST as $k=>$v) $_REQUEST[$k] = trim($v);
foreach ($_COOKIE as $k=>$v) $_COOKIE[$k] = trim($v);
