<?php
$_mainmenulink = true;

require('includes.inc.php');
require('header.inc.php');

class Video {
	public $Table = 'video_card';
	public $Type = 'Video Card';

	public $Key = 'Video_Card_ID';
	public $SN = 'Video_Card_SN';

	// These get displayed in order
	public $Columns_Unique = array('Video_Card_SN'=>'Video_Card_SN');
	public $Columns = array('Video_Card_Type'=>'Video_Card_Type', 'Location'=>'Location', 'Comments'=>'Video_Card_Comments');

	public $DisplayColumns_Unique = array('Video_Card_SN'=>'Video_Card_SN');
	public $DisplayColumns = array('Video_Card_Type'=>'Video_Card_Type', 'Location'=>'Location', 'Comments'=>'Video_Card_Comments');

	// Column Types
	public $Column_Types = array();

	// Required Columns
	public $Columns_Required = array('Video_Card_SN', 'Location');

	public $Columns_Foreign = array('Location'=>'location:Location');

	// These will be dropdown lists of values currently in the table
	public $Columns_List = array('Video_Card_Type', 'Location');

	// These will be freeform textarea inputs instead of one-line text fields
	public $Columns_Text = array('Video_Card_Comments');

	public $validate_edit_error;
	public $validate_edit_field;
	function validate_edit(){
		$this->validate_edit_error = $this->validate_edit_field = '';

		if (empty($_POST[$this->SN])){
			$this->validate_edit_error = 'Required field empty: ' . htmlentities($this->SN);
			$this->validate_edit_field = $this->SN;
			return false;
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
		if (empty($_POST[$this->SN])) return false;

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
}
$Table = new Video;

require('table.inc.php');
require('footer.inc.php');
