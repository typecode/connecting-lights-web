<?php

// util.php - utility functions for Connecting Light web site
// Created 5.11.12 by Derek Chung for Connecting Light
// Copyright (c) 2012 Tactile Pictures, Connecting Light

// ------------------------------------------------------------------------------------
// SYSTEM AND BROWSER INFORMATION
// ------------------------------------------------------------------------------------

function clInit()
{
	if (CL_DEBUG) {
		ini_set('display_errors','1');
	}
	
	date_default_timezone_set('Europe/London');
}

// ------------------------------------------------------------------------------------
// DATABASE UTILITIES
// ------------------------------------------------------------------------------------

function clDbConnect()
{
	$cldb = @mysql_pconnect(CL_DBHOST, CL_DBLOGIN, CL_DBPASS);	
	$dbname = clGetParamStr("db", CL_DBNAME);
	if ($cldb && @mysql_select_db($dbname, $cldb))
		return $cldb;

	return FALSE;
}

function clDbClose($cldb)
{
	mysql_close($cldb);
}

// just a cover routine for the query function, in case we have to 
// add code to it later.
function clDbQuery($sql)
{
	return mysql_query($sql);
}

function clGetMySqlError()
{
	if (CL_DEBUG)
		return "MySQL Error " . mysql_errno() . ": " . mysql_error();
	else
		return "Database error: " . mysql_errno();
}

function clGetInsertStrSQL($value, $prependcomma = true)
{
	return ($prependcomma ? ", " : "") . clMakeSQLString($value);
}

function clGetInsertIntSQL($value, $prependcomma = true)
{
	return ($prependcomma ? ", " : "") . clMakeSQLInt($value);
}

function clGetInsertFloatSQL($value, $prependcomma = true)
{
	return ($prependcomma ? ", " : "") . clMakeSQLFloat($value);
}

function clGetUpdateStrSQL($column, $value, $prependcomma = true)
{
	return ($prependcomma ? ", " : "") . "$column=" . clMakeSQLString($value);
}

function clGetUpdateIntSQL($column, $value, $prependcomma = true)
{
	return ($prependcomma ? ", " : "") . "$column=" . intval($value);
}

function clGetUpdateFloatSQL($column, $value, $prependcomma = true)
{
	return ($prependcomma ? ", " : "") . "$column=" . floatval($value);
}

// ------------------------------------------------------------------------------------
// deletes an object in the database -
function clDeleteObject($table, $idfield, $id)
{
	$sql = "DELETE FROM $table WHERE $idfield=" . $id;
	// print $sql;
	
	$result = mysql_query($sql);
	if (! $result)
		return CL_SQLERROR;
		
	return CL_SUCCESS;
}

// ------------------------------------------------------------------------------------
// approves a pending object in the database
function clApproveObject($table, $idfield, $id)
{
	// supersede any existing entries
	// ideally, we would do this as a trancltion in case the script dies in between this
	// SQL statement and the next
	clSupersedeObject($table, $idfield, $id);
	
	$sql = "UPDATE $table set status=" . CL_STATUS_ACTIVE;
	$sql .= " WHERE $idfield=$id AND status=" . CL_STATUS_PENDING;
	
	$result = mysql_query($sql);
	if (! $result)
		return CL_SQLERROR;
		
	if (mysql_affected_rows() == 0)
		return CL_NOTFOUND;
		
	return CL_SUCCESS;
}


// ------------------------------------------------------------------------------------
// sets value of the column in the table for the row with the given ID
function clSetField($table, $column, $idfield, $id, $value, $status = CL_STATUS_ACTIVE)
{
	$sql = "UPDATE $table SET $column=$value WHERE $idfield=$id";
	if ($status != CL_STATUS_ANY) 
		 $sql .= " AND (status=" . CL_STATUS_ACTIVE . ")";

	$result = clDbQuery($sql);
	if (! $result)
		return CL_SQLERROR;
	
	return CL_SUCCESS;
}

function clSetString($table, $column, $idfield, $id, $strvalue, $status = CL_STATUS_ANY) {
	return clSetField($table, $column, $idfield, $id, "'$strvalue'", $status);
}

// ------------------------------------------------------------------------------------
// returns a LIMIT clause to use in a SQL statement.  Increments maxitems by one so
// that the caller can figure out if it needs to show a "more..." link
function clMakeSQLLimit($maxitems = 0)
{
	if ($maxitems)
	{
		return " LIMIT $maxitems";
	}
	
	return "";
}

