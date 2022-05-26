<?php
	$includePath = get_include_path();
	ini_set('include_path', 'C:\Users\whoskins\Documents\web;'.$includePath);
	//var_dump(get_include_path());
	require_once "defaultincludes.inc";
	require_once "mrbs_sql.inc";
	require_once "mincals.inc";
	require_once "theme.inc";
	require_once "adodb5/adodb.inc.php";
	
	$connect = ADONewConnection("mysql");
	$connect->Connect($db_host, $db_login, $db_password, $db_database);
	
	$name = $_GET['name'];
	$start = time();
	$end = time() + (int)($_POST['hours'] * 60 * 60);
	$room = $_GET['room'];
	$creator = $_POST['user'];
	$shortDescrip = $_POST['short'];
	$type = 'Internal';
	$descrip = $_POST['full'];

	if((double)$_POST['hours'] == 0)
	{
		header( 'Location: smallclient.php?error=3&name='.$name ) ;
	}
	else if(!$room || !$creator || !$shortDescrip)
	{
		header( 'Location: smallclient.php?error=1&name='.$name ) ;
	}
	else if(!authValidateUser($creator, $_POST['pass']))
	{
		header( 'Location: smallclient.php?error=2&name='.$name  ) ;
	}
	else
	{
		$start = (int) $start;
		$end = (int) $end;
		$room = (int) $room;
		//$creator = $connect->Quote($creator);
		//$shortDescrip = $connect->Quote($shortDescrip);
		//$descrip = $connect->Quote($descrip);
		$data = array();

		$data['ical_uid'] = generate_global_uid($name);
		$data['ical_sequence'] = 0;
		$data['start_time'] = $start;
		$data['end_time'] = $end;
		$data['room_id'] = $room;
		$data['create_by'] = $creator;
		$data['name'] = $shortDescrip; //Title
		$data['type'] = "Internal";
		$data['description'] = $descrip;
		$data['entry_type'] = 0;
		$data['repeat_id'] = 0;
		$data['status'] = 0;
		$data['rep_type'] = 0;
	
	
      // Create the entry:
		$new_id = mrbsCreateSingleEntry($data);
	//echo $start . " " . $end . " " . $_POST['duration'];
		$is_repeat_table = FALSE;
		$data['id'] = $new_id;  // Add in the id now we know it
		$HTML = "<script type=\"text/javascript\">
		<!--
		function delay()
		{
			window.location = \"https://gfxlabman.so.intel.com/smallclient.php?name=".$name."\"
		}
		//-->
		</script>
		<body onload=\"setTimeout('delay()', 1000)\">
		</body>";
		//header( 'Location: http://localhost/web/smallclient.php?nonce='.rand() ) ;
		echo $HTML;
	}
?>