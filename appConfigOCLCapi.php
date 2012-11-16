<?php

/**********************************************************
This file contains variables + functions for use in the entire application
*/

/**************************************************************************

AJE 12/29/2011
for more on the database, see
	/docs/mysql/DATABASE_SPECS.txt
	/docs/mysql/DATABASE_CREATION.txt

mysql> use icondata;
mysql> describe issues_test;
+----------------+------------------+------+-----+---------+----------------+
| Field          | Type             | Null | Key | Default | Extra          |
+----------------+------------------+------+-----+---------+----------------+
| record_id      | int(10) unsigned | NO   | PRI | NULL    | auto_increment |
| pub_id         | varchar(15)      | YES  |     | NULL    |                |
| pub_date       | date             | YES  |     | NULL    |                |
| repository     | varchar(5)       | YES  |     | NULL    |                |
| phys_condition | varchar(255)     | YES  |     | NULL    |                |
| format         | varchar(255)     | YES  |     | NULL    |                |
| provenance     | varchar(255)     | YES  |     | NULL    |                |
| update_date    | date             | YES  |     | NULL    |                |
| notes          | varchar(255)     | YES  |     | NULL    |                |
+----------------+------------------+------+-----+---------+----------------+
9 rows in set (0.00 sec)


**************************************************************************/

# AJE: original setup file from book's companion site, Script 7.2 - mysqli_connect.php
// This file contains the database access information.
// This file also establishes a connection to MySQL and selects the database.

// Set the database access information as constants.
define('DB_USER', 'webuser');
define('DB_PASSWORD', 'w3bus3r');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'OCLCapi');

// Make the connection.
$databaseConnection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die ('<h3 class="alert">Could not connect to MySQL: <br /><span class="highlightCode">' . mysqli_error($databaseConnection) . "</span>. </h3>" );

// Select the database.
mysqli_select_db($databaseConnection, DB_NAME) or die ('<h3>Could not select the database: <br /><em>' . mysqli_error($databaseConnection) . "</em></h3>" );

//echo "<p>this is appConfigOCLCapi.php</p>";

	/**********************************************************
		VARS FOR FILE SYSTEM */

//some leftover globals code
$CLImode = false; //command-line mode
if ( isset($_SERVER['argc']) && $_SERVER['argc'] >=1 ) {
  $CLImode = true;
}
//echo "CLImode == '" . $CLImode . "'";

$devNotes 	= "";
$separator 	= "\n====\n";


$numStartQuery		= -1;
$numQueryLimit		= -1;


if($CLImode){ //command-line mode has no use for these:
	$thisServerIP = "junk";
	$thisUrl 		= "garbage";
} else { // running as server script
	$thisServerIP = $_SERVER['SERVER_ADDR'];
	$thisUrl 		= $_SERVER['PHP_SELF']; //thisUrl is like '/index.php' or similar
}

	$parts 		= explode("/", $thisUrl);
	$partsSize 	= count($parts); //returns array length
	$thisFile	= $parts[ $partsSize - 1];

	$fileuploadpath = "/datasources/uploads/";	//relative to web root

	/* end VARS FOR FILE SYSTEM
	**********************************************************/

	//not using $bshowDebug yet
$bShowDebug = 0; // value of 0 = don't show application debugging; 1 = do show it
$strAppDebug = "<h3>thisServerIP = '" . $thisServerIP . "'; thisURL = '" . $thisUrl . "'; thisFile = '" . $thisFile . "'</h3>";

	/**********************************************************
		VARS FOR DATABASE + PAGE LINKING*/
		/* database table chosen from form object or assigned in code. */
	$tableChoice = "OCLC_HOLDINGS";

	$recordStep = 100; //how many to get for each page
	//echo "<h2>recordStep=" . $recordStep . "</h3";
	if (isset($_REQUEST["beginID"])) 	$beginID = $_REQUEST["beginID"];
	else 	$beginID 	= 1;
	if (isset($_REQUEST["endID"])) 		$endID = $_REQUEST["endID"];
	else	$endID		= $beginID + $recordStep;
	$numRecs    = 0; // number of records in the set
	$recNum			= -1; //which record this is, in looping

/*
	//$strSQL = "SELECT * FROM " . $tableChoice . " ";  // string holding SQL statement
	$strSQL = "SELECT * FROM " . $tableChoice . " ";  // string holding SQL statement
		$strSQL .= " WHERE " . $tableChoice . ".id >= " . $beginID;
		$strSQL .= " AND " . $tableChoice . ".id <= " . $endID;
		$strSQL .= " ORDER BY " . $tableChoice . ".id;";
*/

/*
$strSQL = 'SELECT ' . $tableChoice . '.OCLC_NUMBER ';
$strSQL .= 'FROM ' . $tableChoice . ' ';
$strSQL .= 'WHERE ' . $tableChoice . '.OCLC_NUMBER NOT IN ( SELECT OCLC_NUMBER FROM OCLC_HOLDINGS );';
*/

//hard coded sample for testing
$strSQL = 'SELECT * ';
$strSQL .= 'FROM ' . $tableChoice . ' ';
$strSQL .= 'WHERE OCLC_NUMBER IN ( 807527, 809393, 812841, 813488, 813697, 816323, 816337, 817186, 817225, 817249, 818129, 819038, 819064, 819101, 819182, 824083, 825130 );';

//echo $strSQL;

	$prevPageLink = "";	// paging through the records
	$nextPageLink = "";

	$strSQLDescribe 	= "DESCRIBE $tableChoice";
	$result = "";
	$selectResult = ""; //names for various result operations, hold resources or booleans returned by mysqli_query
	$updateResult = "";
	$confirmResult = "";
	$describeResult = "";

	$strAppDebug .= 	"<h4>in " . DB_NAME . ", tableChoice = '" . $tableChoice . "'<br />strSQL = '$strSQL'</h4>";
	/* end VARS FOR DATABASE + PAGE LINKING
	**********************************************************/


	/**********************************************************
		- QUERY STRING OR FORM VALUES
	**********************************************************/
	if (isset($_REQUEST["action"])) { $reqAction 	= $_REQUEST["action"]; }
	else { $reqAction 	= "actionDummy"; }

		// $_REQUEST["datasource"]) could have values: 'text', 'fileupload', 'database'; default to text
	if (isset($_REQUEST["datasource"])) { $reqDatasource = $_REQUEST["datasource"]; }
	else { $reqDatasource = "text"; }

	if (isset($_REQUEST["reportVersion"])) { $reqReportVersion = $_REQUEST["reportVersion"]; }
	else { $reqReportVersion = "reportVersionDummy"; }

	if (isset( $_FILES["filenameBox"]["name"]) ) { 	 $base = basename( $_FILES["filenameBox"]["name"]); }
	else if (isset($_REQUEST["filenameBox"])) { 	$base 	= $_REQUEST["filenameBox"]; }
	else { $base 	= "defaultFilename.csv"; }
	$fileExtension = strpos($base, '.', 0);
	if ($fileExtension) {
		$reqFilename 	= substr($base, 0, $fileExtension);
	} else $reqFilename = $base;


		$strAppDebug .= 	"<h4>reqAction='" . $reqAction . "', reqDatasource='" . $reqDatasource . "'; ', reqReportVersion='" . $reqReportVersion . "'; ";
		$strAppDebug .= "reqFilename='" . $reqFilename . "'</h4>";
