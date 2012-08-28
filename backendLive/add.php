<?php

// api/add.php - connecting light message data script
// Created 5.11.12 by Derek Chung for Connecting Light
// Copyright (c) 2012 Tactile Pictures, Connecting Light

include_once('../local.php');

// global initialization
clInit();

// connect to database
$cldb = clDbConnect()
	or die("Cannot connect to database");
	
// initialize global context
$rootpath = "../";
$script = clGetScript();
$id     = clGetParamInt("id");

// initialize page parameters
$sitename = CL_SITENAME;
$pagecontent = "";
$head = "";

// save new record to database
$pagename = "New Message";
$pagetitle = $pagename;
$msg = new clMessage();
$msg->LoadFromPost();
if ($msg->message) {
	$result = $msg->InsertData();
	$pagecontent = ($result == CL_SUCCESS ? clGetDisplayText($msg->message) : clGetMySqlError());
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