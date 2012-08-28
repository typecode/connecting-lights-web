<?php
 
	define("CL_DEBUG", FALSE);
	define("CL_PRIVATE", FALSE);
	define("CL_MAJOR_VERSION", 1);
	define("CL_MINOR_VERSION", 0);
	define("CL_RELEASE_VERSION", 0);
	
	define("CL_SITENAME", "Connecting Light");
	define("CL_MESSAGE_MAXCHARS", 100);
	
	if (CL_DEBUG)
	{
		// test server
		define("CL_DOMAIN", "tactilepix.com");
		
		define("CL_BASE_PATH", "/dev/");
		define("CL_BASE_URL", "http://www.tactilepix.com/projects/light/");
		define("CL_SERVER_ROOT", "/home/39704/domains/tactilepix.com/html/projects/light/");
	
		define("CL_UPLOAD_DIRECTORY", "docs/");
		define("CL_DBHOST", "internal-db.s39704.gridserver.com");
		define("CL_DBNAME", "db39704_light");
		define("CL_DBLOGIN", "db39704");
		define("CL_DBPASS", "EzNZN7BZ");
	}
	else
	{
		// live server
		define("CL_DOMAIN", "connectinglight.org");
		define("CL_BASE_PATH", "/");
		define("CL_BASE_URL", "http://www.connectinglight.org/");
		define("CL_SERVER_ROOT", "/var/www/");
	
		define("CL_UPLOAD_DIRECTORY", "docs/");
		define("CL_DBHOST", "localhost");
		define("CL_DBNAME", "light");
		define("CL_DBLOGIN", "lightuser");
		define("CL_DBPASS", "gQ5ty707");
	}
	define("CL_ADMINPASS", "chinat0wn");
	
	define("CL_ADMIN_PAGE", "admin/index.php");
	define("CL_API_PAGE", "api/index.php");
	define("CL_HOME_PAGE", "index.php");
	
	// special ID for "no owner"
	define("CL_OWNER_NONE", 0);
	
	// error codes
	define("CL_SUCCESS", 0);
	define("CL_ERROR", 1);
	define("CL_SQLERROR", 2);
	define("CL_NOTFOUND", 3);
	define("CL_MISSINGDATA", 4);
	define("CL_DUPLICATEDATA", 5);
	define("CL_NOTAUTHORIZED", 6);
	define("CL_INVALIDINPUT", 7);
	
	// project includes
	include_once('message.php');
	include_once('util.php');
?>