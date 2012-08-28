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
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/farbtastic.js"></script>
<link rel="stylesheet" href="farbtastic.css" type="text/css" />
<style type="text/css" media="screen">
   .colorwell {
     border: 2px solid #fff;
     width: 15em;
     text-align: center;
     cursor: pointer;
   }
   body .colorwell-selected {
     border: 2px solid #000;
     font-weight: bold;
     background-color: #ccc;
   }
</style>
<script type="text/javascript" charset="utf-8">
  $(document).ready(function() {
    $('#demo').hide();
    var f = $.farbtastic('#picker');
    var p = $('#picker').css('opacity', 1);
    var selected;
    $('.colorwell')
      .each(function () { f.linkTo(this); $(this).css('opacity', 0.75); })
      .focus(function() {
        if (selected) {
          $(selected).css('opacity', 0.75).removeClass('colorwell-selected');
        }
        f.linkTo(this);
        p.css('opacity', 1);
        $(selected = this).css('opacity', 1).addClass('colorwell-selected');
      });
  });
</script>
</head>
<body>
<div id="container">
	
	<div id="content">
	
	</div>
</div>
</body>
<!-- InstanceEnd --></html>