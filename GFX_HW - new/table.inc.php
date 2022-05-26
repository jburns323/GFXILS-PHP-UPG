<?php
/*****************************************************************************
 ********************************* Search ************************************
 *****************************************************************************/
if (empty($_GET['searchfield'])) $_GET['searchfield'] = '';
?><div id="searchframe">
	<div id="startoverlink"><a href="?">Start Over</a></div>
	<div id="newrecordlink"><a href="?new">Create New Record</a></div>

	<form method="get" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
		<select name="searchfield"><?php
			foreach ($Table->Columns_Unique as $Column){
				echo '<option value="' . htmlentities($Column) . '"';
				if ($_GET['searchfield'] == $Column) echo 'selected="selected"';
				echo '>' . htmlentities($Column) . '</option>';
			}
			foreach ($Table->Columns as $Column){
				echo '<option value="' . htmlentities($Column) . '"';
				if ($_GET['searchfield'] == $Column) echo 'selected="selected"';
				echo '>' . htmlentities($Column) . '</option>';
			}
		?></select>
		<input type="hidden" name="Form_Name" value="search" />
		<input type="text" name="query" id="searchquery" value="<?php if (!empty($_GET['query'])) echo htmlentities($_GET['query']); ?>" />
		<input type="submit" value="Search" />
	</form>
</div>
<script type="text/javascript">
	$("#searchquery").focus();
	$("#searchquery").select();
</script><?php
/*****************************************************************************
 ****************************** End Search ***********************************
 *****************************************************************************/

/*****************************************************************************
 ******************************* Delete *************************************
 *****************************************************************************/
if (!empty($_GET['delete'])){
	$sth = $db->prepare('SELECT COUNT(*) FROM `' . mysql_escape_string($Table->Table) . '` WHERE `' . $Table->Key . '` = :Key');
	$sth->bindParam('Key', $_GET['delete']);
	$sth->execute();
	$row = $sth->fetch(PDO::FETCH_NUM);
	if ($row[0] != 1) unset($_GET['delete']);
}

if (!empty($_GET['delete'])){
	if (isset($_GET['SURE'])){
		/**************** History logging *****************/
		$sth = $db->prepare('SELECT Location FROM `'. mysql_escape_string($Table->Table) . '` WHERE `'. mysql_escape_string($Table->Key) . '` = :Key');
		$sth->bindParam('Key', $_POST[$Table->Key]);
		$sth->execute();
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		if (empty($row['Location'])) $row['Location'] = '';

		if ($row['Location'] != $_POST['Location']){
			$Date = date('Y-m-d');
			$Time = date('g:i:s A');
			$sth = $db->prepare('INSERT INTO history (Date_Changed, Time_Changed, Type, SN, Location_Old, Location_New) VALUES (:Date_Changed, :Time_Changed, :Type, :SN, :Location_Old, "")');
			$sth->bindParam('Date_Changed', $Date);
			$sth->bindParam('Time_Changed', $Time);
			$sth->bindParam('Type', $Table->Type);
			$sth->bindParam('SN', $_POST[$Table->SN]);
			$sth->bindParam('Location_Old', $row['Location']);
			$sth->execute();
		}
		/*********** End of History logging *************/

		$sth = $db->prepare('DELETE FROM `'. mysql_escape_string($Table->Table) . '` WHERE `'. mysql_escape_string($Table->Key) . '` = :Key');
		$sth->bindParam('Key', $_GET['delete']);
		$sth->execute();

		ob_end_clean();
		header('Location: ' . $_SERVER['PHP_SELF']);
		die();
	}
	?><div id="deleterecord">
		<h2>Are you sure you wish to delete this record?</h2>
		<table class="tablesorter" id="List">
			<thead>
				<tr class="header"><?php
					foreach ($Table->Columns_Unique as $Header=>$Column)
						echo '<th>' . htmlentities($Header) . '</th>';
					foreach ($Table->Columns as $Header=>$Column)
						echo '<th>' . htmlentities($Header) . '</th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

				$sth = $db->prepare('SELECT * FROM `' . mysql_escape_string($Table->Table) . '` WHERE `' . mysql_escape_string($Table->Key) . '` = :Key');
				$sth->bindParam('Key', $_GET['delete']);
				$sth->execute();

				$i = 0;
				$row = $sth->fetch(PDO::FETCH_ASSOC);
				$class = '';

				$i++;
				if ($i % 2 == 0) $class = 'row2';			

				if (!empty($_GET['edit']) && $_GET['edit'] == $row[$Table->Key]) $class .= ' highlight';

				echo '<tr class="' . $class . '">';
					foreach ($Table->Columns_Unique as $Column)
						echo '<td class="nowrap">' . htmlentities($row[$Column]) . '</td>';
					foreach ($Table->Columns as $Column)
						echo '<td class="nowrap">' . htmlentities($row[$Column]) . '</td>';
				echo '</tr>';
			echo '</tbody>';
		echo '</table>';

		?><table id="deleterecord_yesno">
			<tr>
				<td id="yes"><a href="?delete=<?php echo htmlentities($row[$Table->Key]); ?>&amp;SURE">Yes, Delete This Record</a></td>
				<td id="no"><a href="?">No, Return to <?php echo htmlentities($Table->Type); ?> List</a></td>
			</tr>
		</table>
	</div><?php

	require('footer.inc.php');
	die();
}
/*****************************************************************************
 ****************************** End Delete ***********************************
 *****************************************************************************/

