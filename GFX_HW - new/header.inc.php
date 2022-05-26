<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>GFX Inventory</title>

	<meta http-equiv="Content-Type" content="text/html; Charset=utf-8" />
	<link type="text/css" rel="stylesheet" href="style.css" />

    <script type="text/javascript" src="jquery-1.6.4.min.js"></script>

	<script type="text/javascript" src="jquery.tablesorter.js"></script>

	<script type="text/javascript" src="jquery-ui-1.8.16.custom.min.js"></script>
	<link type="text/css" rel="stylesheet" href="smoothness/jquery-ui-1.8.16.custom.css" />

	<script type="text/javascript">
		$(document).ready(
			function(){
				$(".tablesorter").tablesorter({
					sortList      : [[0,0]]
				});
			}
		);	
	</script>

	<?php if (!empty($Headers)) echo $Headers; ?>
</head>
<body>
<div id="header">
	<?php if (!empty($_mainmenulink)) echo '<div id="mainmenulink"><a href=".">Main Menu</a></div>'; ?>
	<h1>GFX Inventory</h1>
	<?php if (preg_match('/mconnifx/i', $DSN)) echo '<div class="error">WARNING: Using test database</div>'; ?>
</div>

<div id="page">