if ($bShowDebug) echo $strAppDebug;

	/**********************************************************
		- COOKIE VALUES
	**********************************************************/
	$cookieEnableMessage = 	"<h3 class='alert'>This application requires cookies to be enabled.<br />";
	$cookieEnableMessage .= "Please check your browser settings and resubmit the data.</h3>";
	if (isset($_COOKIE["adminUser"]["username"])){  $adminUserNameCookie = $_COOKIE["adminUser"]["username"]; }
	else { $adminUserNameCookie = "adminUserNameCookieDummy"; }
	if (isset($_COOKIE["adminUser"]["userPassword"])) { $adminUserPwdCookie   = $_COOKIE["adminUser"]["userPassword"]; }
	else { $adminUserPwdCookie   = "adminUserPwdCookieDummy"; }

		$strAppDebug .= "<h4>adminUserNameCookie='" . $adminUserNameCookie . "', ";
		$strAppDebug .= "reqReportVersion='" . $reqReportVersion . "'</h4>";

	$strBGcolor 	= "#adbd90"; //darker
	$strBGcolorAlt 	= "#e9e6d3";	//lighter

	// set these to use in SQL-generating code for the reports
	$minOCLC_NUMBER = 5;
	$maxOCLC_NUMBER = 814278076;
	$fromOCLC_NUMBER 	= $minOCLC_NUMBER;
	$toOCLC_NUMBER 		= $maxOCLC_NUMBER;
	$minnumHolders = 1;
		/*
			http://oclc.org/developer/groups/worldcat-search-api/worldcat-search-api-enhancements-and-bug-fixes
			- can get up to 100 now [seen 12-Sep-2011

			- 2012-Apr-11 tested with $maximumLibraries = 200; we still only get 100
			http://oclc.org/developer/documentation/worldcat-search-api/using-api
				Maximum number of records/libraries –
				For any query the maximum number of records or library locations that can be requested is 100.
				It is possible to page through the results
					by sending another query with the next start position
					and request the next set of records or library locations up to another 100.
				If not specified, results default to 10 records per request.

				Total maximum number of records for a single specific query –
					For any specific query, it is possible to page through all the results
					up to the 10,000th record. After that point, the system will appear to continue to page, but the next result will be a repeat of an earlier record.

		*/
	$maximumLibraries = 100;
	$maximumHolders 	= 300; //after this point we don't care: DEVNOTE WE ARE NOT USING IT
		//spit out maximumLibraries into a javascript var that can be checked in Holdings.js --> libraryLocationHandler
	$maximumLibrariesscript = '<script language="javascript" type="text/javascript">';
	$maximumLibrariesscript .= 'maximumLibraries = ' . $maximumLibraries . ';';
	//$maximumLibrariesscript .= 'alert(maximumLibraries = "' . $maximumLibraries . '");'; //works
	$maximumLibrariesscript .= '</script>';
	echo $maximumLibrariesscript;

	$minDBid	= 1;
	$maxDBid = 51730;

	$strReportTitle = "";

	/*
			//THE FIELD SPECS ARE OLD: FOR OCLC API APPLICATION, MONTHS OLD AS OF 12/29/2011
		strings to hold the data from database:
			fields are (with MS Access data types):
			id (autonumber)
			OCLC_NUMBER (number)
			OCLCalternate (number)
			LCCN (text) //not returned by this API
			TITLE (memo)
			CRLholds (memo)
			numHolders (number)
			allNames (memo)
			allCodes (memo)
	*/
	$row 					= "";
	$datasource		= "";
	$strID 				= 0;
	$strOCLC_NUMBER 		= 0;
	$strOCLCalternate		= 0;
	$strISSN 			= "";
	//$strLCCN 			= "";
	$strTITLE 		= "";
	$strTITLEsmall 	= ""; //truncations of underlying data from some fields, for better display
	$strCRLholds 		= "";
	$strNumHolders 	 	= "";
	$strAllNames 	= "";
	$strAllCodes 	= "";

	$numTruncate = 50;

		//sets of vars holding data from form submission, or database
	$formTitle 	= (isset($_REQUEST["title"])) ? $_REQUEST["title"] : "" ;
	//$formLCCN 	= (isset($_REQUEST["LCCN"])) ? $_REQUEST["LCCN"] : "" ;
	$fromOCLC 	= (isset($_REQUEST["fromOCLC_NUMBER"])) ? $_REQUEST["fromOCLC_NUMBER"] : "" ;
	$toOCLC 	= (isset($_REQUEST["toOCLC_NUMBER"])) ? $_REQUEST["toOCLC_NUMBER"] : "" ;
	$formDataErrorMessage = "<h3 class='alert'>There was a problem with the form data.</h3>";
	if (isset($_REQUEST['OCLC_NUMBERlist']))
		$strOCLC_NUMBERlist = $_REQUEST['OCLC_NUMBERlist'];
	else
		$strOCLC_NUMBERlist = "";

	/* vars to determine when to add an && in the SQL, and how to construct report captions
			default all to false, change each if any form data submitted in that field:
			see setBooleansForFields() in appConfigOCLCapi.php		*/
	$bTITLE = false;
	//$bLCCN = false;
	$bOCLC = false;
	$b_numHolders = false;
	$bSummarize = false;
	$bNoChangedFormData = false;
	if ( isset($_REQUEST['sortByField']) && ($_REQUEST['sortByField'] != "") ){
		$sortByField = $_REQUEST['sortByField'];
	} else {
		$sortByField = "title";
	}



		/**********************************************************
		VARS SPECIFIC to OCLC APIs: but note form-related vars are set above
			- see also function composeOCLCscriptURL()
		*/
		//shortcuts for calls to various APIs
	$OCLC_REQ_MARC_XML_BIB 	= "http://www.worldcat.org/webservices/catalog/content/";
	$OCLC_REQ_SRU 					= "http://www.worldcat.org/webservices/catalog/search/sru?query=";
	$OCLC_LIBRARY_LOCATION	= "http://www.worldcat.org/webservices/catalog/content/libraries/";
	$zipCode 								= 60637;
	$serviceLevel 					= "full";
	$libtype 								= ""; //  "" = all; 1 = academic; 2 = public; 3 = government; 4 = other.
	$startLibrary 	= (isset($_REQUEST["paging"])) ? $_REQUEST["paging"] : 1 ;

		// wskey code required for ea. OCLC API request: 7-Dec-10 from Kristy Schiro  [schirok@oclc.org]; Amy also has a different code
	$wskey = "RO0ajrCDg7Zwh4MXsz3VK5a9TyNw2Osxw4o1iPzx1plqp8o9Yf6L5tXCvLXTOnDJ7qCBGbYGCuwIxsPL";
		// logo required on every page that uses WorldCat Search API
	$OCLC_LOGO = '<a href="http://www.worldcat.org/" target="_blank">';
	$OCLC_LOGO .= '<img align="right" border="0" src="http://www.worldcat.org/images/wc_badge_80x15.gif?ai=Center_redsoxandy" width="80" height="15" alt="WorldCat lets people access the collections of libraries worldwide [WorldCat.org]" title="WorldCat lets people access the collections of libraries worldwide [WorldCat.org]" /></a>';

	/* end VARS SPECIFIC to OCLC APIs: see also function composeOCLCscriptURL()
	**********************************************************/


