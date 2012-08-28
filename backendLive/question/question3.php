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
<link rel="stylesheet" href="css/qstyle.css" />
<script language="javascript" type="text/javascript">
function limitText(limitField, limitCount, limitNum) {
	if (limitField.value.length > limitNum) {
		limitField.value = limitField.value.substring(0, limitNum);
	} else {
		limitCount.value = limitNum - limitField.value.length;
	}
}
</script>
</head>

<body>
<iframe width='80%' height='300px' frameBorder='0' src='http://a.tiles.mapbox.com/v3/chriswoebken.map-l1msszc5.html#10.00/54.9429/-2.3236'></iframe>
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
					print "<input type=hidden name=\"q\" value=\"$q->id\">\n";
					print clGetDiv(clGetDisplayText($q->question));
				}
			 ?>
			<div class="box shadow clearfix radius">
				<textarea name="m" id="m" onKeyDown="limitText(this.form.m,this.form.countdown,100);" 
onKeyUp="limitText(this.form.m,this.form.countdown,100);"></textarea>
<input readonly type="text" name="countdown" class="countdownclass" value="100"><br></font>				
				<input type="submit" value="submit"></input>			
			</div>
			</form>
			<form method="post" action="question.php"><?php
				if ($q) {
					print "<input type=hidden name=\"exclude\" value=\"$q->id\">\n";
				} ?>
				<!-- <input type="submit" value=""></input> -->		
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