/*****************************************************************************
 **************************** Duplicate Range ********************************
 *****************************************************************************/
if (!empty($_GET['duplicate']) && isset($_GET['range']) && isset($_GET['SURE'])){
	if ($Table->validate_duplicate_range()){
		$sth = $db->prepare('SELECT * FROM `'. mysql_escape_string($Table->Table) . '` WHERE `' . mysql_escape_string($Table->Key) . '` = :Key');
		$sth->bindParam('Key', $_POST['duplicate']);
		$sth->execute();
		$row = $sth->fetch(PDO::FETCH_ASSOC);

		/**************** History logging *****************/
		$Date = date('Y-m-d');
		$Time = date('g:i:s A');
		$sth_log = $db->prepare('INSERT INTO history (Date_Changed, Time_Changed, Type, SN, Location_Old, Location_New) VALUES (:Date_Changed, :Time_Changed, :Type, :SN, "", :Location_New)');
		$sth_log->bindParam('Date_Changed', $Date);
		$sth_log->bindParam('Time_Changed', $Time);
		$sth_log->bindParam('Type', $Table->Type);
		$sth_log->bindParam('SN', $_POST[$Table->SN]);
		$sth_log->bindParam('Location_New', $row['Location']);
		/*********** End of History logging *************/

		$Columns = $Params = array();
		$Columns[] = '`' . mysql_escape_string($Table->SN) . '`';
		$Params[] = ':' . mysql_escape_string($Table->SN);

		foreach ($Table->Columns as $Column){
			$Columns[] = '`' . mysql_escape_string($Column) . '`';
			$Params[] = ':' . $Column;
		}

		$sth = $db->prepare('REPLACE INTO `'. mysql_escape_string($Table->Table) . '` (' . implode(', ', $Columns) . ') VALUES (' . implode(', ', $Params) . ')');

		$i = 0;
		if (isset($Table->Column_Types[$Table->SN]))
			$sth->bindParam($Table->SN, $i, $Table->Column_Types[$Table->SN]);
		else
			$sth->bindParam($Table->SN, $i);

		foreach ($Table->Columns as $Param){
			if (isset($Table->Column_Types[$Param]))
				$sth->bindParam($Param, $row[$Param], $Table->Column_Types[$Param]);
			else
				$sth->bindParam($Param, $row[$Param]);
		}

		for ($i = $_POST['Start']; $i <= $_POST['End']; $i++){
			$sth->execute();
			if (method_exists($Table, 'postcreate')) $Table->postcreate($db->lastInsertId());
			$sth_log->execute();

			if (!empty($_POST['Location'])){
				$sth = $db->prepare('REPLACE INTO location (Location) VALUES (:Location)');
				$sth->bindParam('Location', $row['Location']);
				$sth->execute();
			}
		}

		ob_end_clean();
		header('Location: ' . $_SERVER['PHP_SELF']);
		die();
	}
	else
		unset($_GET['SURE']);
}

