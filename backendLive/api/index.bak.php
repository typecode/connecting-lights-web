<?php

// api/index.php - connecting light message data script
// Created 5.11.12 by Derek Chung for Connecting Light
// Copyright (c) 2012 Tactile Pictures, Connecting Light

// include_once('../../../../php/light/config.php');
include_once('/var/local/php/config.php');

// global initialization
clInit();

// connect to database
$cldb = clDbConnect()
	or die("Cannot connect to database");
	
// initialize global context
$rootpath = "../";
$op     = clGetParam("op");
$script = clGetScript();
$id     = clGetParamInt("id");

// initialize page parameters
$sitename = CL_SITENAME;
$parenturl = "index.php";
$pagecontent = "";
$head = "";

// get HTML based on requested operation
switch ($op)
{
case "new":
	// display empty form
	$pagename = "New Message";
	$pagetitle = $pagename;	
	
	$msg = new clMessage();
	$pagecontent  = clGetPageHeading($pagename);
	$pagecontent .= $msg->GetForm($script);
	break;
case "save":
	// save new record to database
	$pagename = "Save Message";
	$pagetitle = $pagename;
	$msg = new clMessage();
	$msg->LoadFromPost();
	if ($msg->message) {
		$result = $msg->InsertData();
		$pagecontent = ($result == CL_SUCCESS ? clGetDisplayText($msg->message) : clGetMySqlError());
	}
	break;
case "addq":
	// save new question to database
	if (clGetParamStr("pw") == CL_ADMINPASS) {
		$pagename = "Add Question";
		$pagetitle = $pagename;
		$q = new clQuestion();
		$q->LoadFromPost();
		if ($q->question) {
			$result = $q->InsertData();
			$pagecontent = ($result == CL_SUCCESS ? clGetDisplayText($q->question) : clGetMySqlError());
		}
	}
	break;
case "delq":
	// delete question from database
	if (clGetParamStr("pw") == CL_ADMINPASS) {
		$pagename = "Delete Question";
		$pagetitle = $pagename;
		if ($id) {
			$q = new clQuestion($id);
			if (CL_SUCCESS == $q->DeleteData()) {
				$pagecontent = clGetParagraph("Question deleted");
			}
		}
	}
	break;
case "approve":
	// approve a message (sets its status to active) - password required
	// use param status=0 to set it back to inactive, or use op=delete instead to just get rid of it
	if (clGetParamStr("pw") == CL_ADMINPASS) {
		$startindex = clGetParamInt("since", 0);
		$endindex = clGetParamInt("last", -1);
		$starttime = clGetParamStr("start");
		$endtime = clGetParamStr("end");
		$status = clGetParamInt("status", CL_STATUS_ACTIVE);
		
		if ($id) {
			$props = clGetMessage($id); 
			$props->mask = CL_MESSAGE_ATTR_ID;
		}
		else {
			$props = new clMessage();
		}
		$pagecontent = $props->SetStatus($status, $startindex, $endindex, $starttime, $endtime);
	}
	break;
case "delete":
	// request deleting a message - password required
	if (clGetParamStr("pw") == CL_ADMINPASS) {
		$msg = new clMessage();
		$pagetitle = "$sitename: Delete Messages";
		$startindex = clGetParamInt("since", 0);
		$endindex = clGetParamInt("last", -1);
		$starttime = clGetParamStr("start");
		$endtime = clGetParamStr("end");
		if ($id) {
			$props = clGetMessage($id); 
			$props->mask = CL_MESSAGE_ATTR_ID;
		}
		else {
			$props = new clMessage();
		}
		$props->question = clGetParamInt("q");
		if ($props->question > 0) {
			$props->mask |= CL_MESSAGE_ATTR_QUESTION;
		}
		$pagecontent = $props->DeleteData($startindex, $endindex, $starttime, $endtime);
	}
	break;
case "json":
	// return JSON data only, no page
	$props = new clMessage();
	$props->mask = 0;
	
	$startindex = clGetParamInt("since", 0);
	$endindex = clGetParamInt("last", -1);
	$starttime = clGetParamStr("start");
	$endtime = clGetParamStr("end");
	$question = clGetParamStr("q");
	$maxitems = clGetParamInt("n", 0);
	$random = clGetParamInt("rnd", 0);
	$status = clGetParamInt("status", CL_STATUS_ANY);
	
	$json = $props->getJSON($startindex, $endindex, $starttime, $endtime, $question, $status, $maxitems, $random);
	clPrintJSON($json);
	exit;
case "time":
	print clGetSQLDateTime();
	exit;
case "timestamp":
	print time();
	exit;
default:
case "list":
	$title = "Messages";
	$pagename = "Messages";
	$timeframe = 0;
	$format = (clGetParamStr("pw") == CL_ADMINPASS ? CL_MESSAGE_FORMAT_ADMIN : CL_MESSAGE_FORMAT_LIST);
	$sortby = clGetParamStr("sortby", "postdate");
	$sortdir = clGetParamInt("sortdir", 1);
	
	// get parameters to filter results
	$props = new clMessage();
	$props->mask = 0;
	
	$pagetitle = "$sitename: $title";
	$pagecontent .= clGetPageHeading($title);
	$maxitems = clGetParamInt("maxitems");
		
	$startindex = clGetParamInt("start", 0);
	
	$pagecontent .= $props->GetList($script, "view", $timeframe, $format, $maxitems, $sortby, $sortdir, $startindex);

	break;
}

clDbClose($cldb);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/page.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php print $pagetitle; ?></title>
<!-- InstanceEndEditable -->
<link rel="stylesheet" href="../css/style.css" />
<!-- InstanceBeginEditable name="head" --><?php print $head; ?>
<!-- InstanceEndEditable -->
</head>

<body>
<div id="container">
	
	<div id="content">
		<!--  HEADER -->
		<div id="content-header">
		
			<!-- NAVIGATION 
			<ul id="content-header-navigation">
				
			</ul>
			
			<div id="content-header-user">
			</div> -->
		</div>
		
		<!-- RIGHT SIDEBAR 
		<div id="content-sidebar"> -->
			<!-- InstanceBeginEditable name="sidebar" --><!-- InstanceEndEditable -->
		<!-- RIGHT SIDEBAR </div> -->
	
			<!-- main content area -->
		<div id="content-main">
			<!-- InstanceBeginEditable name="content" -->
			<?php clPrintHtml($pagecontent); ?>
			<!-- InstanceEndEditable -->
		</div>
		
		<!-- BOTTOM FOOTER -->
		<div id="footer">			
		</div>

	</div>
</div>
</body>
<!-- InstanceEnd --></html>