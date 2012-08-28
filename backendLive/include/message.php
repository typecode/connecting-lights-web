<?php

// message.php - functions for managing text messages
// Created 5.11.12 by Derek Chung for Connecting Light
// Copyright (c) 2012 Tactile Pictures, Connecting Light

// ------------------------------------------------------------------------------------
/* N O T E S

   These scripts manage text messages stored in the "message" table in the database.  
 */

// ------------------------------------------------------------------------------------
// C O N S T A N T S

// formats
define("CL_MESSAGE_FORMAT_LIST", 1);
define("CL_MESSAGE_FORMAT_JSON", 2);
define("CL_MESSAGE_FORMAT_ADMIN", 3);

// attribute bits
define("CL_MESSAGE_ATTR_STATUS", 1);
define("CL_MESSAGE_ATTR_POSTDATE", 2);
define("CL_MESSAGE_ATTR_QUESTION", 4);
define("CL_MESSAGE_ATTR_ID", 8);
define("CL_MESSAGE_ATTR_TIMESTAMP", 16);

// statuses for messages
define("CL_STATUS_PENDING", 0);			// a new record that has not been approved
define("CL_STATUS_ACTIVE", 1);			
define("CL_STATUS_REJECTED", 2);			
define("CL_STATUS_ANY", -1);

// status labels
$g_statuses = array('pending', 'active', 'rejected');


// generic sort orders
define("CL_SORT_DEFAULT", 0);
define("CL_SORT_ASCENDING", 1);
define("CL_SORT_DESCENDING", -1);

//default red, green, blue
define("CL_DEFAULT_RED", 0);
define("CL_DEFAULT_GREEN", 0);
define("CL_DEFAULT_BLUE", 0);


// ------------------------------------------------------------------------------------
// F U N C T I O N S

// ------------------------------------------------------------------------------------
// retrieves the message and sets up global variables with the message properties
function clGetMessage($messageid)
{
	$message = new clMessage($messageid);
	
	if ($message->LoadFromDB())
		return $message;
		
	return NULL;
}

function clGetRandomQuestion($exclude = -1)
{
	$q = 0;
	$num = 0;
	
	$sql = "SELECT COUNT(*) num FROM questions";
	$result = clDbQuery($sql);
	if ($result) {
		$row = mysql_fetch_array($result);
		if ($row) {
			$num = $row["num"];
		}
	}
	
	do {
	
		$sql = "SELECT * FROM questions ORDER BY RAND() LIMIT 1";
		$result = clDbQuery($sql);
		if (! $result)
			return 0;
			
		$row = mysql_fetch_array($result);
		if ($row)
		{
			// return the question object
			$q = new clQuestion($row["id"]);
			$q->LoadFromRow($row);
		}
		else {
			break;
		}
		
		// check count from row to prevent looping if there's only one question in DB
	} while ($q && ($q->id == $exclude && $num > 1));
	
	mysql_free_result($result);
	
	return $q;
}


// ------------------------------------------------------------------------------------
// clMessage Class
// ------------------------------------------------------------------------------------
class clMessage {
	var $mask;			// when used as an attribute record
	
	// these correspond directly to database fields
	var $id;
	var $status;		// pending (unapproved) or active (approved)
	var $question;		// question ID
	var $postdate;		// time posted (SQL format)
	var $timestamp; 	// unix timestamp of time posted (returned from MySQL query)
	var $message;
	var $latitude;
	var $longitude;
	var $red;				// red, green, blue values
	var $green;
	var $blue;
	
	// ====================================================================================
	// clMessage: constructor
	function clMessage($id = 0)
	{
		$this->id     = $id;
		$this->mask   = 0;
		$this->status = CL_STATUS_PENDING;
		$this->question = 0;
		$this->message  = "";
		$this->timestamp = 0;
		$this->red = CL_DEFAULT_RED;
		$this->green = CL_DEFAULT_GREEN;
		$this->blue = CL_DEFAULT_BLUE;
	}

	// ====================================================================================
	// loads the message from the database
	function LoadFromDB()
	{
		// load message from database
		$sql = "SELECT * from message where (id = '$this->id')";
		
		$result = clDbQuery($sql);
		if (! $result)
			return FALSE;
	
		$row = mysql_fetch_array($result);
		if ($row)
		{
			$this->LoadFromRow($row);			
			mysql_free_result($result);
		
			return TRUE;
		}
		
		mysql_free_result($result);
		return FALSE;
	}
			
