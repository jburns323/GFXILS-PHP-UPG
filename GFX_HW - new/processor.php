<?php
$_mainmenulink = true;

require('includes.inc.php');
require('header.inc.php');

class Processor {
	public $Table = 'processor';
	public $Type = 'Processor';

	public $Key = 'ProcessorID';
	public $SN = 'Processor_ID';

	// Columns to allow editing
	public $Columns_Unique = array('Processor_ID'=>'Processor_ID', 'Processor_SN'=>'Processor_SN');
	public $Columns = array('Processor'=>'Processor', 'Processor_SKU'=>'Processor_SKU', 'Processor_Stepping'=>'Processor_Stepping', 'GFX_Stepping'=>'GFX_Stepping', 'QDF'=>'QDF', 'Speed'=>'Speed', 'Package_Stepping'=>'Package_Stepping', 'Location'=>'Location', 'Processor_Skew'=>'Processor_Skew', 'Comments'=>'Processor_Comments');

	// Columns to display in the table
	public $DisplayColumns_Unique = array('Processor_ID'=>'Processor_ID', 'Processor_SN'=>'Processor_SN');
	public $DisplayColumns = array('Date_Received'=>'Date_Received', 'Processor'=>'Processor', 'Processor_SKU'=>'Processor_SKU', 'Processor_Stepping'=>'Processor_Stepping', 'GFX_Stepping'=>'GFX_Stepping', 'QDF'=>'QDF', 'Speed'=>'Speed', 'Package_Stepping'=>'Package_Stepping', 'Location'=>'Location', 'Processor_Skew'=>'Processor_Skew', 'Comments'=>'Processor_Comments');

	// Column Types
	public $Column_Types = array();

	// Required Columns
	public $Columns_Required = array('Processor_ID', 'Processor_SN', 'Location');

	public $Columns_Foreign = array('Location'=>'location:Location');

	// These will be dropdown lists of values currently in the table
	public $Columns_List = array('Date_Received', 'Processor', 'Processor_SKU', 'Processor_Stepping', 'GFX_Stepping', 'QDF', 'Speed', 'Package_Stepping', 'Location', 'Processor_Skew');

	// These will be freeform textarea inputs instead of one-line text fields
	public $Columns_Text = array('Processor_Comments');

	public $validate_edit_error;
	public $validate_edit_field;
	function validate_edit(){
		$this->validate_edit_error = $this->validate_edit_field = '';

		foreach ($this->Columns_Required as $Column){
			if (empty($_POST[$Column])){
				$this->validate_edit_error = 'Required field empty: ' . htmlentities($Column);
				$this->validate_edit_field = $Column;
				return false;
			}
		}

		if ($_POST[$this->Key] != 'new'){
			if (!preg_match('/^\d+$/', $_POST[$this->Key])){
				$this->validate_edit_error = htmlentities($this->Key) . ' must be numeric';
				$this->validate_edit_field = $this->Key;
				return false;
			}

			global $db;
			$sth = $db->prepare('SELECT COUNT(*) FROM `' . mysql_escape_string($this->Table) . '` WHERE `' . mysql_escape_string($this->Key) . '` = :Key');
			$sth->bindParam('Key', $_POST[$this->Key]);
			$sth->execute();
			$row = $sth->fetch(PDO::FETCH_NUM);
			if ($row[0] != 1){
				$this->validate_edit_error = 'Provided `' . htmlentities($this->Key) . '` doesn\'t exist in table';
				$this->validate_edit_field = $this->Key;
				return false;
			}
		}

		return true;
	}

	public $validate_duplicate_range_error;
	public $validate_duplicate_range_field;
	function validate_duplicate_range(){
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
			$_POST = $_GET;

		if (empty($_POST['Start'])){
			$this->validate_duplicate_range_error = 'Required Field Empty: Starting SN';
			$this->validate_duplicate_range_field = 'Start';
			return false;
		}

		if (empty($_POST['End'])){
			$this->validate_duplicate_range_error = 'Required Field Empty: Ending SN';
			$this->validate_duplicate_range_field = 'End';
			return false;
		}

		$_POST['Start'] = (int) $_POST['Start'];
		$_POST['End'] = (int) $_POST['End'];

		if ($_POST['Start'] < 1){
			$this->validate_duplicate_range_error = 'Starting SN must be a positive integer';
			$this->validate_duplicate_range_field = 'Start';
			return false;
		}

		if ($_POST['End'] < 1){
			$this->validate_duplicate_range_error = 'Ending SN must be a positive integer';
			$this->validate_duplicate_range_field = 'End';
			return false;
		}

		if ($_POST['End'] <= $_POST['Start']){
			$this->validate_duplicate_range_error = 'Ending SN must be greater than Starting SN';
			$this->validate_duplicate_range_field = 'End';
			return false;
		}

		global $db;
		$sth = $db->prepare('SELECT COUNT(*) AS Count FROM `' . mysql_escape_string($this->Table) . '` WHERE `' . mysql_escape_string($this->SN) . '` >= :Start AND `' . mysql_escape_string($this->SN) . '` <= :End');
		$sth->bindParam('Start', $_POST['Start'], PDO::PARAM_INT);
		$sth->bindParam('End', $_POST['End'], PDO::PARAM_INT);
		$sth->execute();

		$row = $sth->fetch(PDO::FETCH_ASSOC);
		if ($row['Count'] > 0){
			$this->validate_duplicate_range_error = $row['Count'] . ' entries already exist in the specified range';
			$this->validate_duplicate_range_field = 'Start';
			return false;
		}

		return true;
	}

	public $validate_duplicate_batch_error;
	public $validate_duplicate_batch_field;
	function validate_duplicate_batch(){
		foreach ($this->Columns_Unique as $Column){
			if (empty($_POST[$Column])){
				$this->validate_edit_error = 'Required field empty: ' . htmlentities($Column);
				$this->validate_edit_field = $Column;
				return false;
			}
		}

		global $db;
		$sth = $db->prepare('SELECT COUNT(*) AS Count FROM `' . mysql_escape_string($this->Table) . '` WHERE `' . mysql_escape_string($this->SN) . '` = :SN');
		$sth->bindParam($this->SN, $_POST[$this->SN]);
		$sth->execute();
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		if ($row['Count'] > 0){
			$this->validate_duplicate_batch_error = 'A record already exists with ' . $this->SN . ' = ' . $_POST[$this->SN];
			$this->validate_duplicate_batch_field = $this->SN;
			return false;
		}

		return true;
	}

	function postcreate($Key){
		global $db;
		$Date = date('Y-m-d');
		$sth = $db->prepare('UPDATE `' . mysql_escape_string($this->Table) . '` SET Date_Received = :Date_Received WHERE `' . mysql_escape_string($this->Key) . '` = :Key');
		$sth->bindParam('Key', $Key);
		$sth->bindParam('Date_Received', $Date);
		$sth->execute();
	}
}
$Table = new Processor;

require('table.inc.php');
require('footer.inc.php');