/***************************************************************************************
	BEGIN FUNCTIONS
***************************************************************************************/
function composeOCLCscriptURL($strOCLC_NUMBER, $requestedPage){
	global $OCLC_LIBRARY_LOCATION, $wskey, $zipCode, $serviceLevel, $libtype, $maximumLibraries;

		//http* vars like OCLC_LIBRARY_LOCATION + OCLCqueryString compose a request
	$OCLCqueryString = "?wskey=" . $wskey;
	$OCLCqueryString .= "&location=" . $zipCode;
	$OCLCqueryString .= "&serviceLevel=" . $serviceLevel;
	$OCLCqueryString .= "&libtype=" . $libtype;
	$OCLCqueryString .= "&startLibrary=" . $requestedPage; //use this to get holders beyond maximumLibraries
	$OCLCqueryString .= "&maximumLibraries=" . $maximumLibraries; //if skip maximumLibraries: only 10 results for ea. item
	$OCLCqueryString .= "&format=json";
	//$OCLCqueryString .= "&callback=libraryLocationHandler"; //not needed since using function cURLrequest

	/* 				http://oclc.org/developer/documentation/worldcat-search-api/library-locations
			Note: default option = include all library locations for any record that represents an ed. of item identified.
			This is sometimes referred to as a "FRBRized" holdings results.
			To see holdings associated with only a single record, include param frbrGrouping=off.
			However, 'off' option only works with an OCLC number. */
	//$OCLCqueryString .= "&frbrGrouping=off";

			//compose the URL (mostly set in global OCLC API vars section)
	$OCLCscriptURL = $OCLC_LIBRARY_LOCATION . $strOCLC_NUMBER;
	$OCLCscriptURL .= $OCLCqueryString;
	//if ($requestedPage > 1){
	//	echo '<h3 class="highlightCode">composeOCLCscriptURL(' . $strOCLC_NUMBER . ', ' . $requestedPage . ') will return <br/>. ' . $OCLCscriptURL . '</h3>';
	//}
	return $OCLCscriptURL;

}//end composeOCLCscriptURL




/***********************************************************************
	cURL wrapper function after
		http://blog.miloco.com/2008/11/having-php-problems-with-json-and-file_get_contents.html
*/
function cURLrequest($url) {
	$curlInfo  = '<p><a href="' . $url . '" class="last_date" target="_blank">cURLrequest link</a><br/>';
	$ch = curl_init();
	$timeout = 20; // set to zero for no timeout
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Ask cURL to return contents in var (not echoing to browser)
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$file_contents = curl_exec($ch);
	curl_close($ch);
	$curlInfo  .= 'cURLrequest has file_contents = ' . substr($file_contents, 0, 1000). '...</p>';
	//$curlInfo  .= 'cURLrequest has file_contents = ' . $file_contents . '...</p>';
	//echo $curlInfo;
	return $file_contents;
}//end cURLrequest




function extractJSONtitle($clientJSON){ //helper function for composeNewJSON, also called for debug in getJSONfromOCLC
	$bgn = strpos($clientJSON, '"title":"') + 9;
	$end = strpos($clientJSON, '."', $bgn) - $bgn;
	return substr($clientJSON, $bgn, $end);
}//end extractJSONtitle




function composeNewJSON($strOCLC_NUMBER, $clientJSON, $strDiagnostic, $bHasTitle){ //helper function for getJSONfromOCLC
	$strTitle = "";
	if($bHasTitle){
		$strTitle = extractJSONtitle($clientJSON);
	} else {
		$strTitle = $strDiagnostic;
	}

	$clientJSON = '{'; //open new JSON (we're replacing whole string); real OCLC JSON has more fields we don't use
		$clientJSON .= '"title":"' . $strTitle .  '","OCLCnumber":"' . $strOCLC_NUMBER .  '",';
		$clientJSON .= '"library":[{'; // library is array of institution objects, ea. institution is in {}
			$clientJSON .= '"institutionName":"' . $strDiagnostic . '",';
			$clientJSON .= '"oclcSymbol":"[---]",';
			$clientJSON .= '"opacUrl":"http://www.worldcat.org/wcpa/oclc/' . $strOCLC_NUMBER .  '"';
	$clientJSON .= '}]}'; //close library array element 0, the whole library array, and the new JSON

	return $clientJSON;
}//end composeNewJSON