if (!empty($_POST['duplicate']) && !empty($_POST['range'])){
	if ($Table->validate_duplicate_range()){
		?><div id="duplicaterecord">
			<h2>Are you sure you wish to create <?php echo ($_POST['End'] - $_POST['Start']) + 1; ?> copies of this record?</h2>
			<table class="tablesorter" id="List">
				<thead>
					<tr class="header"><?php
						foreach ($Table->Columns as $Header=>$Column)
							echo '<th>' . htmlentities($Header) . '</th>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';

					$sth = $db->prepare('SELECT * FROM `'. mysql_escape_string($Table->Table) . '` WHERE `' . mysql_escape_string($Table->Key) . '` = :Key');
					$sth->bindParam('Key', $_POST['duplicate']);
					$sth->execute();

					$i = 0;
					$row = $sth->fetch(PDO::FETCH_ASSOC);
					$class = '';

					$i++;
					if ($i % 2 == 0) $class = 'row2';			

					if (!empty($_GET['edit']) && $_GET['edit'] == $row[$Table->Key]) $class .= ' highlight';

					echo '<tr class="' . $class . '">';
						foreach ($Table->Columns as $Column)
							echo '<td class="nowrap">' . htmlentities($row[$Column]) . '</td>';
					?></tr>
				</tbody>
			</table>

			<table id="duplicaterecord_yesno">
				<tr>
					<td id="yes"><a href="?range=1&amp;Start=<?php echo $_POST['Start']; ?>&amp;End=<?php echo $_POST['End']; ?>&amp;duplicate=<?php echo htmlentities($_POST['duplicate']); ?>&amp;SURE">Yes, Duplicate Records</a></td>
					<td id="no"><a href="?">No, Return to Memory List</a></td>
				</tr>
			</table>
		</div><?php

		require('footer.inc.php');
		die();
	}
	else {
		$_GET['duplicate'] = $_POST['duplicate'];
		$_GET['range'] = $_POST['range'];
	}
}
if (!empty($_GET['duplicate']) && isset($_GET['range'])){
	?><div id="duplicaterange">
		<h2>Inserting Range</h2>
		<table class="tablesorter" id="List">
			<thead>
				<tr class="header"><?php
					foreach ($Table->Columns as $Header=>$Column)
						echo '<th>' . htmlentities($Header) . '</th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
				$sth = $db->prepare('SELECT * FROM `'. mysql_escape_string($Table->Table) . '` WHERE `' . mysql_escape_string($Table->Key) . '` = :Key');
				$sth->bindParam('Key', $_GET['duplicate']);
				$sth->execute();

				$i = 0;
				$row = $sth->fetch(PDO::FETCH_ASSOC);
				$class = '';

				$i++;
				if ($i % 2 == 0) $class = 'row2';			

				if (!empty($_GET['edit']) && $_GET['edit'] == $row[$Table->Key]) $class .= ' highlight';

				foreach ($Table->Columns as $Column)
					echo '<td class="nowrap">' . htmlentities($row[$Column]) . '</td>';
				?></tr>
			</tbody>
		</table>
	</div>

	<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
		<input type="hidden" name="duplicate" value="<?php echo htmlentities($_GET['duplicate']); ?>" />
		<input type="hidden" name="range" value="1" />
		<table class="form">
			<tr>
				<th>*Starting SN:</th>
				<td <?php if ($Table->validate_duplicate_range_field == 'Start') echo ' class="error"'; ?>>
					<input type="text" name="Start" id="Start" value="<?php if (!empty($_POST['Start'])) echo htmlentities($_POST['Start']); ?>" />
					<?php if ($Table->validate_duplicate_range_field == 'Start') echo '<div>' . $Table->validate_duplicate_range_error . '</div>'; ?>
				</td>
			</tr>
			<tr>
				<th>*Ending SN:</th>
				<td <?php if ($Table->validate_duplicate_range_field == 'End') echo ' class="error"'; ?>>
					<input type="text" name="End" id="End" value="<?php if (!empty($_POST['End'])) echo htmlentities($_POST['End']); ?>" />
					<?php 
 ?>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" value="Insert Records" /></td>
			</tr>
		</table>
	</form>
	<?php
	if (empty($Table->validate_duplicate_range_field)) $Table->validate_duplicate_range_field = 'Start';
	echo '<script type="text/javascript"> $(document).ready(function(){ $("#' . htmlentities($Table->validate_duplicate_range_field) . '").focus(); $("#' . htmlentities($Table->validate_duplicate_range_field) . '").select(); }); </script>';
	require('footer.inc.php');
	die();
}
/*****************************************************************************
 ************************** End Duplicate Range ******************************
 *****************************************************************************/