// ------------------------------------------------------------------------------------
// HTML UTILITIES
// ------------------------------------------------------------------------------------

// sets content type for the returned data.  must be called before any other content is delivered
function clSetContentType($type, $filename = "") 
{
	header("Content-Type:$type");
	
	if ($filename) {
		header("Content-Disposition:attachment;filename=$filename");
	}
	else {
		header('Content-Disposition: inline; filename=sample.xml');
	}
}


// returns debug text to the client (wrapped in a XML/HTML comment)
function clPrintDebug($str)
{	
	print "<!-- $str -->";
}

// returns HTML to client.  divides long HTML strings into shorter segments to
// avoid potential network problems
function clPrintHtml($html, $bufferSize = 16384)
{	
	$len = strlen($html);
	
	for ($i = 0; $i < $len; $i += $bufferSize)
	{
		echo substr($html, $i, $bufferSize);
	}
}

// returns XML to client.  divides long XML strings into shorter segments to
// avoid potential network problems
function clPrintJSON($json, $bufferSize = 8192)
{	
	// set http headers
	clSetContentType("application/json");
		
	$len = strlen($json);
	
	for ($i = 0; $i < $len; $i += $bufferSize)
	{
		echo substr($json, $i, $bufferSize);
	}
}

// returns HTML for standard HTML tag, with optional CSS tag
function clGetTag($tag, $text, $class = "", $id = "")
{
	if ($text !== "") {
		$classattr = ($class ? " class=\"$class\"" : "");
		$idattr = ($id ? " id=\"$id\"" : "");
		return "<$tag$idattr$classattr>$text</$tag>";
	}
	
	return "";
}

// returns HTML for a standard page heading
function clGetPageHeading($heading, $class = "")
{
	return clGetTag("h2", $heading, $class);
}

// returns HTML for a standard page subheading
function clGetSubHeading($subheading, $class = "")
{
	return clGetTag("h3", $subheading, $class);
}

// returns HTML for standard paragraph text
function clGetParagraph($paragraph, $class = "")
{
	return clGetTag("p", $paragraph, $class);
}

// returns HTML for standard HTML div
function clGetDiv($text, $class = "", $id = "")
{
	return clGetTag("div", $text, $class, $id);
}

// returns HTML for standard HTML span
function clGetSpan($text, $class = "", $id = "")
{
	return clGetTag("span", $text, $class, $id);
}

// returns HTML for text and linebreak
function clGetLine($text)
{
	if (trim($text))
		return "$text<br>\n";
		
	return "";
}

// simple function to construct a normal HTML link
// optional third and fourth parameters
// $target (link target) and $class (CSS style)
function clGetLink($href, $text)
{
	$target = (func_num_args() > 2 && func_get_arg(2) ? "target=\"" . func_get_arg(2) . "\"" : "");
	$class  = (func_num_args() > 3 && func_get_arg(3) ? "class=\"" . func_get_arg(3) . "\""  : "");
	
	return "<a href=\"$href\" $target $class>$text</a>";
}

// returns a new string with the new HTML appended, separated from the base string by the delimiter
function clAppendHtml($base, $html, $delimiter = " ") 
{
	if ($base == "")
		return $html;
		
	if ($html == "")
		return $base;
		
	return "$base$delimiter$html";
}


// returns text with line breaks, unless the first nonspace character is "<".
// in that case, this assumes the string is HTML and does not insert line breaks.
function clGetDisplayText($str, $strip_paragraph = false, $encode = true)
{
	if ($encode)
		$str = clHtmlEncode($str);
	
	return $str;
}

// HTML-encodes an output string, to help prevent scripting attacks
function clHtmlEncode($str)
{
	return htmlentities($str);
}

// returns the script name for the current request
function clGetScript()
{
	return $_SERVER['SCRIPT_NAME'];
}

function clGetTableHtml($rows, $width = 0, $class = "formtable", $id = "")
{
	if ($width)
		$width = "width=\"$width\"";
	else
		$width = "";
	
	if ($id != "") {
		$id = " id=\"$id\"";
	}
	
	$html  = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" $width $id class=\"$class\"><tbody>" . "\n";
	$html .= $rows;
	$html .= "</tbody></table>\n";

	return $html;
}

function clGetTableRowHtml($cells, $class = "", $id = "")
{
	return clGetTag("tr", $cells, $class = "", $id = "");
}

function clGetTableCellHtml($content, $class = "", $id = "")
{
	return clGetTag("td", $content, $class = "", $id = "");
}


// ------------------------------------------------------------------------------------
// PARAMETER UTILITIES
// ------------------------------------------------------------------------------------