	// ====================================================================================
	// loads message from query results
	function LoadFromRow($row)
	{
		if ($row)
		{
			$this->id = $row["id"];
			$this->postdate = $row["postdate"];
			$this->timestamp = $row["timestamp"];
			$this->status = $row["status"];
			$this->question = $row["question"];
			$this->message = $row["message"];
			$this->latitude = $row["latitude"];	
			$this->longitude = $row["longitude"];	
			$this->red = $row["red"];
			$this->green = $row["green"];
			$this->blue = $row["blue"];
		}
	}

	// ====================================================================================
	// loads message from POSTed data (works for GET too)
	function LoadFromPost()
	{
		$html = "";

		if ($this->id == 0)
			$this->id = clGetParamInt("id");
			
		$this->message = clGetParamStr("m");
		$this->question = clGetParamStr("q");
		$this->red = clGetParamInt("r", CL_DEFAULT_RED);
		$this->green = clGetParamInt("g", CL_DEFAULT_GREEN);
		$this->blue = clGetParamInt("b", CL_DEFAULT_BLUE);

		if (! clParamIsNull("t"))
		{
			$this->mask = CL_MESSAGE_ATTR_TIMESTAMP;
			$this->timestamp = clGetParamInt("t");
		}
		
		return $html;
	}
	
	// ====================================================================================
	// returns HTML to display message properties.  
	function GetHtml($script = "", $headingclass = "", $containerclass = "")
	{
		$html  = clGetSubHeading($this->heading, $headingclass);
		$html .= clGetDisplayText($this->message, false, false);
		
		return ($containerclass ? clGetDiv($html, $containerclass) : $html);
	}

	// ====================================================================================
	function GetJSON($startindex = 0, $endindex = -1, $starttime = 0, $endtime = 0, $question = 0, $status = CL_STATUS_ANY, $maxitems = 0, $random = false)
	{
		$results = array();
		
		// construct WHERE clause for the SQL statement based on parameters, if set
		if ($question > 0) {
			$this->mask |= CL_MESSAGE_ATTR_QUESTION;
			$this->question = $question;
		}
		if ($status != CL_STATUS_ANY) {
			$this->mask |= CL_MESSAGE_ATTR_STATUS;
			$this->status = $status;
		}
		
		$where = $this->GetWhereClause($startindex, $endindex, $starttime, $endtime);
		$limit = clMakeSQLLimit($maxitems);
		$orderby = ($random ? "ORDER BY RAND()" : "ORDER BY id ");
					
		// retrieve activities matching the form parameters
		// TODO: see if need $sql = "SELECT message.*, UNIX_TIMESTAMP(postdate) timestamp FROM message $where $orderby $limit"; 
		$sql = "SELECT message.* FROM message $where $orderby $limit";
		// clPrintDebug($sql);
		$result = clDbQuery($sql);
		if (! $result)
			return clGetMySqlError();
			
		$numrows = mysql_num_rows($result);
		if ($numrows > 0)
		{
			// return each message as array
			for ($i=0; $row = mysql_fetch_array($result); ++$i)
			{
				$msg = new clMessage($row["id"]);
				$msg->LoadFromRow($row);
				
				array_push($results, $msg->GetArrayForJSON());
			}
		}
		
		mysql_free_result($result);
		
		return json_encode(array("messages" => $results));
	}
	
	// ====================================================================================
	function GetArrayForJSON()
	{
		$arr = array("id" => $this->id, "m" => $this->message, "postdate" => $this->postdate, "timestamp" => $this->timestamp, "r" => $this->red, "g" => $this->green, "b" => $this->blue);
		if ($this->question > 0) {
			$arr["q"] = $this->question;
		}
		
		return $arr;
	}
	
	// ====================================================================================
	function GetList($script = "", 
			$linkop = "view", 
			$timeframe = 0, 
			$format = CL_MESSAGE_FORMAT_LIST, 
			$maxitems = 0, 
			$sortby = "startdate",
			$sortdir = 1,
			$startindex = 0)
	{
		$html  = "";
		$limit = "";
		$tables = "message";
		
		// construct WHERE clause for the SQL statement based on form parameters, if set
		$where = $this->GetWhereClause($startindex);
			
		$orderby = "ORDER BY message.$sortby ";
		if ($sortdir == -1)
			$orderby .= "DESC ";
			
		$limit = clMakeSQLLimit($maxitems);
		
		// retrieve activities matching the form parameters
		$sql = "SELECT message.* FROM $tables $where $orderby $limit";
		// clPrintDebug($sql);
		$result = clDbQuery($sql);
		if (! $result)
			return clGetMySqlError();
			
		$headers =  clGetTableCellHtml("ID");
		$headers .= clGetTableCellHtml("status");
		if ($format == CL_MESSAGE_FORMAT_ADMIN)
			$headers .= clGetTableCellHtml("admin");
		$headers .= clGetTableCellHtml("time");
		$headers .= clGetTableCellHtml("q");
		$headers .= clGetTableCellHtml("message");
		$html .= clGetTableRowHtml($headers);
		
		$numrows = mysql_num_rows($result);
		if ($numrows > 0)
		{
			// return each message with a link
			for ($i=0; ($row = mysql_fetch_array($result)) && ($maxitems == 0 || $i < $maxitems); ++$i)
			{
				$msg = new clMessage($row["id"]);
				$msg->LoadFromRow($row);
				$html .= clGetTableRowHtml($msg->GetListing($script, $linkop, $format));
			}
		}
		
		mysql_free_result($result);
		
		return clGetTableHtml($html);
	}
	