/*****************************************************************************
 **************************** Duplicate Batch ********************************
 *****************************************************************************/
if (!empty($_POST['duplicate']) && isset($_POST['batch'])){
	if ($Table->validate_duplicate_batch()){
		$sth = $db->prepare('SELECT * FROM `'. mysql_escape_string($Table->Table) . '` WHERE `' . mysql_escape_string($Table->Key) . '` = :Key');
		$sth->bindParam('Key', $_POST['duplicate']);
		$sth->execute();
		$row = $sth->fetch(PDO::FETCH_ASSOC);

		/**************** History logging *****************/
		$Date = date('Y-m-d');
		$Time = date('g:i:s A');
		$sth_log = $db->prepare('INSERT INTO history (Date_Changed, Time_Changed, Type, SN, Location_Old, Location_New) VALUES (:Date_Changed, :Time_Changed, :Type, :SN, "", :Location_New)');
		$sth_log->bindParam('Date_Changed', $Date);
		$sth_log->bindParam('Time_Changed', $Time);
		$sth_log->bindParam('Type', $Table->Type);
		$sth_log->bindParam('SN', $_POST[$Table->SN]);
		$sth_log->bindParam('Location_New', $row['Location']);
		$sth_log->execute();
		/*********** End of History logging *************/

		$Columns = $Params = array();
		$Columns[] = '`' . mysql_escape_string($Table->SN) . '`';
		$Params[] = ':' . mysql_escape_string($Table->SN);

		foreach ($Table->Columns as $Column){
			$Columns[] = '`' . mysql_escape_string($Column) . '`';
			$Params[] = ':' . $Column;
		}

		$sth = $db->prepare('REPLACE INTO `'. mysql_escape_string($Table->Table) . '` (' . implode(', ', $Columns) . ') VALUES (' . implode(', ', $Params) . ')');

		foreach ($Table->Columns_Unique as $Param){
			if (isset($Table->Column_Types[$Param]))
				$sth->bindParam($Param, $_POST[$Param], $Table->Column_Types[$Param]);
			else
				$sth->bindParam($Param, $_POST[$Param]);
		}
		foreach ($Table->Columns as $Param){
			if (isset($Table->Column_Types[$Param]))
				$sth->bindParam($Param, $row[$Param], $Table->Column_Types[$Param]);
			else
				$sth->bindParam($Param, $row[$Param]);
		}

		$sth->execute();

		if (!empty($row['Location'])){
			$sth = $db->prepare('REPLACE INTO location (Location) VALUES (:Location)');
			$sth->bindParam('Location', $_POST['Location']);
			$sth->execute();
		}

		if (method_exists($Table, 'postcreate')) $Table->postcreate($db->lastInsertId());

		header('Location: ?duplicate=' . urlencode($_POST['duplicate']) . '&batch');
		ob_end_clean();
		die();
	}
	elseif (empty($_POST[$Table->SN])){
		header('Location: ?');
		ob_end_clean();
		die();
	}
	else
		$_GET = $_POST;
}