function getJSONfromOCLC($strOCLC_NUMBER){
	//global $OCLC_LIBRARY_LOCATION, $OCLCqueryString;
	global $OCLCnums, $OCLClength, $maximumLibraries, $maximumHolders, $startLibrary;

	$strDebug  = '<span class="highlightCode">getJSONfromOCLC OCLC: "' . $strOCLC_NUMBER . '" ';
	$strOCLC_NUMBER = trim($strOCLC_NUMBER);

	$requestedPage 	= $startLibrary;
	$OCLCscriptURL = composeOCLCscriptURL($strOCLC_NUMBER, $requestedPage);

	$semicolon = strpos( $strOCLC_NUMBER, ";" );
	//$strDebug .= "; semicolon position (to strip out, is invalid data) =='" . $semicolon . "'\n";
	if($semicolon){ //sometimes invalid data
		$strOCLCarray 	= explode(";", $strOCLC_NUMBER);
		$strOCLC_NUMBER = $strOCLCarray[0];
		$strDebug .= "; new O# =='" . $strOCLC_NUMBER . "'";
	}//end if

		//spit out requested OCLC num into a javascript var that can be checked in Holdings.js --> libraryLocationHandler
	$OCLCrequested = '<script language="javascript" type="text/javascript">';
	$OCLCrequested .= 'OCLCrequested = "' . $strOCLC_NUMBER . '";';
	//$OCLCrequested .= 'alert(OCLCrequested = "' . $strOCLC_NUMBER . '");'; //works
	$OCLCrequested .= '</script>';
	echo $OCLCrequested;

	$strDebug  .= ' <a href="' . $OCLCscriptURL . '" target="_blank">link OCLCscriptURL</a></span>';
	//echo $strDebug; //display the URL we are using in the request SEE BELOW: JAVASCRIPT ALWAYS PUTS IT IN

	// SEE ./OCLCapi/invalidLabelError.txt for comments that were here

	/*
		2012-08-20:
		OCLC may return malformed JSON with string 'diagnostic' inside,
			but subsequent tries will get the good stuff, so takeNap and try numAttempts times.
		Some records will fail: for JSON with "Record does not exist" or "Holding does not exist",
			just build some dummy JSON for Holdings.js > libraryLocationsHandler()
	*/
	$badJSON = true;
	$quitWhile = 1; $numAttempts = 10;
	$debugWhile = "&nbsp;cURLrequest: assume badJSON, so " . $badJSON . ". ";
	while($badJSON && ($quitWhile <= $numAttempts) ){
		//to see what's going on in here, uncomment line below adding debugWhile to JS var scriptLink

			//cURLrequest($OCLCscriptURL) IS THE MONEY LINE THAT MAKES THE ACTUAL REQUEST
		$clientJSON = cURLrequest($OCLCscriptURL); 		//version sent to Javascript: PHP var typeof(clientJSON) == string
		$serverJSON = json_decode($clientJSON, true); //version used by PHP: 'true' means the JSON is an array here

			//strings that could be in the response + mean there is a problem
		$posDiagnostic 				= strpos($clientJSON, "diagnostic"); //all bad conditions seem to include this in the JSON
		$posNoRecord					= strpos($clientJSON, "Record does not exist"); // far more common than next conditions
		$posNoHolding					= strpos($clientJSON, "Holding does not exist");
		$posHoldingNotFound		= strpos($clientJSON, "Holding not found");
			//next diagnostic message means end of holders list: not catching it below, it was found only bc of AJE logic error
		$posEndOfHoldings			= strpos($clientJSON, "First position out of range");
		$JSONtroubleMsg = '<div class="apiNoRecordOrHolding">O# ' . $strOCLC_NUMBER . ' JSON has posDiagnostic @ ' . $posDiagnostic . ', ';
			$JSONtroubleMsg .= 'and posNoRecord @ ' . $posNoRecord . '; and posNoHolding @ ' . $posNoHolding . '; and posHoldingNotFound @ ' . $posHoldingNotFound . ':<br/>';
			//$JSONtroubleMsg .= 'clientJSON: ' . $clientJSON . '<br/>';
			$JSONtroubleMsg .= 'gettype(clientJSON) ' . gettype($clientJSON) . '<br/>';
			$JSONtroubleMsg .= 'serverJSON: ' . $serverJSON . '; gettype(serverJSON) ' . gettype($serverJSON) . '<br/>';
		if ($posDiagnostic === false) { //good data returned
			/*********************************************************
			clientJSON is fine - now do PAGING
			- while still on server, want to see if more holdings not returned (bc OCLC gives <= 100 holders per request)
			- if maximumLibraries 100, there may be more holdings
			- request again using cURL in background: One page load per title, no matter how many holders.
			- good set of numbers to test with:
					good + weird JSON: 233668988, 7900786, 16144636, 10011344, 16144627, 16777928
					100+ holders: 8207536, 8209340, 8210731, 8211281, 8212477, 8212481
			*/
				$libraryJSON 			= $serverJSON["library"]; //the whole list of libraries
				$numHolders 			= count($libraryJSON);
				$pageMsg = $strDebug . '<div class="pubData">Paging O#' . $strOCLC_NUMBER . '; numHolders = <span class="highlightNumbers">' . $numHolders . '</span>; maximumLibraries = <span class="highlightNumbers">' . $maximumLibraries . '</span>; ';
				if ( $numHolders == $maximumLibraries ){ // then maybe more holders on OCLC server
					do { //maximumLibraries paging loop
						$napTime = 1;
						$napResult 	= takeNap($napTime);
						/*
							- compose new URL with new paging number (startLibrary parameter)
									- this should be multiples of 100 if we want full pages
									- don't add just 1! //this example is wrong: $requestedPage++;
						*/
						$requestedPage = $requestedPage + $maximumLibraries;

						$nextURL					= composeOCLCscriptURL($strOCLC_NUMBER, $requestedPage);
						$nextJSON 				= json_decode(cURLrequest($nextURL), true); //call next page of results
						$numTheseHolders 	= count($nextJSON["library"]);

						$pageMsg 					.= '<br/>holders from <span class="highlightDocumentation">' . $requestedPage .  '</span>; ';
						$pageMsg 					.= 'numTheseHolders=<span class="first_date">' . $numTheseHolders . '</span> (in nextJSON["library"]); ';

							//put ea. member of new JSON's library array into original JSON's library array
						for($i=0; $i < $numTheseHolders; $i++){
							//array_push($serverJSON["library"], $nextJSON["library"][$i]); //GOOD SYNTAX DO NOT REMOVE
							$serverJSON["library"][] = $nextJSON["library"][$i];
						}//end for

						$numAllHolders 			= count($serverJSON["library"]); // WHILE LOOP DEPENDS ON THIS
						$clientJSON 			= json_encode($serverJSON); // CRITICAL this is what gets saved: JavaScript form submission
						$pageMsg 					.= 'after processing, numAllHolders = <span class="first_date">' . $numAllHolders . '</span>; ';
					} while ( $numTheseHolders >= $maximumLibraries );
					// end maximumLibraries paging do loop: condition means as long as OCLC keeps returning batches of 100, keep requesting
				}//end ( $numTheseHolders >= $maximumLibraries )

				$pageMsg .= '<br/>end pageMsg for O# ' . $strOCLC_NUMBER . '</div>';
				//echo $pageMsg;
				/*
				END PAGING SECTION:
				*********************************************************/

			$badJSON = 0;
			//$JSONtroubleMsg .= 'JSON is GOOD: <br/>' . $clientJSON . ' ';
		} //end if good data returned
		else if ($posNoRecord){ // RECORD does not exist, fix JSON
			//sample OCLC: 12067555 (see BAD_NUMBER_TEST.txt): returns {"diagnostic":{"uri":"info:srw/diagnostic/1/65","details":"","message":"Record does not exist"}}
			$clientJSON = composeNewJSON($strOCLC_NUMBER, $clientJSON, "[diagnostic message: 'Record does not exist']", false);
			$JSONtroubleMsg .= 'NEW JSON: <span class="alert">' . $clientJSON . '</span> ';
			$badJSON = 0; // stop re-checking
		} // end if RECORD does not exist
		else if ($posNoHolding){ // HOLDING DOES NOT EXIST: we still get title details, fix JSON
			//this is different from 'Holding not found', which is next
			//sample OCLC: 16777928 (see BAD_NUMBER_TEST.txt): returns {"title":"Human nutrition.","author":"","publisher":"John Libbey","date":"","OCLCnumber":"16777928",{"diagnostic":{"uri":"info:srw/diagnostic/1/65","details":"","message":"Holding does not exist"}}}
			$clientJSON = composeNewJSON($strOCLC_NUMBER, $clientJSON, "[diagnostic message: 'Holding does not exist']", true);
			$JSONtroubleMsg .= '<br/>title = *' . extractJSONtitle($clientJSON) . '*<br/>';
			$JSONtroubleMsg .= 'NEW JSON: <span class="alert">' . $clientJSON . '</span> ';
			$badJSON = 0; // stop re-checking
		} // end if HOLDING does not exist
		else if ($posHoldingNotFound){ // HOLDING may exist but NOT FOUND: we still get title details, fix JSON
			//CHECK libtype PARAMETER IN REQUEST TO OCLC FOR THIS TYPE OF ERROR
			//sample OCLC: 16144638  (see LIBTYPE_NUMBER_TEST.txt): returns {"title":"Fortschritt-Berichte der VDI-Zeitschriften.","author":"","publisher":"VDI-Verlag","date":"1965-","OCLCnumber":"16144636","library":[{"diagnostic":{"uri":"info:srw/diagnostic/1/65","details":"","message":"Holding not found"}}]}
			$clientJSON = composeNewJSON($strOCLC_NUMBER, $clientJSON, "[diagnostic message: 'Holding not found']", true);
			$JSONtroubleMsg .= '<br/>title = *' . extractJSONtitle($clientJSON) . '*<br/>';
			$JSONtroubleMsg .= 'NEW JSON: <span class="alert">' . $clientJSON . '</span> ';
			$badJSON = 0; // stop re-checking
		} // end if holding NOT FOUND

		else { //bad JSON not fixable via options above; sleep and try again, see if we get a good one
			$napTime = 1;
			$napResult 	= takeNap($napTime);
			$debugWhile 	.= $napResult;
		} //end if-else
		$debugWhile .= " After (" . $quitWhile . ") badJSON == " . $badJSON . ". ";
		$quitWhile++; //just try a certain number of times
	}//end while
	$JSONtroubleMsg .= '</div>';
	if ($posDiagnostic) { echo $JSONtroubleMsg; }


	$OCLCscriptBlock = '<script type="text/javascript">' . "\n";
		//next includes ADD SEMICOLON TO AVOID 'INVALID LABEL' JS ERROR
	//$OCLCscriptBlock .= "var JSON_" . $strOCLC_NUMBER . " = " . $clientJSON . "; " . "//PHP spat out the clientJSON\n\n\n";
	$OCLCscriptBlock .= "\n\nlibraryLocationHandler(" . $clientJSON . ");" . "// PHP spat out the function call with clientJSON \n\n\n";
	$OCLCscriptBlock .= '</script>' . "\n";
	echo $OCLCscriptBlock;
	//echo "<h2 class='alert'>NOT ECHOING THE OCLCscriptBlock</H2>";

	//info about the OCLCscriptURL goes in summaryBar result area for debugging or curiousity
	$infoScriptBlock = '<script language="javascript" type="text/javascript">';
	$infoScriptBlock .= 'var scriptLink  = "<span class=\"unimportant\">";';
	if (strpos($debugWhile, "takeNap")){
		$infoScriptBlock .=     'scriptLink += "&nbsp;' . $debugWhile . '";';
	}
	$infoScriptBlock .=     'scriptLink += "&nbsp;<a href=\"' . $OCLCscriptURL . '\" target=\"_blank\">";';
	$infoScriptBlock .= 		'scriptLink += "script link</a>&nbsp;";';
	$infoScriptBlock .= 'scriptLink += "</span>&nbsp;&nbsp;";';
	//$infoScriptBlock .= '$("#apiResult' . $strOCLC_NUMBER . '").prepend(scriptLink)';
	//$infoScriptBlock .= '$("#apiDisplayHeader' . $strOCLC_NUMBER . '").append(scriptLink)';
	$infoScriptBlock .= '$("#summaryBar' . $strOCLC_NUMBER . '").append("&nbsp;&nbsp;&nbsp;", scriptLink)';
	$infoScriptBlock .= '</script>';
	echo $infoScriptBlock;

	//return $OCLCscriptURL; //just for demo
}//end getJSONfromOCLC



