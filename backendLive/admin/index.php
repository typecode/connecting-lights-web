<?php

// admin/index.php - connecting light message data script
// Created 7.13.12 by Derek Chung for Connecting Light
// Copyright (c) 2012 Tactile Pictures, Connecting Light

include_once('../local.php');

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
$pagecontent = "";
$head = "";
$showlist = 0;

// get HTML based on requested operation
switch ($op)
{
case "addq":
	// save new question to database
	$pagename = "Add Question";
	$pagetitle = $pagename;
	$q = new clQuestion();
	$q->LoadFromPost();
	if ($q->question) {
		$result = $q->InsertData();
		$pagecontent = ($result == CL_SUCCESS ? clGetDisplayText($q->question) : clGetMySqlError());
	}
	break;
case "delq":
	// delete question from database
	$pagename = "Delete Question";
	$pagetitle = $pagename;
	if ($id) {
		$q = new clQuestion($id);
		if (CL_SUCCESS == $q->DeleteData()) {
			$pagecontent = clGetParagraph("Question deleted");
		}
	}
	break;
case "approve":
case "reject":
	// approve a message (sets its status)
	// use param status=0 to set it back to inactive, or use op=delete instead to just get rid of it
	$startindex = clGetParamInt("since", 0);
	$endindex = clGetParamInt("last", -1);
	$starttime = clGetParamInt("start");
	$endtime = clGetParamInt("end");
	$status = clGetParamInt("status", ($op == "reject" ? CL_STATUS_REJECTED : CL_STATUS_ACTIVE));
	
	if ($id) {
		$props = clGetMessage($id); 
		$props->mask = CL_MESSAGE_ATTR_ID;
	}
	else {
		$props = new clMessage();
	}
	if (CL_SUCCESS == $props->SetStatus($status, $startindex, $endindex, $starttime, $endtime)) {
		$showlist = 1;
	}
	break;
case "delete":
	// request deleting message(s)
	$msg = new clMessage();
	$pagetitle = "$sitename: Delete Messages";
	$startindex = clGetParamInt("since", 0);
	$endindex = clGetParamInt("last", -1);
	$starttime = clGetParamInt("start");
	$endtime = clGetParamInt("end");
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
	break;
default:
case "list":
	$showlist = 1;
	break;
}

if ($showlist) {
	$title = "Messages";
	$pagename = "Messages";
	$timeframe = 0;
	$format = CL_MESSAGE_FORMAT_ADMIN;
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