<?php
require_once "defaultincludes.inc";
require_once "mincals.inc";
require_once "theme.inc";
require_once "adodb5/adodb.inc.php";
//require_once "session_http.inc";

	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	header("Pragma: no-cache");
	
	$time = time();
	$room = $_GET['name'];
	
	$connect = ADONewConnection("mysql");
	$connect->Connect($db_host, $db_login, $db_password, $db_database);
	
	$query =
			"SELECT *
			FROM mrbs_room
			INNER JOIN mrbs_entry on mrbs_entry.room_id = mrbs_room.id
			AND start_time < $time
			AND end_time > $time
			AND room_name LIKE ?
			ORDER BY room_name";
	$newQuery = $connect->Prepare($query);
	$recordSet = $connect->Execute($newQuery, array($room));
	//$res = sql_query($newQuery);
	//$row = sql_row_keyed($res, 0);

	echo "<p>System: $room</p>";

	if (!$recordSet) 
	{
         print $connect->ErrorMsg();
	}
		
	else
	{
		//var_dump($recordSet);
		if ($recordSet->EOF) {
			
			if($_GET['error'] == 1)
			{
				echo "<p>Please fill out all fields</p>";
			}
			else if($_GET['error'] == 2)
			{
				echo "<p>Incorrect username/password combination</p>";
			}
			else if($_GET['error'] == 3)
			{
				echo "<p>Input something other than 0 for Hours</p>";
			}
			echo "<p>No one at this machine currently</p>";
			$anotherQuery = 
							"SELECT *
							FROM mrbs_room
							WHERE room_name = ?";
			$anotherNewQuery = $connect->Prepare($anotherQuery);
			$anotherRecordSet = $connect->Execute($anotherNewQuery, array($room));
			if(!$anotherRecordSet)
			{
				print $connect->ErrorMsg();
			}
			else
			{
				//var_dump($anotherRecordSet);
				//PrintLogonBox();
				echo '<form method="post" action="smallsignup.php?room=' .htmlentities($anotherRecordSet ->fields['id']). '&name='.htmlentities($anotherRecordSet ->fields['room_name']) . '">';
				echo <<<EOT
				<div> Username </div> <input type="text" name="user" placeholder="Username"/> </br>
				<div> Password </div> <input type="password" name="pass" placeholder="Password"/> </br>
				<div> Short Description </div> <input type="text" name="short" placeholder="Short Description"/> </br>
				<h4>Start Time: 
			
				<script type="text/javascript">
	
					var currentTime = new Date()
					var hours = currentTime.getHours()
					var minutes = currentTime.getMinutes()
					if (minutes < 10) {
						minutes = "0" + minutes
					}
					if (hours > 12) {
						document.write(hours - 12 + ":" + minutes + " ")
					}
					else {
						document.write(hours + ":" + minutes + " ")
					}
					if (hours > 11) {
						document.write("PM")
					} else {
						document.write("AM")
					}
	
				</script></h4>
			
				<div>
					Duration (in hours)
				</div>
			<!--Old input method, the revert uncomment, comment the line below this
				<select name="hours">
					<option>.5</option>
					<option>1</option>
					<option>1.5</option>
					<option>2</option>
					<option>2.5</option>
					<option>3</option>
					<option>3.5</option>
					<option>4</option>
					<option>4.5</option>
					<option>5</option>
					<option>5.5</option>
					<option>6</option>
				</select>
			-->
				<input type="text" name="hours" size="3" maxlength="3" value="0"/> </br></br>
				
				<input type="submit" value="Go!" />
			</form>
EOT;
		}
		
		} else {
			
			while (!$recordSet->EOF) 
			{
					 print $recordSet->fields['create_by'].'<br/>'.$recordSet->fields['name'].'<br/>';
					 $recordSet->MoveNext();
			}
		}
		$HTML = "<script type=\"text/javascript\">
		
		function delay()
		{
			window.location = \"https://gfxlabman.so.intel.com/smallclient.php?name=$room\"
		}
		
		</script>
		<body onload=\"setTimeout('delay()', 900000)\">
		</body>";
		echo $HTML;
		$recordSet->Close(); # optional

		$connect->Close(); # optional

	}
?>