/*******************************************************************
	insertAPItargetDiv: used by queryAPIfromText.php, queryAPIfromFile.php, queryAPIfromDB.php
		- each of the PHP scripts prepares OCLCnums (array) for this function
		- Holdings.js, holdingsData values put in dataResult[OCLC] by libraryLocationHandler()
*/
function insertAPItargetDiv($OCLCnums){
	global $strOCLC_NUMBER;

	for($i = 0; $i < count($OCLCnums); $i++){

			//first, build the div to be inserted: REAL_OCLC will be changed to each OCLC #
		$targetDiv = '<div class="apiDisplayBox">';
		$targetDiv .= '<div class="apiDisplayHeader">OCLC number:&nbsp;';
			$targetDiv .= '<span id="summaryBarREAL_OCLC" class="holdingsData important" style="background-color:REAL_BG_COLOR; border-color:#FFFFFF;">';
				$targetDiv .= '&nbsp;REAL_OCLC&nbsp;</span></div>'; //end #summaryBarREAL_OCLC and .apiDisplayHeader
		$targetDiv .= '<div id="dataResultREAL_OCLC" class="apiDataText" style="background-color:REAL_BG_COLOR;">';
			$targetDiv .= '<span class="alert">&nbsp;No data yet from OCLC API for #REAL_OCLC.&nbsp; OCLC may respond with merged record data, all saved with original OCLC #.</span>';
		$targetDiv .= '</div><!--end #dataResult[OCLC]--></div><!--end .apiDisplayBox-->';

		$strBGcolor = ($i % 2) ? "#adbd90" : "#e9e6d3";
		$strOCLC_NUMBER = trim($OCLCnums[$i]);

		$targetDiv = str_replace("REAL_BG_COLOR", $strBGcolor, $targetDiv);
		$targetDiv = str_replace("REAL_OCLC", $strOCLC_NUMBER, $targetDiv);

		echo $targetDiv;

	}//end for
}//end insertAPItargetDiv




function check_input($data){
// after http://myphpform.com/validating-forms.php; removes bad stuff from form input
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
} //end check_input



function setPrevNextPageLinks(){
	global $recordStep, $beginID, $endID, $minDBid, $maxDBid;
	if (isset($_REQUEST["beginID"])) 	$beginID = $_REQUEST["beginID"];
	else $beginID = 1;
	if (isset($_REQUEST["endID"])) 		$endID = $_REQUEST["endID"];
	else $endID = $beginID + ($recordStep-1);

	if ($beginID 	< $minDBid) 	$beginID 	= $minDBid;
	if ($endID 		> $maxDBid) 		$endID 		= $maxDBid;

	global $prevPageLink, $nextPageLink;
	global $thisFile, $recordStep;
	$prevPageLink = "<span class='previousPagingLink'>";
	$prevPageLink .= "<a href='" . $thisFile . "?action=queryAPI&datasource=database";
	$prevPageLink .= "&beginID=" . ($beginID-($recordStep - 1)) . "&endID=" . ($beginID-1);
	$prevPageLink .= "'>&lt;--Previous set (records " . ($beginID-($recordStep - 1)) . "-" . ($beginID-1) . "]</a></span>";

	$nextPageLink = "<span class='nextPagingLink'>";
	$nextPageLink .= "<a href='" . $thisFile . "?action=queryAPI&datasource=database";
	$nextPageLink .= "&beginID=" . ($endID+1) . "&endID=" . ($endID+$recordStep);
	$nextPageLink .= "'>Next set (records " . ($endID+1) . "-" . ($endID+$recordStep);
	$nextPageLink .= "]--&gt;</a></span>";

	//echo "<p>prevPageLink = '" . $prevPageLink . "nextPageLink = '" . $nextPageLink . "</p>";
} //end setPrevNextPageLinks
/***************************************************************************************/