// Parameter retrieval functions.  Starting with PHP 4.1(?) the default is not
// to set up global variables for the GET and POST parameters, so we need to
// retrieve them from the _GET and _POST arrays.  It's also good to call intval()
// on parameters that are supposed to be integers so that no one tries to do 
// anything evil by passing in SQL code or something like that.

// of each set of three, call the first if you don't know and don't care if the
// parameter is coming from a GET or a POST query; call the specific one if you
// know what kind of query it should be.
 
// retrieves an integer query parameter from the query.  Returns zero for NULL values.
function clGetParamInt($name, $defvalue = 0)
{
	if (clParamIsNull($name))
		return $defvalue;
		
	$value = clGetParam($name);
	return $value ? intval($value) : 0;
}
function clGetParamGetInt($name, $defvalue = 0)
{
	if (clParamIsNull($name))
		return $defvalue;
		
	$value = clGetParamGet($name);
	return $value ? intval($value) : 0;
}
function clGetParamPostInt($name, $defvalue = 0)
{
	if (clParamIsNull($name))
		return $defvalue;
		
	$value = clGetParamPost($name);
	return $value ? intval($value) : 0;
}

// retrieves a string query parameter.  adds slashes to single quotes to prevent
// messing up SQL statements.  Returns an empty string for NULL values.
function clGetParamStr($name, $defvalue = "")
{
	$value = clGetParam($name, $defvalue);
	return strlen($value) ? clStripMostSlashes($value) : $defvalue;
}
function clGetParamGetStr($name, $defvalue = "")
{
	$value = clGetParamGet($name, $defvalue);
	return strlen($value) ? clStripMostSlashes($value) : $defvalue;
}
function clGetParamPostStr($name, $defvalue = "")
{
	$value = clGetParamPost($name, $defvalue);
	return strlen($value) ? clStripMostSlashes($value) : $defvalue;
}

function clGetParam($name, $defvalue = NULL)
{
	return (array_key_exists($name, $_POST) ? $_POST[$name] : (array_key_exists($name, $_GET) ? $_GET[$name] : $defvalue));
}
function clGetParamGet($name, $defvalue = 0)
{
	return (array_key_exists($name, $_GET) ? $_GET[$name] : $defvalue);
}
function clGetParamPost($name, $defvalue = 0)
{
	return (array_key_exists($name, $_POST) ? $_POST[$name] : $defvalue);
}

function clParamIsNull($name)
{
	return ! (array_key_exists($name, $_POST) || array_key_exists($name, $_GET));
}


// ------------------------------------------------------------------------------------
// STRING AND NUMBER UTILITIES
// ------------------------------------------------------------------------------------

// adds backslash to double-quotes within the string
function clSlashDoubleQuotes($str)
{
	return str_replace('"', '\"', $str);
}

// tests string against words in the blacklist array.
// returns true if any part of the string matches any of the words in the blacklist (case insensitive)
function clCheckBlacklist($str, $blacklist)
{
	for ($i = 0; $i < count($blacklist); $i++)
	{
		if (! (strpos(strtolower($str), strtolower($blacklist[$i])) === FALSE))
			return true;
	}
	
	return false;
}

// use PHP/MySQL function to make string safe for SQL statement
function clAddSQLSlashes($str)
{
	return mysql_real_escape_string($str); 
}

// strips script tags and their contents
function clStripScripts($str)
{
	$str = preg_replace("/<script[^>]*>.*?<\/script[^>]*>/is", "", $str);
	return preg_replace("/<iframe[^>]*>.*?<\/iframe[^>]*>/is", "", $str);
}

function clMakeSQLString($str)
{
	return "'" . clAddSQLSlashes($str) . "'";
}

function clMakeSQLInt($n)
{
	return ($n ? intval($n) : 0);
}

function clMakeSQLFloat($n)
{
	return ($n ? floatval($n) : 0);
}


// strips backslashes except for ones around double-quotes
function clStripMostSlashes($str)
{
	return clEscapeDoubleQuotes(stripslashes(clStripScripts($str)));
}

// changes double-quotes to &quot; within the string
function clEscapeDoubleQuotes($str)
{
	return str_replace('"', '&quot;', $str);
}

// changes double-quotes to &quot; within the string
function clUnescapeDoubleQuotes($str)
{
	return str_replace('&quot;', '"', $str);
}

// returns the string if set, default if empty
function clUseDefault($str, $default)
{
	return ($str ? $str : $default);
}