if (!empty($_GET['duplicate']) && isset($_GET['batch'])){
	?><div id="duplicatebatch">
		<h2>Inserting Batch Entry</h2>
		<table class="tablesorter" id="List">
			<thead>
				<tr class="header"><?php
					foreach ($Table->Columns as $Header=>$Column)
						echo '<th>' . htmlentities($Header) . '</th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
				$sth = $db->prepare('SELECT * FROM `'. mysql_escape_string($Table->Table) . '` WHERE `' . mysql_escape_string($Table->Key) . '` = :Key');
				$sth->bindParam('Key', $_GET['duplicate']);
				$sth->execute();

				$i = 0;
				$row = $sth->fetch(PDO::FETCH_ASSOC);
				$class = '';

				$i++;
				if ($i % 2 == 0) $class = 'row2';			

				if (!empty($_GET['edit']) && $_GET['edit'] == $row[$Table->Key]) $class .= ' highlight';

				foreach ($Table->Columns as $Column)
					echo '<td class="nowrap">' . htmlentities($row[$Column]) . '</td>';
				?></tr>
			</tbody>
		</table>
	</div>

	<?php if (!empty($validate_duplicate_batch_error)) echo '<div class="error">' . $validate_duplicate_batch_error . '</div>'; ?>
	<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
		<input type="hidden" name="duplicate" value="<?php echo htmlentities($_GET['duplicate']); ?>" />
		<input type="hidden" name="batch" value="1" />
		<table class="form"><?php
	
			foreach ($Table->Columns_Unique as $Display=>$Column){
				echo '<tr><th>*' . htmlentities($Display) . ':</th><td';
				if ($Table->validate_duplicate_batch_field == $Column) echo ' class="error"';
				echo '><input type="text" name="' . htmlentities($Column) . '" id="' . htmlentities($Column) . '" value="';
				if (!empty($_POST[$Column])) echo htmlentities($_POST[$Column]);
				echo '" />';
				if ($Table->validate_duplicate_batch_field == $Column) echo '<div>' . $Table->validate_duplicate_batch_error . '</div>';
				echo '</td></tr>';
			}
	
			?><tr>
				<td></td>
				<td><input type="submit" value="Insert Record" /></td>
			</tr>
		</table>
	</form><?php
	if (empty($Table->validate_duplicate_batch_field)) $Table->validate_duplicate_batch_field = $Table->SN;
	echo '<script type="text/javascript"> $(document).ready(function(){ $("#' . htmlentities($Table->validate_duplicate_batch_field) . '").focus(); $("#' . htmlentities($Table->validate_duplicate_batch_field) . '").select(); }); </script>';

	require('footer.inc.php');
	die();
}
/*****************************************************************************
 ************************** End Duplicate Batch ******************************
 *****************************************************************************/

/*****************************************************************************
 ********************************* Edit **************************************
 *****************************************************************************/
if ($_POST['Form_Name'] == 'edit' && !empty($_POST[$Table->Key])){
	if ($Table->validate_edit()){
		/**************** History logging *****************/
		$sth = $db->prepare('SELECT Location FROM `'. mysql_escape_string($Table->Table) . '` WHERE `'. mysql_escape_string($Table->Key) . '` = :Key');
		$sth->bindParam('Key', $_POST[$Table->Key]);
		$sth->execute();
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		if (empty($row['Location'])) $row['Location'] = '';
		if (empty($_POST['Location'])) $_POST['Location'] = '';

		if ($row['Location'] != $_POST['Location']){
			$Date = date('Y-m-d');
			$Time = date('g:i:s A');
			$sth = $db->prepare('INSERT INTO history (Date_Changed, Time_Changed, Type, SN, Location_Old, Location_New) VALUES (:Date_Changed, :Time_Changed, :Type, :SN, :Location_Old, :Location_New)');
			$sth->bindParam('Date_Changed', $Date);
			$sth->bindParam('Time_Changed', $Time);
			$sth->bindParam('Type', $Table->Type);
			$sth->bindParam('SN', $_POST[$Table->SN]);
			$sth->bindParam('Location_Old', $row['Location']);
			$sth->bindParam('Location_New', $_POST['Location']);
			$sth->execute();
		}
		/*********** End of History logging *************/

		if ($_POST[$Table->Key] == 'new') $_POST[$Table->Key] = '';

		$Columns = $Params = array();
		if (!empty($_POST[$Table->Key])){
			$Columns[] = '`' . mysql_escape_string($Table->Key) . '`';
			$Params[] = ':' . mysql_escape_string($Table->Key);
		}

		foreach ($Table->Columns_Unique as $Column){
			$Columns[] = '`' . mysql_escape_string($Column) . '`';
			$Params[] = ':' . $Column;
		}
		foreach ($Table->Columns as $Column){
			$Columns[] = '`' . mysql_escape_string($Column) . '`';
			$Params[] = ':' . $Column;
		}

		$sth = $db->prepare('REPLACE INTO `'. mysql_escape_string($Table->Table) . '` (' . implode(', ', $Columns) . ') VALUES (' . implode(', ', $Params) . ')');
		if (!empty($_POST[$Table->Key]))
			$sth->bindParam($Table->Key, $_POST[$Table->Key]);

		foreach ($Params as $Param){
			$Param = preg_replace('/^:/', '', $Param);
			if (isset($Table->Column_Types[$Param]))
				$sth->bindParam($Param, $_POST[$Param], $Table->Column_Types[$Param]);
			else
				$sth->bindParam($Param, $_POST[$Param]);
		}
		$sth->execute();

		if (!empty($_POST['Location'])){
			$sth = $db->prepare('REPLACE INTO location (Location) VALUES (:Location)');
			$sth->bindParam('Location', $_POST['Location']);
			$sth->execute();
		}

		if (empty($_POST[$Table->Key]) && method_exists($Table, 'postcreate')) $Table->postcreate($db->lastInsertId());

		ob_end_clean();
		header('Location: ' . $_SERVER['PHP_SELF']);
		die();
	}
	else
		$_GET['edit'] = $_POST[$Table->Key];
}