/**************************************************************************************
	- put database or form data in string variables
	- see previous version in backups 2012-11-05 14:36:54
*/
function fillStringVars($datasource){
	global $strID, $strOCLC_NUMBER, $strOCLCalternate, $strISSN;
	// $strLCCN, $strMFHD
	global $strTITLE, $strCRLholds, $strAllNames, $strAllCodes, $strNumHolders;
	global $minOCLC_NUMBER, $maxOCLC_NUMBER, $fromOCLC_NUMBER, $toOCLC_NUMBER;
	global $row;

	$strDebug = 'fillStringVars(' . $datasource . '): ';
	//echo $strDebug;
	if ($datasource == "database"){
		$strID 					= trim($row["id"]);
		$strOCLC_NUMBER = trim($row["OCLC_NUMBER"]);
		$strOCLCalternate = trim($row["OCLCalternate"]);
		$strISSN = trim($row["ISSN"]);
		//$strLCCN 				= trim($row["LCCN"]);
		$strTITLE 			= trim($row["title"]);
		$strCRLholds = trim($row["CRLholds"]);
		if ($strCRLholds == "") $strCRLholds = "&nbsp;";
		$strNumHolders	 	= trim($row["numHolders"]);
		$strAllNames 		= trim($row["allNames"]);
		$strAllCodes 		= trim($row["allCodes"]);
	} else { //for form data, or fileupload, or blank if form not submitted
			if (isset($_REQUEST["id"])) $strID = trim($_REQUEST["id"]);
			else  $strID = "";

			if (isset($_REQUEST["OCLC_NUMBER"])) $strOCLC_NUMBER = trim($_REQUEST["OCLC_NUMBER"]);
			else $strOCLC_NUMBER = "";

			/*
			if (isset($_REQUEST["LCCN"])) $strLCCN = trim($_REQUEST["LCCN"]);
			else $strLCCN = "";
			$strDebug .= '; strLCCN = ' . $strLCCN . '; ';
			*/

			if (isset($_REQUEST["title"])) $strTITLE = trim($_REQUEST["title"]);
			else $strTITLE = "";

			if (isset($_REQUEST["CRLholds"])) $strCRLholds = trim($_REQUEST["CRLholds"]);
			else $strCRLholds = "";

			if (isset($_REQUEST["numHolders"])) $strNumHolders	 = trim($_REQUEST["numHolders"]);
			else $strNumHolders = "";

			if (isset($_REQUEST["allNames"])) $strAllNames = trim($_REQUEST["allNames"]);
			else $strAllNames = "";

			if (isset($_REQUEST["allCodes"])) $strAllCodes = trim($_REQUEST["allCodes"]);
			else $strAllCodes = "";

				//special vars in report forms
			if (isset($_REQUEST["fromOCLC_NUMBER"])) $fromOCLC_NUMBER = $_REQUEST["fromOCLC_NUMBER"];
			else $fromOCLC_NUMBER = $minOCLC_NUMBER;
			if (isset($_REQUEST["toOCLC_NUMBER"])) $fromOCLC_NUMBER = $_REQUEST["toOCLC_NUMBER"];
			else $toOCLC_NUMBER = $maxOCLC_NUMBER;

	}//end else not database

	$strDebug .= '; strID = ' . $strID . ';  strOCLC_NUMBER = ' . $strOCLC_NUMBER;
	$strDebug .= '; strTITLE = ' . $strTITLE . '; strCRLholds = ' . $strCRLholds . '; ';
	//$strDebug .= '$ REQUEST [numHolders] = ' . $_REQUEST["numHolders"] . '; ';
	$strDebug .= 'strNumHolders = ' . $strNumHolders . '; ';
	$strDebug .= '; strAllNames = ' . $strAllNames . '; strAllCodes = ' . $strAllCodes . '; ';
	//echo '<h4>' . $strDebug . '</h4>';
}//end fillStringVars





function takeNap( $napTimeSeconds ){
		date_default_timezone_set('America/Chicago');
		$date = date( "G:i:s T" );
		$napResult = 'takeNap ' . $date . '\n';	// current time
		sleep($napTimeSeconds);		// sleep for X seconds
		$date = date( "G:i:s T" );
		$napResult .= 'awake ' . $date . '.\n ';
		//echo $napResult;
		return $napResult;
}//end takeNap



function getDatasource(){ //compose and return message to show the datasource: file name, or form input...
	global $reqDatasource, $fileuploadpath, $filelocation, $filename, $tableChoice; //set in appConfigOCLCapi.php

	$strDataSourceMsg = 'input data source is currently <span class="highlightProcessing">&nbsp;';
	if ($reqDatasource == "text") {
		$strDataSourceMsg .= "text: form field submitted";
	} else if($reqDatasource == "fileupload"){
		$strDataSourceMsg .= "file: <a href='" . $fileuploadpath .  $filename . "'>" .  $filename . "</a>";
	}	else if($reqDatasource == "database"){
		$strDataSourceMsg .= "database: read from table " . $tableChoice;
	}
	$strDataSourceMsg .= '&nbsp;</span>';

	return $strDataSourceMsg;
}//end getDatasource



function getOCLCoptions(){ //compose and return message to show the OCLC API options used
	global $maximumLibraries, $maximumHolders, $startLibrary, $libtype;

	//$strOptionsMsg = '<div class="highlightCode">OCLC API system on ' . $_SERVER['SERVER_NAME'] . ' configured with options: </div>';
	$strOptionsMsg = '<div class="redBoxData">OCLC API system on ' . $_SERVER['SERVER_NAME'] . ' configured with options:<ul>';
	//$strOptionsMsg .= '<li>OCLC API system on ' . $_SERVER['SERVER_NAME'] . ' configured with options: </li>';
	$strOptionsMsg .= '<li>' . getDatasource() . '</li>';
	$strOptionsMsg .= '<li>maximumLibraries parameter (per request in url) = <span class="highlightProcessing">' . $maximumLibraries . '</span></li>';
	//$strOptionsMsg .= '<li>maximumHolders (total holders) = <span class="highlightProcessing">' . $maximumHolders . '</span></li>';
	$strOptionsMsg .= '<li>startLibrary parameter: get holders from number <span class="highlightProcessing">' . $startLibrary . '</span></li>';

	$strOptionsMsg .= '<li>libtype parameter ("" = all libraries; OR 1 = academic, 2 = public, 3 = government, 4 = other): ';
	$strOptionsMsg .= '<span class="highlightProcessing">&nbsp;' . $libtype . '&nbsp;</span>';
		if ($libtype != "") {
			$strOptionsMsg .= '<br/><span class="littleAlert">libtype NOT blank:&nbsp;may affect API returning merged/superseded OCLC # substitute data; ';
			$strOptionsMsg .= '<br/>may NOT return records for:</span> <span class="littleReverseAlert">LHL</span>. ';
			$strOptionsMsg .= 'Causes diagnostic messages like "<span class="apiNoRecordOrHolding">Holding not found</span>". ';
			$strOptionsMsg .= '(LHL not returned under libtype 1 or 4) ';
		} else {
			//$strOptionsMsg .= '<span class="highlightCode">libtype BLANK: may be thousands of holding libraries: OCLC #s will have to be resubmitted to get each batch of 100';
			$strOptionsMsg .= '<br/>libtype BLANK: may be thousands of holding libraries: system is configured to get them all; may be a long time';
		}
		$strOptionsMsg .= '</li>';
		$strOptionsMsg .= '</ul></div>';

	return $strOptionsMsg;

}//end getOCLCoptions



/*****************************************************************************************
begin functions related to querying data from database and displaying it
*/