	// ====================================================================================
	function GetListing($script = "", $op = "", $format = CL_MESSAGE_FORMAT_LIST)
	{
		global $g_statuses;
		
		$html = "";
		
		$html .= clGetTableCellHtml($this->id);	
		$html .= clGetTableCellHtml($g_statuses[$this->status]);	
		
		if ($format == 	CL_MESSAGE_FORMAT_ADMIN) {
			$links = "";
			
			if ($this->status != CL_STATUS_ACTIVE) {
				$url = "$script?op=approve&id=$this->id";
				$links = clAppendHtml($links, "[" . clGetLink($url, "approve") . "]", "\n");
			}
			if ($this->status != CL_STATUS_REJECTED) {
				$url = "$script?op=reject&id=$this->id";
				$links = clAppendHtml($links, "[" . clGetLink($url, "reject"). "]", "\n");
			}
			
			$url = "$script?op=delete&id=$this->id";
			// $html = clAppendHtml($html, "[" . clGetLink($url, "delete"). "]", "\n");

			$html .= clGetTableCellHtml($links);
		}
		
		$html .= clGetTableCellHtml(clGetDisplayDateTime($this->postdate, 0, "Y-m-d", "H:i:s"));	
		$html .= clGetTableCellHtml($this->question);
		$html .= clGetTableCellHtml(clGetDisplayText($this->message));

		return $html;
	}

	// TODO: test this method
	// ====================================================================================
	function GetWhereClause($startindex = 0, $endindex = -1, $starttime = 0, $endtime = 0) {
		
		$where = "WHERE (message.id >= $startindex) ";
		if ($endindex > -1) {
			$where .= " AND (message.id <= $endindex) ";
		}
		
		if (($this->mask & CL_MESSAGE_ATTR_STATUS) && ($this->status != CL_STATUS_ANY)) {
			$where .= " AND (message.status = $this->status) ";
		}
		
		if ($this->mask & CL_MESSAGE_ATTR_QUESTION) {
			$where .= " AND (message.question = $this->question) ";
		}
		
		if ($starttime) {
			$where .= " AND (timestamp - ". clMakeSQLInt($starttime) . ") >= 0) ";
		}
		if ($endtime) {
			$where .= " AND (timestamp - ". clMakeSQLInt($endtime) . ") <= 0) ";
		}

		return $where;
	}

	// ====================================================================================
	function InsertData()
	{	
		// add message to database
		$sql  = "INSERT INTO message (message,status,question,red,green,blue,postdate,timestamp) VALUES (";
		
		$sql .= clGetInsertStrSQL($this->message, false);
		$sql .= clGetInsertIntSQL($this->status);
		$sql .= clGetInsertIntSQL($this->question);
		$sql .= clGetInsertIntSQL($this->red);
		$sql .= clGetInsertIntSQL($this->green);
		$sql .= clGetInsertIntSQL($this->blue);

		/*
		if ($this->mask & CL_MESSAGE_ATTR_POSTDATE) {
			$sql .= clGetInsertStrSQL($this->postdate);
			$sql .= ")";
		}
		else if ($this->mask & CL_MESSAGE_ATTR_TIMESTAMP) {
			$sql .= ", FROM_UNIXTIME(" . intval($this->timestamp) . ")";
			$sql .= ")";
		}
		else {
			$sql .= ", NOW())";
		}*/
		$sql .= ", NOW()";
		$sql .= ", UNIX_TIMESTAMP())";
		
		$result = mysql_query($sql);
		if (! $result)
			return CL_SQLERROR;
			
		if (mysql_affected_rows() == 0)
			return CL_SQLERROR;
			
		// set the messageid for the row (use the record ID from the insert if this is
		// a brand new message)
		if (mysql_affected_rows() > 0)
		{
			$id = mysql_insert_id();
			if ($id > 0)
			{
				if ($this->id == 0)
					$this->id = $id;
			}
		}	
		
		return CL_SUCCESS;
	}

