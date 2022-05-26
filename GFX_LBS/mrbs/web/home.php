<?php
require_once "defaultincludes.inc";
require_once "mincals.inc";
require_once "theme.inc";
// Get non-standard form variables
$timetohighlight = get_form_var('timetohighlight', 'int');
$debug_flag = get_form_var('debug_flag', 'int');

if (empty($debug_flag))
{
  $debug_flag = 0;
}

// Check the user is authorised for this page
//checkAuthorised();

// form the room parameter for use in query strings.    We want to preserve room information
// if possible when switching between views
if (empty($room))
{
  $room_param = "";
}
else
{
  $room_param = "&amp;room=$room";
}

// print the page header
print_header($day, $month, $year, $area, isset($room) ? $room : "");

$format = "Gi";
if ( $enable_periods )
{
  $format = "i";
  $resolution = 60;
  $morningstarts = 12;
  $morningstarts_minutes = 0;
  $eveningends = 12;
$eveningends_minutes = count($periods)-1;
}
//https://gfxemucal.so.intel.com/day.php
?>
<p>Welcome to the GFX SV Reservation System Home Page!</p>
<table style="width:100%">
	<tr>
		<td style="width:50%">
			<h2>GFX SV Silicon Calender</h2>
			<ul class="mainmenu">
				<li><a href="https://gfxlabman.so.intel.com/day.php"> Silicon Calendar</a></li>
				
			</ul>
		</td>
	</tr>
</table>