if (!empty($_GET['edit']) || isset($_GET['new']) || !empty($_GET['duplicate'])){
	if (!empty($_GET['duplicate']))
		$_GET['edit'] = $_GET['duplicate'];

	if (!empty($_GET['edit']) && $_GET['edit'] == 'new'){
		$row = $_POST;
	}
	elseif (!empty($_GET['edit'])){
		$sth = $db->prepare('SELECT * FROM `' . mysql_escape_string($Table->Table) . '` WHERE `' . mysql_escape_string($Table->Key) . '` = :Key');
		$sth->bindParam('Key', $_GET['edit']);
		$sth->execute();
		$row = $sth->fetch(PDO::FETCH_ASSOC);
	}
	elseif (isset($_GET['new']))
		$row[$Table->Key] = 'new';

	if (!empty($_GET['duplicate'])){
		$row[$Table->SN] = '';
		$row[$Table->Key] = 'new';
	}

	?><div id="editform">
		<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
			<input type="hidden" name="Form_Name" value="edit" />
			<input type="hidden" name="<?php echo htmlentities($Table->Key); ?>" value="<?php echo htmlentities($row[$Table->Key]); ?>" />
			<table><?php
				foreach (array_merge($Table->Columns_Unique, $Table->Columns) as $Display=>$Column){
					echo '<tr><th>';
					if (in_array($Column, $Table->Columns_Required)) echo '*';
					echo htmlentities($Display) . '</th><td';
					if ($Table->validate_edit_field == $Column) echo ' class="error"';
					echo '>';

					if (in_array($Column, $Table->Columns_List)){
						echo '<input type="text" name="' . htmlentities($Column) . '" id="' . htmlentities($Column) . '" value="';
						if (!empty($row[$Column])) echo htmlentities($row[$Column]);
						echo '" /><img class="downarrow" id="' . htmlentities($Column) . '_List" src="downarrow.png" alt="[...]" title="[...]" />';

						if (!empty($Table->Columns_Foreign[$Column])){
							list($list_Table, $list_Column) = explode(':', $Table->Columns_Foreign[$Column], 2);
						}
						else {
							$list_Table = $Table->Table;
							$list_Column = $Column;
						}

						?><script type="text/javascript">
							$("#<?php echo htmlentities($Column); ?>").autocomplete({
								source:"./ajax.php?action=getlist&Table=<?php echo urlencode($list_Table); ?>&Column=<?php echo urlencode($list_Column); ?>",
								minLength: 0
							});

							$("#<?php echo htmlentities($Column); ?>_List").click(function(){
								input = $("#<?php echo htmlentities($Column); ?>");
								// close if already visible
								if (input.autocomplete("widget").is(":visible")){
									input.autocomplete("close");
									return;
								}

								// work around a bug (likely same cause as #5265)
								$(this).blur();

								// pass empty string as value to search for, displaying all results
								input.autocomplete("search", "");
								input.focus();
							});
						</script><?php
					}
					elseif (in_array($Column, $Table->Columns_Text)){
						echo '<textarea name="' . htmlentities($Column) . '" id="' . htmlentities($Column) . '">';
						if (!empty($row[$Column])) echo htmlentities($row[$Column]);
						echo '</textarea>';
					}
					else {
						echo '<input type="text" name="' . htmlentities($Column) . '" id="' . htmlentities($Column) . '" value="';
						if (!empty($row[$Column])) echo htmlentities($row[$Column]);
						echo '" />';
					}

					if ($Table->validate_edit_field == $Column) echo '<div>' . $Table->validate_edit_error . '</div>';
					echo '</td></tr>';
				}

			echo '</table>';
			if (empty($Table->validate_edit_field)) $Table->validate_edit_field = $Table->SN;

			?><script type="text/javascript">
				$(document).ready(function(){
					$("#<?php echo htmlentities($Table->validate_edit_field); ?>").focus();
					$("#<?php echo htmlentities($Table->validate_edit_field); ?>").select();
				});
			</script><?php

			echo '<input type="submit" value="Save Changes" />';
		echo '</form>';

		if (!empty($_GET['duplicate'])){
			?><table id="duplicatemode">
				<tr>
					<td><a href="?range&duplicate=<?php echo htmlentities($_GET['duplicate']); ?>">Insert Range</a></td>
					<td><a href="?batch&duplicate=<?php echo htmlentities($_GET['duplicate']); ?>">Batch</a></td>
				</tr>
			</table><?php
		}				

	echo '</div>';
	if (!isset($_GET['new'])){
		echo '<div id="duplicatedeleterecord">';
			echo '<div id="duplicaterecordlink"><a href="?duplicate=' . htmlentities($row[$Table->Key]) . '">Duplicate This Record</a></div>';
			echo '<div id="deleterecordlink"><a href="?delete=' . htmlentities($row[$Table->Key]) . '">Delete This Record</a></div>';
		echo '</div>';
	}
}
/*****************************************************************************
 ******************************* End Edit ************************************
 *****************************************************************************/