	// ====================================================================================
	// updates an existing message in the database
	function UpdateData()
	{		
		// update row of message in database
		$sql  = "UPDATE message SET ";
		$sql .= clGetUpdateStrSQL("message", $this->message, false);
		$sql .= ", postdate=NOW()";
		$sql .= " WHERE (id='" . $this->id . "')";
		
		$result = mysql_query($sql);
		if (! $result)
			return CL_SQLERROR;
			
		if (mysql_affected_rows() == 0)
			return CL_NOTFOUND;
			
		return CL_SUCCESS;
	}

	// ====================================================================================
	function DeleteData($startindex = 0, $endindex = 0, $starttime = 0, $endtime = 0)
	{
		// delete message(s) matching criteria from database
		if (($this->mask & CL_MESSAGE_ATTR_ID) && $this->id) {
			$where = " WHERE (id='" . $this->id . "')";
		}
		else {
			$where = $this->GetWhereClause($startindex, $endindex, $starttime, $endtime);
		}
		$sql  = "DELETE FROM message $where";
		
		clPrintDebug($sql);
		// $result = mysql_query($sql);
		if (! $result)
			return CL_SQLERROR;
		return CL_SUCCESS;
	}

	// ====================================================================================
	function SetStatus($status = CL_STATUS_ACTIVE, $startindex = 0, $endindex = 0, $starttime = 0, $endtime = 0)
	{
		// sets status of messages matching criteria.  If the message ID is set, only this
		// message's status is changed
		if (($this->mask & CL_MESSAGE_ATTR_ID) && $this->id) {
			$where = " WHERE (id='" . $this->id . "')";
		}
		else {
			$where = $this->GetWhereClause($startindex, $endindex, $starttime, $endtime);
		}
		$sql  = "UPDATE message SET status=$status $where";
		
		// clPrintDebug($sql);
		$result = mysql_query($sql);
		if (! $result)
			return CL_SQLERROR;
		return CL_SUCCESS;
	}
	
	
} // end class clMessage


// ------------------------------------------------------------------------------------
// clQuestion Class
// ------------------------------------------------------------------------------------
class clQuestion {
	// these correspond directly to database fields
	var $id;
	var $question;
	
	// ====================================================================================
	// clQuestion: constructor
	function clQuestion($id = 0)
	{
		$this->id     = $id;
		$this->question = "";
	}

	// ====================================================================================
	// loads the question from the database
	function LoadFromDB()
	{
		// load message from database
		$sql = "SELECT * from question where (id = '$this->id')";
		
		$result = clDbQuery($sql);
		if (! $result)
			return FALSE;
	
		$row = mysql_fetch_array($result);
		if ($row)
		{
			$this->LoadFromRow($row);			
			mysql_free_result($result);
		
			return TRUE;
		}
		
		mysql_free_result($result);
		return FALSE;
	}
			
	// ====================================================================================
	// loads question from query results
	function LoadFromRow($row)
	{
		if ($row)
		{
			$this->id = $row["id"];
			$this->question = $row["question"];
		}
	}

	// ====================================================================================
	// loads message from POSTed data (works for GET too)
	function LoadFromPost()
	{
		if ($this->id == 0)
			$this->id = clGetParamInt("id");
			
		$this->question = clGetParamStr("q");
	}
	

	// ====================================================================================
	function InsertData()
	{	
		// add message to database
		$sql  = "INSERT INTO questions (question) VALUES (";
		
		$sql .= clGetInsertStrSQL($this->question, false);
		$sql .= ")";
		
		$result = mysql_query($sql);
		if (! $result)
			return CL_SQLERROR;
			
		if (mysql_affected_rows() == 0)
			return CL_SQLERROR;
			
		// set the id for the row (use the record ID from the insert if this is
		// a brand new question)
		if (mysql_affected_rows() > 0)
		{
			$id = mysql_insert_id();
			if ($id > 0)
			{
				if ($this->id == 0)
					$this->id = $id;
			}
		}	
		
		return CL_SUCCESS;
	}

	// ====================================================================================
	function DeleteData()
	{
		// delete question from database
		$sql  = "DELETE FROM questions WHERE id=$this->id";
		
		// clPrintDebug($sql);
		$result = mysql_query($sql);
		if (! $result)
			return CL_SQLERROR;
		return CL_SUCCESS;
	}

	
} // end class clQuestion

?>