/*****************************************************************************************
	called by queryDBprocess.php - boolean vars are in appConfigOCLCapi.php
*/
function setBooleansForFields(){
	global $bTITLE, /* $bLCCN, */ $bOCLC, $b_numHolders, $bSummarize, $bNoChangedFormData;
	global $fromOCLC_NUMBER, $toOCLC_NUMBER, $minOCLC_NUMBER, $maxOCLC_NUMBER;
	global $from_numHolders, $to_numHolders, $min_numHolders, $maximumLibraries;

	$bTITLE = false;
	/* $bLCCN = false; */
	$bOCLC = false;
	$b_numHolders = false;
	$bSummarize = false;
	$bNoChangedFormData = false;
	$setBooleansDebug = "<div class='processNavigationLink'>setBooleansForFields...<br />";

	if (isset($_REQUEST["title"]) && ($_REQUEST["title"] != "")){
		$bTITLE = true;
	}
	/*
	if (isset($_REQUEST["LCCN"]) && ($_REQUEST["LCCN"] != "")){
		$bLCCN = true;
	}
	*/

	if (isset($_REQUEST["fromOCLC_NUMBER"]) || isset($_REQUEST["toOCLC_NUMBER"])){
		$bOCLC = true;
	}
	$bSameMinOCLC = ((int)($fromOCLC_NUMBER) == $minOCLC_NUMBER) && ($fromOCLC_NUMBER != "") ? true : false;
	$bSameMaxOCLC = ((int)($toOCLC_NUMBER)   == $maxOCLC_NUMBER) && ($toOCLC_NUMBER != "") ? true : false;
	if ((($bSameMinOCLC == false) && ($bSameMaxOCLC == false) )){
		$bOCLC = true;
	}

	// if (($bTITLE == false) && ($bLCCN == false) && ($bOCLC == false)) {
	if (($bTITLE == false) && ($bOCLC == false)) {
		$bNoChangedFormData = true;
	}

	$setBooleansDebug .= "req TITLE = " . $_REQUEST["title"] . "; bTITLE = " . $bTITLE . "<br />";
	//$setBooleansDebug .= "req LCCN = " . $_REQUEST["LCCN"] . "; bLCCN = " . $bLCCN . "<br />";
	$setBooleansDebug .= "req fromOCLC_NUMBER = " . $_REQUEST["fromOCLC_NUMBER"] . "; bOCLC = $bOCLC<br />";
	$setBooleansDebug .= "req toOCLC_NUMBER = " . $_REQUEST["toOCLC_NUMBER"] . "; bOCLC = $bOCLC<br />";
	$setBooleansDebug .= "minOCLC_NUMBER = '" . $minOCLC_NUMBER . "'; fromOCLC_NUMBER='"  . $fromOCLC_NUMBER . "'<br />";
	$setBooleansDebug .= "toOCLC_NUMBER='"    . $toOCLC_NUMBER  . "'; maxOCLC_NUMBER = '" . $maxOCLC_NUMBER  . "'<br />";
	$setBooleansDebug .= "from_numHolders == '" . $from_numHolders . "'; to_numHolders == '" . $to_numHolders . "'<br />";
	//echo $setBooleansDebug;

	$setBooleansDebug .= "bNoChangedFormData= '" . $bNoChangedFormData . "'<br />";

	$bSameMinHolders = ((int)($from_numHolders) == $min_numHolders) ? true : false;
	$bSameMaxHolders = ((int)($to_numHolders) == $maximumLibraries) ? true : false;
	if (($bNoChangedFormData = true) || ($bSameMinHolders == false) || ($bSameMaxHolders == false)){
	  	$b_numHolders = true;
	}
	$setBooleansDebug .= "b_numHolders = $b_numHolders<br />end setBooleansForFields";
	$setBooleansDebug .= "</div>";
	//echo "<span class='highlightNumbers'>" . $setBooleansDebug . "</span>";

} //end setBooleansForFields




/*****************************************************************************************
	string variables are ready when fillStringVars() completes in appConfigOCLCapi.php:
			Title, LCCN, from* + toOCLC_NUMBER, fromNUM* + $to_numHolders
*/
function prepareReportVariables(){
	global $strID, $strOCLC_NUMBER, $strLCCN, $strTITLE, $strCRLholds, $strAllNames, $strNumHolders, $strMFHD;
	global $bTITLE, /* $bLCCN, */ $bOCLC, $b_numHolders, $bSummarize, $bNoChangedFormData, $sortByField;
	global $fromOCLC_NUMBER, $toOCLC_NUMBER, $minOCLC_NUMBER, $maxOCLC_NUMBER;
	global $from_numHolders, $to_numHolders, $min_numHolders, $maximumLibraries;
	global $strReportTitle;

	setBooleansForFields();

	//sortByField is set as appConfigOCLCapi global

	if (isset($_REQUEST["titletype"]) && ($_REQUEST["titletype"] != "")){
		//echo '<h2>_REQUEST["titletype"] == ' . $_REQUEST["titletype"] . "</h2>";
		$strTitletype = $_REQUEST["titletype"];
	}	else {
		$strTitletype = "index";
	}//end if


	//construct the report title, summarizing criteria used
	$strReportTitle .= "report criteria: ";
	if ( isset($_REQUEST["title"]) && ($_REQUEST["title"] != "")  ){
		$strReportTitle .= "Title field <em>";
		//echo 'strTitletype=="' . $strTitletype . '"';
		if ($strTitletype == "contains")
			$strReportTitle .= "contains</em> '";
		else {
			$strReportTitle .= "begins with</em> '";
		}//end if
		$strReportTitle .= "<span class='highlightCode'>" . $strTITLE . "</span>'.";
	}//end if

	/*
	if ( isset($_REQUEST["LCCN"]) && ($_REQUEST["LCCN"] != "")  ){
		$strReportTitle .= " Library of Congress Classification Number contains ";
		$strReportTitle .= "'<span class='highlightCode'>" . $strLCCN . "</span>'.";
	}//end if
	*/

	if ($bOCLC == 1){
		if (isset($_REQUEST["fromOCLC_NUMBER"])){
			$fromOCLC_NUMBER = $_REQUEST["fromOCLC_NUMBER"];
		}
		if (isset($_REQUEST["toOCLC_NUMBER"])){
			$toOCLC_NUMBER = $_REQUEST["toOCLC_NUMBER"];
		}
		if ($fromOCLC_NUMBER == $toOCLC_NUMBER)
			$strReportTitle .= ' OCLC number <span class="highlightProcessing">' . $fromOCLC_NUMBER . '</span>.';
		else {
			$strReportTitle = ' OCLC number range <span class="highlightProcessing">' . $fromOCLC_NUMBER;
			$strReportTitle .= '</span>-<span class="highlightProcessing">' . $toOCLC_NUMBER . '</span>.';
		}//end else
	}//end if

	if ( isset($_REQUEST["from_numHolders"]) ){
		$from_numHolders 	= $_REQUEST["from_numHolders"];
	} else {
		$from_numHolders = $min_numHolders;
	}
	if ( isset($_REQUEST["to_numHolders"]) ){
		$to_numHolders 		= $_REQUEST["to_numHolders"];
	} else {
		$to_numHolders = $maximumLibraries;
	}
	if ($from_numHolders == $to_numHolders){
		$strReportTitle .= " Number of holding libraries: '<span class='highlightProcessing'>";
		$strReportTitle .= $from_numHolders . "</span>'.";
	} else {
		$strReportTitle .= " Number of holding libraries range: ";
		$strReportTitle .= "<span class='highlightProcessing'>" . $from_numHolders . "</span>";
		$strReportTitle .= "-<span class='highlightProcessing'>" . $to_numHolders . "</span>.";
	}//end if


	if (isset($_REQUEST["summarizeBox"]) && ($_REQUEST["summarizeBox"] != "summarize")){
		$strReportTitle .= " Sorted by <em>";
		switch ($sortByField){
			case "title":
				$strReportTitle .= strtolower($sortByField);
				break;
			case "LCCN":
				$strReportTitle .= $sortByField;
				break;
			case "OCLC_NUMBER":
				$strReportTitle .= "OCLC number";
				break;
			case "NUM_HOLDERS":
				$strReportTitle .= "number of holding libraries";
				break;
		} //end switch
		$strReportTitle .= "</em>.";
	}//end if summarize
	//end construct the report title, summarizing criteria

	//echo "<span class='alert'><br />strReportTitle = $strReportTitle</span>";
} //end prepareReportVariables
/******************************************************************************************/



