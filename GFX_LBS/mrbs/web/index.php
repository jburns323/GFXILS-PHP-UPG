<?php

// $Id: index.php 1640 2010-11-24 17:50:28Z jberanek $

// Index is just a stub to redirect to the appropriate view
// as defined in config.inc.php using the variable $default_view
// If $default_room is defined in config.inc.php then this will
// be used to redirect to a particular room.

require_once "defaultincludes.inc";
require_once "mrbs_sql.inc";

switch ($default_view)
{
  case "month":
    $redirect_str = "month.php";
    break;
  case "week":
    $redirect_str = "week.php";
    break;
  case "day":
     $redirect_str = "day.php";
    break;
  case "home":
     $redirect_str = "home.php";
    break;
  default:
    $redirect_str = "home.php";
}

$redirect_str .= "?year=$year&month=$month&day=$day&area=$area&room=$room";

header("Location: $redirect_str");

?>