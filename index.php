<?php
		/*PHP manual:
			"Many proxies and clients can be forced to disable caching with" ...*/
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

	header("Content-type: text/html; charset=utf-8");
	header("Accept-Charset: utf-8");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--the php header statement at top of file is really what forces it to read as UTF-8-->

<?php
	//server-side scripts incl. connection to database
require_once('appConfigOCLCapi.php');
require_once('fileFunctions.php');
require_once('emailFunctions.php');
	setEmailAddress();
require_once('jquery-1.7.1.min.js');
require_once('Holdings.js');

//phpinfo();
?>

<link href="http://staff.crl.edu/crl.css" rel="stylesheet" type="text/css" />
<link href="css/index.css" rel="stylesheet" type="text/css" />
<link href="images/favicon.ico" rel="shortcut icon" type="image/x-icon" />


<title>OCLC Holdings application: CRL Intranet</title>
</head>

<body>

<?php

include("header.php");
include("inputTextDataForm.php"); //form to input OCLC numbers
include("inputFileDataForm.php");	//form to upload file of OCLC numbers
//echo $strAppDebug;

if ($reqAction == "queryAPI"){
	switch ($reqDatasource) {
	case "text":
		include("queryAPIfromText.php"); 	//check OCLC #s from text input
		break;
	case "fileupload":
		// queryAPIfromFile.php includes fileUploadHandler.php
		include("queryAPIfromFile.php");  //check OCLC #s from file
		break;
	case "database":
		//include("navigateDBrecords.php");
		include("queryAPIfromDB.php"); //check OCLC #s from a DB table (specify the table in this file)
		break;
	}//end switch on reqDatasource
}//end if queryAPI

else if ($reqAction == "createFile"){ // can save API data to file for any reqDatasource
	include("createFile.php"); //save the queried data
}//end else: save queried data

else if ($reqAction == "queryDB"){ //get saved info from the database, not from live OCLC
	include("queryDBform.php");
	if (isset($_REQUEST["dbQueryTrigger"])) {
		include("queryDBprocess.php"); //includes toggleable updateDBform.php
	}  /* else if (isset($_REQUEST["dbUpdateTrigger"])) { //NOV. 2012: NO LONGER TRYING TO SUPPORT
		include("updateDBprocess.php"); //update saved info in database with submitted values
	}//end if dbUpdateTrigger      */
}//end else: queryDB



include("explain.php");
include("supportLinks.php");
include("devNotes.php");
include("footerCRL.html");
include("dbCleanup.php");
?>
</body>
</html>