/******************************************************************************************/
function prepareReportSQL(){

	global $tableChoice, $sortByField;
	global $strID, $strOCLC_NUMBER, $strLCCN, $strTITLE, $strCRLholds, $strAllNames, $strNumHolders, $strMFHD;
	global $bTITLE, /* $bLCCN, */ $bOCLC, $b_numHolders, $bSummarize, $bNoChangedFormData;
	global $fromOCLC_NUMBER, $toOCLC_NUMBER, $minOCLC_NUMBER, $maxOCLC_NUMBER;
	global $from_numHolders, $to_numHolders, $min_numHolders, $maximumLibraries;

	$this_strSQL = 'SELECT * FROM ' . $tableChoice . ' WHERE ';
		$strReportSQLdebug = '<h4 class="highlightDocumentation">prepareReportSQL:';
		$strReportSQLdebug .= '<br />sortByField = ' . $sortByField . '&nbsp;';
		$strReportSQLdebug .= '<div class="alert">1) this_strSQL = ' . $this_strSQL . '</div>';


	if ($bTITLE == 1){
		$strReportSQLdebug .= '<br />_REQUEST["titletype"] = ' . $_REQUEST['titletype'] . '<br />';
		if (isset($_REQUEST['titletype']) && ($_REQUEST['titletype'] == 'index'))
			$this_strSQL .= " (" . $tableChoice . ".title LIKE '%" . $strTITLE . "%')";
		else{
				if (isset($_REQUEST["titletype"]) && ($_REQUEST["titletype"] == "contains"))
					$this_strSQL .= " InStr(" . $tableChoice . ".title, '" . $strTITLE . "') <> 0";
		}//end else
	}//end if title

	$strReportSQLdebug .= "<div class='highlightNumbers'>2) this_strSQL = $this_strSQL</div>";

	/*
	if ($bLCCN == 1){
		if ($bTITLE == 1) $this_strSQL .= " AND";
		$this_strSQL .= " (" . $tableChoice . ".LCCN LIKE '" . $strLCCN . "%')";
	}//end if lccn
	*/

	//extended check whether OCLC form data differs from default, + not blank
	if ($bOCLC == 1){
		//if (($bTITLE == 1) || ($bLCCN == 1)) $this_strSQL .= " AND";
		if ($bTITLE == 1) $this_strSQL .= " AND";
		if ($fromOCLC_NUMBER == $toOCLC_NUMBER)
			$this_strSQL .= " (" . $tableChoice . ".OCLC_NUMBER = '" . $fromOCLC_NUMBER . "')";
		else {
			$this_strSQL .= " ((" . $tableChoice . ".OCLC_NUMBER >= '" . $fromOCLC_NUMBER . "')";
			$this_strSQL .= " AND (" . $tableChoice . ".OCLC_NUMBER <= '" . $toOCLC_NUMBER . "'))";
		}//end else
	}//end if  $fromOCLC_NUMBER != minOCLC etc

	/* 	- will always have NUM_HOLDERS: it's a select box
		- if holdings numbers were changed, add to the SQL;
		- if not, but no other field was either, we need sth for the WHERE clause, so use NUM_HOLDERS field		*/
	if ( ($b_numHolders == 1) || ($bNoChangedFormData==1) ){
		//if (($bTITLE == 1) || ($bLCCN == 1) || ($bOCLC == 1)) $this_strSQL .= " AND";
		if (($bTITLE == 1) || ($bOCLC == 1)) $this_strSQL .= " AND";
		if ($from_numHolders == $to_numHolders){
			$this_strSQL .= " (" . $tableChoice . ".numHolders = " . $from_numHolders . ")";
		} else {
			$this_strSQL .= " ((" . $tableChoice . ".numHolders >= " . $from_numHolders . ")";
			$this_strSQL .= " AND (" . $tableChoice . ".numHolders <= " . $to_numHolders . "))";
		}//end else
	}//end if num holders was changed

	//$this_strSQL .=  " AND (" . $tableChoice . ".allNames IS NOT NULL)";
	$this_strSQL .=  " ORDER BY " . $tableChoice . "." . $sortByField . ";";

	$strReportSQLdebug .= "bNoChangedFormData = '" . $bNoChangedFormData . "'; <br />";
	$strReportSQLdebug .= "bTITLE = <span class='alert'> '" . $bTITLE . "' </span>; ";
	$strReportSQLdebug .= "strTITLEsql = '" . $this_strSQL . "'<br />";
	//$strReportSQLdebug .= "bLCCN = <span class='alert'> '" . $bLCCN . "' </span>; ";
	$strReportSQLdebug .= "strLCCNsql = '" . $this_strSQL . "'<br />";
	$strReportSQLdebug .= "bOCLC = <span class='alert'> '" . $bOCLC . "' </span>; ";
	$strReportSQLdebug .= "strOCLCsql = '" . $this_strSQL . "'; ";

	$strReportSQLdebug .= "fromOCLC_NUMBER = <span class='alert'> '" . $fromOCLC_NUMBER . "' </span>; ";
	$strReportSQLdebug .= "toOCLC_NUMBER= <span class='alert'>'" . $toOCLC_NUMBER . "' </span>; ";

	$strReportSQLdebug .= "minOCLC_NUMBER = <span class='alert'> '" . $minOCLC_NUMBER . "' </span>; ";
	$strReportSQLdebug .= "maxOCLC_NUMBER= '" . $maxOCLC_NUMBER . "'<br />";
	$strReportSQLdebug .= "b_numHolders = <span class='alert'> '" . $b_numHolders . "' </span>; ";

	$strReportSQLdebug .= "from_numHolders = <span class='alert'> '" . $from_numHolders . "' </span>; ";
	$strReportSQLdebug .= "to_numHolders= <span class='alert'> '" . $to_numHolders . "' </span>; ";

	$strReportSQLdebug .= "min_numHolders = <span class='alert'> '" . $min_numHolders . "'</span>; ";
		$strReportSQLdebug .= "maximumLibraries= '" . $maximumLibraries . "'<br />";
		$strReportSQLdebug .= "strHOLDERSsql = <span class='alert'> '" . $this_strSQL . "'<br />";

	$strReportSQLdebug .= "<p class='newData'>returning<br />" . $this_strSQL . "</p>";
	$strReportSQLdebug .= "</h4>";
	//echo $strReportSQLdebug;

	return $this_strSQL;

} //end prepareReportSQL



function getRowsInDatabase(){ //return total number of rows
	// Database access information set as constants in appConfigOCLCapi.php
	global $tableChoice;
	//echo '<p>DB_HOST=' . DB_HOST . ', DB_USER=' . DB_USER . ', DB_PASSWORD=' . DB_PASSWORD . ', DB_NAME=' . DB_NAME . ', tableChoice=' . $tableChoice . '</p>';

	$thisDBConnection   = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die ('<h3 class="alert">getRowsInDatabase() Could not connect to MySQL: <br /><span class="highlightCode">' . mysqli_error($databaseConnection) . "</span>. </h3>" );

	$countQuery = 'SELECT COUNT(*) FROM ' . $tableChoice . ';';
	//echo "<h3 span class='highlightNumbers'>getRowsInDatabase is about to count, using query '" . $countQuery . "' ... ";
	$result       = mysqli_query($thisDBConnection, $countQuery) or die('Error counting the records: <span class="highlightCode">' . mysql_error() . "</span>");
	//echo "</h3>";

		//there is 1 row in the result, the value in the 1 row is the count of records
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	if (isset($row["COUNT(*)"])) {
		$numTotalRows = $row["COUNT(*)"];
	} else {
		$numTotalRows = mysqli_num_rows($result) . ' rows in dataset'; // 1: this means an error
	}
	mysqli_free_result($result);
	mysqli_close($thisDBConnection);
	return $numTotalRows;
}//end getRowsInDatabase







?>