/*****************************************************************************
 ********************************* List **************************************
 *****************************************************************************/
if ($_GET['Form_Name'] == 'search' && !empty($_GET['query']) && !empty($_GET['searchfield'])){
	$sth = $db->prepare('SELECT * FROM `' . mysql_escape_string($Table->Table) . '` WHERE `' . mysql_escape_string($_GET['searchfield']) . '` LIKE :query');
	$query = '%' . $_GET['query'] . '%';
	$sth->bindParam('query', $query);
	$sth->execute();
}
else {
	$sth = $db->query('SELECT * FROM `' . mysql_escape_string($Table->Table) . '` ORDER BY `' . mysql_escape_string($Table->SN) . '`');
}

echo '<div id="rowcount">' . $sth->rowCount() . ' Rows Found</div>';
echo '<table class="tablesorter" id="List">';
	echo '<thead><tr class="header">';
		foreach ($Table->DisplayColumns_Unique as $Header=>$Column)
			echo '<th>' . htmlentities($Header) . '</th>';
		foreach ($Table->DisplayColumns as $Header=>$Column)
			echo '<th>' . htmlentities($Header) . '</th>';
	echo '</tr></thead><tbody>';


	$i = 0;
	while ($row = $sth->fetch(PDO::FETCH_ASSOC)){
		$class = '';

		$i++;
		if ($i % 2 == 0) $class = 'row2';			

		if (!empty($_GET['edit']) && $_GET['edit'] == $row[$Table->Key]) $class .= ' highlight';

		echo '<tr class="' . $class . '" onclick="location.href=\'?edit=' . $row[$Table->Key] . '\'">';
			foreach ($Table->DisplayColumns_Unique as $Column)
				echo '<td class="nowrap"><a href="?edit=' . htmlentities($row[$Table->Key]) . '">' . htmlentities($row[$Column]) . '</a></td>';
			foreach ($Table->DisplayColumns as $Column)
				echo '<td class="nowrap">' . htmlentities($row[$Column]) . '</td>';
		echo '</tr>';
	}
echo '</tbody></table>';
