<?php

// question.php - connecting light test page for asking questions
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
$op     = clGetParam("op");
$script = clGetScript();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php print $pagetitle; ?></title>
<link rel="stylesheet" href="css/qstyle_john.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script src="js/jquery.caret.min.js"></script>
<script language="javascript" type="text/javascript" src="js/q_john.js">
</script>

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
			<form name="messageform" id="messageform"  method="post" action="addAnswer.php" >
			<?php 
				// get random question
				$exclude = clGetParamInt("exclude", -1);
				$q = clGetRandomQuestion($exclude);
				
				if ($q) {
					print "<input type=hidden name=\"q\" value=\"$q->id\">";
					$promptsize = strlen(clGetDisplayText($q->question)) + 1; //one for space
					print "<input id=\"q-length\" type=hidden name=\"q-length\" value=\"".$promptsize."\">";
				}
			 ?>
			<div id="prompt-container" class="box shadow clearfix radius">
				<div id="prompt-float"><?php print clGetDisplayText($q->question); ?></div>
				<textarea id="m" name="m" onKeyDown="limitText(this.form.m,this.form.countdown,100);" 
onKeyUp="limitText(this.form.m,this.form.countdown,100);"><?php print clGetDisplayText($q->question); ?></textarea>
				<input readonly id="countdown" type="text" name="countdown" class="countdownclass" value="100"><br></font>				
				<input type="submit" value="submit"></input>			
			</div>
			</form>
		</div>
		
		<!-- BOTTOM FOOTER -->
		<div id="footer">			
		</div>

	</div>
</div>
</body>
</html>
<?php
	clDbClose($cldb);
?>