// ------------------------------------------------------------------------------------
// DATE UTILITIES
// ------------------------------------------------------------------------------------

// constructs a date string for use in a SQL statement out of the parameters values.
// returns an empty string if all of the values are zero or NULL.
function clMakeSQLDate($year, $month, $day, &$dateflags)
{
	if ($year == 0 && $month == 0 && $day == 0)
	{
		$dateflags = CL_DATEFLAG_NOYEAR | CL_DATEFLAG_NOMONTH | CL_DATEFLAG_NODAY;
		return "";
	}
	
	// set defaults, if necessary	
	$today = getdate();
	$dateflags = 0;
	
	if (! $year)
	{
		$year = $today['year'];
		$dateflags |= CL_DATEFLAG_NOYEAR;
	}
	if (! $month)
	{
		$month = $today['mon'];
		$dateflags |= CL_DATEFLAG_NOMONTH;
	}
	if (! $day)
	{
		$day = 28; // $today['mday'];
		$dateflags |= CL_DATEFLAG_NODAY;
	}
	
	return "$year-$month-$day";
}


// constructs a date string for use in a SQL statement out of the parameters values.
// returns an empty string if all of the values are zero or NULL.
function clMakeSQLTime($hour, $minute, $second, &$dateflags)
{
	if ($hour == 0 && $minute == 0 && $second == 0)
	{
		$dateflags |= CL_DATEFLAG_NOHOUR | CL_DATEFLAG_NOMINUTE | CL_DATEFLAG_NOSECOND;
		return "";
	}
	
	return "$hour:$minute:$second";
}

function clMakeSQLDateTime($year, $month, $day, $hour, $minute = 0, $second = 0) 
{
	return "$year-$month-$day $hour:$minute:$second";
}


function clSQLDateFromDateTime($sqldatetime, $dateflags = 0)
{
	clParseSQLDate($sqldatetime, $year, $month, $day);
	
	return clMakeSQLDate($year, $month, $day, $newdateflags);
}


function clGetDate(&$year, &$month, &$day)
{
	// set defaults, if necessary	
	$today = getdate();
	$year = $today['year'];
	$month = $today['mon'];
	$day = $today['mday'];
}

// returns current date and time in SQL format
// TODO: handle time zones
function clGetSQLDateTime()
{
	$now = getdate();
	return clMakeSQLDateTime($now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']); 
}


// parses a date string from a SQL query out of the parameters values.
function clParseSQLDate($sqldate, &$year, &$month, &$day)
{
	if ($sqldate)
	{
	   $year = strtok($sqldate, "-");
	   $month = strtok("-");
	   $day = strtok(" ");
	}
	else
	{
		// set defaults, if necessary	
		$today = getdate();
		$year == $today['year'];
		$month == $today['month'];
		$day == $today['mday'];
	}
}

// parses a date and time string from a SQL query out of the parameters values.
function clParseSQLDateTime($sqldate, &$year, &$month, &$day, &$hour, &$minute)
{
	if ($sqldate)
	{
	   $year = strtok($sqldate, "-");
	   $month = strtok("-");
	   $day = strtok(" ");
	   $hour = strtok(":");
	   $minute = strtok(":");
	}
	else
	{
		// set defaults, if necessary	
		$today = getdate();
		$year == $today['year'];
		$month == $today['month'];
		$day == $today['mday'];
		$hour == $today['hours'];
		$minute == $today['minutes'];
	}
}

// constructs a date string for on screen display from the parameter.
// returns an empty string if all of the values are zero or NULL.
// The date format can be passed in as the second parameter, otherwise it defaults
// to something like "Monday, April 1, 2001 12:01 PM"
function clGetDisplayDateTime($sqldatetime, $flags = 0, $dateformat = "F j, Y", $timeformat = "H:i:s")
{
	if ($sqldatetime)
	{
		$timestamp = strtotime($sqldatetime);
		if (! $timestamp)
		{
		   $year = strtok($sqldatetime, "-");
		   $month = strtok("-");
		   $day = strtok(" ");
		   $hour = strtok(":");
		   $minute = strtok(":");
		   $second = strtok(" ");
	   	   if ($year > 0 && $month > 0 && $day > 0)
	   	   	  $timestamp = mkTime($hour, $minute, $second, $month,$day,$year);
		}
		
		if ($timestamp)
			return date(rtrim("$dateformat $timeformat"), $timestamp);
	}
  	return "";
}

// returns formatted date
function clGetFormattedDate($year, $month, $day, $format)
{
  	return date($format, mkTime(0,0,0,$month,$day,$year));
}


?>