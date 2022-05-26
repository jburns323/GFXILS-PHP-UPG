<?php

// $Id: version.inc 1842 2011-07-13 19:52:31Z jberanek $

// This should probably be a class, but you can only have protected
// class members in PHP 5, so we won't bother

function get_mrbs_version()
{
  // MRBS developers, make sure to update this string before each release
  $mrbs_version = "Rev 0.7";

  return $mrbs_version;
}

?>
