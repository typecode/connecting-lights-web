<?php

// api/index.php - connecting light message data script
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
$id     = clGetParamInt("id");

// initialize page parameters
$sitename = CL_SITENAME;
$pagecontent = "";
$head = "";

// return JSON
// return JSON data only, no page
$props = new clMessage();
$props->mask = 0;

$startindex = clGetParamInt("since", 0);
$endindex = clGetParamInt("last", -1);
$starttime = clGetParamInt("start");
$endtime = clGetParamInt("end");
$question = clGetParamStr("q");
$maxitems = clGetParamInt("n", 0);
$random = clGetParamInt("rnd", 0);
$status = clGetParamInt("status", CL_STATUS_ANY);

$json = $props->getJSON($startindex, $endindex, $starttime, $endtime, $question, $status, $maxitems, $random);
clPrintJSON($json);

clDbClose($cldb);

?>
