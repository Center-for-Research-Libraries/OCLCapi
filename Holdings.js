<script language="javascript" type="text/javascript">

/*
	AJE 8-Dec-2010
		- This file contains scripts for use with OCLC's WorldCat Search API
		- Can't do regular src="*.js" bc needs access to database information served by PHP
		- done as require_once() in OCLCapi/index.php

	AJE 2012-11-05
		- see MFHD.js for MARC FORMAT for HOLDINGS DATA functions that never became production data

*/


/*******************************************************************************************
	JAVASCRIPT OBJECTS DEFINED IN RECORDOBJECTS.js
*******************************************************************************************/
//global vars for all scripts; wskey assigned by OCLC
var wskey = "RO0ajrCDg7Zwh4MXsz3VK5a9TyNw2Osxw4o1iPzx1plqp8o9Yf6L5tXCvLXTOnDJ7qCBGbYGCuwIxsPL";
	//shortcuts for calls to various APIs
var OCLC_REQ_MARC_XML_BIB 	= "http://www.worldcat.org/webservices/catalog/content/";
var OCLC_REQ_SRU 			= "http://www.worldcat.org/webservices/catalog/search/sru?query=";
var OCLC_LIBRARY_LOCATION = "http://www.worldcat.org/webservices/catalog/content/libraries/";
var zipCode = 60637;
//var maxHoldingLibraries = 50; // see applicationConfiguration.php and holdingsOCLCcheckAPI.php


/*
	http://www.loc.gov/marc/specifications/specchargeneral.html
		"subfield delimiter, 1F(hex) in MARC-8 and Unicode encoding"

	http://www.oclc.org/developer/content/marc-json-draft-2010-03-11
		[not a standard; in this page OCLC replaces delimiter with 241F]

	elsewhere found:
		- double dagger is: \u2021
		- AJE: I think 1F would be 496 in decimal; 1F is not showing up on this page or in any of the utilities I've checked.
		use 0024 (the dollar) for now because at least it shows up.
	[I don't have the font needed: http://www.fileformat.info/info/unicode/char/1F/fontsupport.htm]
*/
var delimiter  	= "\u2021";	//really 001F
var fieldTerminator 	= "\u001E";
var recordTerminator 	= "\u001D";

	//many records have more than 1 call number in 590: use these lists to treat each one separately
var callNumberList = new Array();
var holdingsList = new Array(); // enumeration and issue info in the 590 for each call number
	//things that indicate the beginning of a call number
var callNumHeaders = new Array("A-", "B-", "C-", "D-", "E-", "F-", "G-", "MF-", "R-", "Serials", "SERIALS", "2J", "7A", "7B", "7C", "7D", "7E", "7F", "7G", "7H", "7I", "7J", "7K", "7L", "7M", "7N", "7O", "7P", "7Q", "7R", "7S", "7T", "7U", "7V", "7W", "7X",
/*
"5/", "71-", "72-", "73-", "74-", "75-", "76-", "77-", "78-", "79-", "80-", "81-", "82-", "83-", "84-",
*/
"T3/", "Temporarily in Physical Processing", "TEMPORARILY IN PHYSICAL PROCESSING", "Temporarily in physical processing",  "4th Floor Thai Serials", "Thai Serials");

var monthNames = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");//no periods, same as we expect from 590
var monthAbbrevs = new Array("Jan.", "Feb.", "Mar.", "Apr.", "May.", "Jun.", "Jul.", "Aug.", "Sep.", "Oct.", "Nov.", "Dec.");//variation
var firstHoldingsYear = "";
var lastHoldingsYear = "";
var firstHoldingsMonth = "";
var lastHoldingsMonth = "";


	//things to be removed from 590
var removeStatements = new Array("Center has:", "Center’s holdings begin with:", "Center's holdings:", "Center's holdings combine 2 eds.:", "(irregular numbering)");
	//ways missing issue info may be expressed
var lackingExpressions = new Array("LACK", "Lack", "lack", "MISSING", "Missing", "missing", "WANTING", "Wanting", "wanting");
	//things to trigger the end of an enumeration statement
var enumerationEnds	= new Array("-", ",", " ", "(", " ", "." ); //list in order of likelihood to be found
//end global vars



/******************************************************************************************
	Cookie functions after http://www.w3schools.com/js/js_cookies.asp
*/
function setCookie( cookieName, inValue, numDaysCookieLives ){
	//alert("setCookie( " + cookieName+ ", " +inValue+ ", " +numDaysCookieLives+ " )");
	var cookieExpirationDate = new Date();
	cookieExpirationDate.setDate( cookieExpirationDate.getDate() + numDaysCookieLives );
	var cookieValue=escape( inValue ) + ( (numDaysCookieLives==null) ? "" : "; expires=" + cookieExpirationDate.toUTCString() );
	document.cookie=cookieName + "=" + cookieValue;
}//end setCookie

function getCookie( cookieName ){
	var i, x, y, ARRcookies=document.cookie.split( ";" );
	for ( i=0; i < ARRcookies.length; i++ ){
		x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("=") );
		y = ARRcookies[i].substr( ARRcookies[i].indexOf("=")+1 );
		x = x.replace( /^\s+|\s+$/g, "" );
		if ( x==cookieName ) return unescape( y );
	}
}//end getCookie(cookieName)
/*	end cookie functions
*******************************************************************************************/


/*******************************************************************************************
	in emailing and file creation functions for holdings,
		we look for a cookie with the email address to use for sending,
		or the first part of the email for a unique user name.
*/
function checkForEmailAddress(){
	var emailAddress = getCookie("emailAddress");
	if (( (typeof emailAddress) == "undefined") || (emailAddress == "")){
		var inputAddress = document.saveAPIdataForm.emailAddressBox.value;
		var atpos 	= inputAddress.indexOf("@");
		var dotpos 	= inputAddress.lastIndexOf(".");
		if ( atpos < 1 || dotpos < atpos+2 || dotpos+2 >= inputAddress.length){
		  alert("Please type your email address into the box.");
		  document.saveAPIdataForm.emailAddressBox.focus();
		  return false;
		} else {
			//alert("about to set cookie with " +inputAddress);
			setCookie("emailAddress", inputAddress, 1);
			return true;
		}//end if/else chain
	}//end if no email address
}//end checkForEmailAddress


function stripBadCharacters( strOldValue ){ //remove commas from OCLC_NUMBERlist
	var strDebug = "stripBadCharacters( " + strOldValue + " )";
	var strNewValue = strOldValue;
	while ( strNewValue.charAt( strNewValue.length-1 ) == "," ){
		strNewValue = strNewValue.substring(0, strNewValue.length-1);
		strDebug += "\n" + strNewValue;
	}//end while
	//alert(strDebug);
	document.forms[0].elements[0].value = strNewValue;
}//end stripBadCharacters



/******************************************************************************
	parseOpacUrl is helper for libraryLocationHandler();
		parseOpacUrl NOT used by parseISSN even though parseISSN uses the URL, because parseISSN needs URL in escaped form
*/
function parseOpacUrl( rawUrl, OCLCnumber ){ //API supplying junk links, try to clean up
	rawUrl = unescape( rawUrl );
	var strDebug = "parseOpacUrl( " +rawUrl+ " )\n\n\n\n";
	var stripFrom = rawUrl.indexOf("&url=")+5; //+5 for length of sought string
	var stripTo = rawUrl.indexOf("&checksum=");
	var goodUrl = rawUrl.substring(stripTo, stripFrom);
	strDebug += "stripTo = " +stripTo+ ", stripFrom = " +stripFrom+ "\ngoodUrl=" +goodUrl;
	//update_apiResult(strDebug, OCLCnumber);
	return goodUrl;
}//end parseOpacUrl


/******************************************************************************
	we've requested info for an OCLC number;
		libLoc_JSON_obj is the whole JSON object returned by the API.
	parseISSN is called by libraryLocationHandler and receives libLoc_JSON_obj.
	NOTE libraryLocationHandler checked if libLoc_JSON_obj is valid before calling parseISSN,
		so we shouldn't have to check its validity.
	If an ISSN exists, it is NOT a member of libLoc_JSON_obj,
		but only as text inside libLoc_JSON_obj.library.opacUrl,
		where it's a parameter in the URL for some of the library links.
		An example of an OCLC that returns an ISSN is: O# 3252677 = ISSN 0001-0049.
	See ./OCLCapi/sample OCLC.../3252677hasISSN.js
	Possible patterns in URLs from that file:
		%3D0001-0049%26	[%3D means '=', %26 means '&']
		%2F0001-0049%26 [%2F means '/']
			variant:	%2Fi0001-0049%26 [extra 'i' is abbreviation for isbn holdings library uses in their opac]
		%5E0001-0049%26	[%5E means '^']
		%3F0001-0049%26 [%3F means '?']
		%3A0001-0049%26	[%3A means ':']
		%253A0001-0049%26	[%25 means '%', so opac using '%%' ?]
	%3D[issn]%26 = MOST COMMON in O# 3252677 (25 times);
	%2Fi[issn]%26 = 2nd most common (11 times, with %2F[issn]%26 3rd most @ 4 times)
*/
function parseISSN( libLoc_JSON_obj ){
	var strDebug = "parseISSN has libLoc_JSON_obj.OCLCnumber == '" +libLoc_JSON_obj.OCLCnumber+"'. ";
	var ISSN = "0000-0000"; //dummy value
	var issnRegExp = new RegExp(/%[235](A|D|E|F|Fi)(\d{4}-\d{4})/); //with parens around the \d{4} pair, means capture that
		// pct char; plus one of 2,3,5; plus one of A,D,E,F, or Fi; plus 4 digits; plus '-'; plus 4 more digits
		// no /g or /i (global and case-insensitive flags) on RegExp bc we only want 1st match

	var OCLCnumber 	= libLoc_JSON_obj.OCLCnumber;
	var libraryObj 	= libLoc_JSON_obj.library;
	//alert(libraryObj.length);
	var numHolders 	= libraryObj.length;

		// look in each holdings library's opacUrl for an ISSN pattern, extract + return 1st one found
	for (var h = 0; h < numHolders; h++){
			var opacUrl 		= libraryObj[h].opacUrl;

			strDebug += "<br/>"+h+") opacUrl=" +opacUrl+ " ";

			if (issnRegExp.test(opacUrl)) { //RegExpObject test() method checks for match in string param
				strDebug += '<span class="alert"> ' +issnRegExp+ ' FOUND</span> .';
				var execResult = issnRegExp.exec(opacUrl);
				ISSN = execResult[2]; //https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Global_Objects/RegExp/exec
				//$('#saveAPIdataForm').append("<br/>parseISSN strDebug is " + strDebug);
				return ISSN; //as soon as we find an ISSN, no need to keep looking
			} else strDebug += ' <span class="highlightCode">' +issnRegExp+ ' not found</span>.';

	}//end for h

	//$('#saveAPIdataForm').append("<br/>parseISSN strDebug is " + strDebug);

	return ISSN;
} //end parseISSN



/******************************************************************************
	libraryLocationHandler() receives a JSON obj
		from OCLC's Library Locations in WorldCat Search API
		builds a display string with the info,
		prepares form data to be submitted.

	- global var OCLCrequested is set by PHP in applicationConfiguration.getJSONfromOCLC()
	- OCLCrequested is the number we submitted to the API to look up
	- OCLCrequested may not be same as the O# that gets returned (merged records, etc.)
	- if it's not the same, then we save OCLCrequested in a new field

*/
function libraryLocationHandler( libLoc_JSON_obj ){
	var strDebug = "OCLC WorldCat Search API, Library Locations, func libraryLocationHandler \n";
			strDebug += eval(libLoc_JSON_obj) + "\n";
			//alert(strDebug);

	if ((libLoc_JSON_obj == null) ||
		 (typeof libLoc_JSON_obj  === "undefined") ||
		 (libLoc_JSON_obj.library == "")) {
		 		//alert("libLoc_JSON_obj is undefined");
		 		//document.writeln("<h3>libLoc_JSON_obj is undefined, or useless</h3>");
		 		return;
	}

		//parse the data
	var ISSN 				= parseISSN( libLoc_JSON_obj ); //ISSN is complex: see this func for details
	var libraryObj 	= libLoc_JSON_obj.library;
	var OCLCnumber 	= libLoc_JSON_obj.OCLCnumber;
	var title				= libLoc_JSON_obj.title;

	if (typeof title === "undefined"){
		return;
	} else {
			if (title.charAt(title.length-1) == '.') { //remove trailing period from title
				title = title.substr(0, title.length-1);
			}
			strDebug += "OCLCrequested = '" + OCLCrequested + "\n"; //see leading comments before function
			strDebug += "json obj has O# == '" + OCLCnumber + "'\n";
			strDebug += "title=='" + title + "'\n";
	}	//end if

	var numHolders;
	try{	//check number of holders, see if need another run w/ new startLibrary value to get all of them
		numHolders = libraryObj.length;
	} catch(err){
		return;
	}

	strDebug += "numHolders=='" +numHolders+ "'\n";
	//alert(strDebug);

	var holdingsData = "";  //   *ForDisplay vars are what will show up on the HTML page
	var holdingsDataForDisplay = '<span class="apiDisplayHeader">OCLC holdings:&nbsp;</span>';
	var thisHolder = "";
	var thisHolderForDisplay = "";
	var institutionName = ""; var oclcSymbol 		= ""; var opacUrl 		= "";

	var targetLibraries = new Array(); //the ones we want to highlight
	var targetStyles = new Array(); //can set unique styles for each
		targetLibraries[0] = "Linda Hall Library";
			targetStyles[0] = '<span style="font-weight:bold; color:#303030; border:#303030 thin solid">';
		targetLibraries[1] = "Center for Research Libraries";
			targetStyles[1] = '<span style="font-weight:bold; color:#2d2d4d">';
		targetLibraries[2] = "[diagnostic message: 'Record does not exist']"; //API sends messages back when problems; see php
			targetStyles[2] = '<span class="apiNoRecordOrHolding">';
		targetLibraries[3] = "[diagnostic message: 'Holding does not exist']";
			targetStyles[3] = targetStyles[2];
		targetLibraries[4] = "[diagnostic message: 'Holding not found']";
			targetStyles[4] = targetStyles[2];

	for (var h = 0; h < numHolders; h++){ //build display and links for each of the holdings libraries
		/*
			2012-Aug-13
				sometimes we get no holdings back - examples in latest batch of LHL OCLC #s:
					- test: 10006518, 10013747, 10014696, 10027871, 10340117, 10340206
					- annoyingly, check any of these numbers in Connexion --> CtrlShiftA for View Holdings All:
						all have 1-3 holders (LHL, or LHL/CRL, or LHL/CRL/blstp in the sample above)
					- saved JSON result /OCLCapi/sample OCLC API responses/10340206-noHoldingsReturned.js
						- in that file, library object has 'diagnostic' object with members 'uri', 'details', 'message'.
						- 'message' is text "Holding not found"
						- http://oclc.org/developer/documentation/worldcat-search-api/error-statuses
						"Library Location requests for a record with no library holdings (or none at the default service level) -
						Identifier: info:srw/diagnostic/1/65
						Message: Holdings does not exist"
						- CRL is requesting with "serviceLevel=full" so that's not our problem
						- SOLUTION: check for valid
							- institutionName
							- oclcSymbol
							- opacUrl
							- IF not found, substitute some values so the items can be checked later
						- ORIGINAL CODE
								institutionName = libraryObj[h].institutionName;
								oclcSymbol 		= libraryObj[h].oclcSymbol;
								opacUrl 		= parseOpacUrl(libraryObj[h].opacUrl, OCLCnumber);
				2012-Aug-22
					A lot of these problems have been helped by changes in appConfigOCLCapi.php,
						which sleeps between bad requests but better yet will build some JSON from certain bad returned results
		*/

			if(libraryObj[h].institutionName){
				institutionName = libraryObj[h].institutionName;
			} else {
				institutionName = libraryObj[h].diagnostic.message;
			} strDebug += "institutionName=" +institutionName+ "\n";

			if(libraryObj[h].oclcSymbol){
				oclcSymbol 		= libraryObj[h].oclcSymbol;
			} else {
				oclcSymbol 		= "...";
			} strDebug += "oclcSymbol=" +oclcSymbol+ "\n";

			if (libraryObj[h].opacUrl){
				opacUrl 		= parseOpacUrl(libraryObj[h].opacUrl, OCLCnumber);
			} else {
				opacUrl 		= "http://oclc.org/developer/documentation/worldcat-search-api/error-statuses";
			} strDebug += "opacUrl=" +opacUrl+ "\n";
			//alert(strDebug);

			thisHolderNum		= h+1;

			thisHolder    	= thisHolderNum + ') ' +institutionName+ ' ' + '['+oclcSymbol+'].   ';
			if (h == numHolders-1) { //test for last value, style to showcase highest value of numHolders
				thisHolderForDisplay = ' <span class="highlightProcessing important">' +thisHolderNum+ '</span>';
			} else {
				thisHolderForDisplay = ' ' +thisHolderNum;
			}
			thisHolderForDisplay += ') ' +institutionName+ ' <a href="' +opacUrl+ '" target="_blank">[' +oclcSymbol+ ']</a>. ';

			for (var t=0; t < targetLibraries.length; t++){ //highlight holdings libraries that we're tracking
				if (institutionName == targetLibraries[t])
					thisHolderForDisplay =  targetStyles[t] + "&nbsp;" +thisHolder+ "&nbsp;</span>";
			}//end for targetLibraries

			holdingsData += thisHolder;
			holdingsDataForDisplay += thisHolderForDisplay;
	}//end for h: building data to be displayed


	/******************BGN DISPLAYING RETURNED DATA IN SPANS ON PAGE******************/
	try {	/* the real meat of the display section */


		//var dataResult = document.getElementById('dataResult' + OCLCnumber);

		var dataResult = $('#dataResult' + OCLCnumber);

		if (dataResult != null) {

			/*
			//THIS DIV IS DEAD
			$("#noDataResult" + OCLCnumber).removeClass("alert");
			$("#noDataResult" + OCLCnumber).html("");
			*/

				/* insert the new display text in the target div:
					when the OCLC API data IS one of the ones requested */
			$('#summaryBar' + OCLCnumber).append(': &nbsp;', title);
			$("#dataResult" + OCLCnumber).html(holdingsDataForDisplay);

		} /* 	NOW IF : OCLC number we sent is not the same as the one returned
							AND we haven't put data for returned OCLC number in mergedRecordsData
							then: insert the new display text in a different div */
			if( (OCLCrequested != OCLCnumber) && ($('#mergedRecordsData').text().indexOf(OCLCnumber) == -1 )){
				var originalColor = $('#dataResult' + OCLCrequested).css('background-color');
				var newDisplay = '<div class="apiDisplayBox">';
						newDisplay += '<div class="apiDisplayHeader">OCLC number&nbsp;';
								newDisplay += '<span>&nbsp;' +OCLCrequested+ '&nbsp;</span>: ';
								newDisplay += 'OCLC response is: <span class="apiMergeNumber">&nbsp;' +OCLCnumber+ '&nbsp;</span> ';
								newDisplay += ' "' +title+ '" ';
							newDisplay += '</div>';
							newDisplay += '<span style="background-color:' +originalColor+ '"></span>';
					newDisplay += '<div class="apiDataText" style="background-color:' +originalColor+ '">' +holdingsDataForDisplay  + '&nbsp;&nbsp;</div></div>';

				$('#mergedRecordsHeader').show();
				$('#mergedRecordsData').append(newDisplay);
				$("#noDataResult" + OCLCnumber).html("");
			}//end if we haven't listed the new data
		//} /* end else */
	} catch (err) {
		alert("libraryLocationHandler encountered a dataResult error:\n" + err);
		return;
	}
	/******************END DISPLAYING RETURNED DATA IN SPANS ON PAGE******************/



	/******************BGN FILLING FORM FIELDS WITH RETURNED DATA*********************/
		/*
			find the correct form in the document, save the JSON data in the form fields
			- the main form in the app is in saveAPIdataForm.php, it's called saveAPIdataForm
				- targetFormOriginalLength holds original number of elements in form with given id
				- tells us when it's ok to append new form fields (see 'merged record' in the function)
				- it's NOT SAME AS elementsLength BC WE'LL ADD FIELDS TO IT + put ourselves in endless loop */
	var allForms 									= document.forms;
	//var targetForm 								= document.getElementById("saveAPIdataForm");
	var targetForm 								= $("#saveAPIdataForm");
	//var targetFormOriginalLength 	= targetForm.elements.length;
	var targetFormOriginalLength 	= $("#saveAPIdataForm").length;

	for (var f=0; f < allForms.length; f++){
		strDebug += "\n f=" +f+ " has id: " + allForms[f].id + " begin: ";
			//in this clause, specify form ids we want to look at, ignore others
		if ((allForms[f].id != "saveAPIdataForm") && (allForms[f].id != "update" + OCLCnumber)) continue;

		var elementsLength = allForms[f].elements.length;
		for (var e=0; e < elementsLength; e++){
				//IF ... ADD NEW FORM FIELDS FOR REQUESTED OR MERGED RECORD
			if ((allForms[f].id == "saveAPIdataForm") && (e == targetFormOriginalLength-1)){
					/* conditions mean:
						- we're on form with id 'saveAPIdataForm'
						- are @ end of its original set of elements
						- and have not found field set matching returned OCLC number.
					this happens when OCLC number we submitted has been merged,
					but OCLC API only sends new (merged record's) data with no match point, no reference to old number we sent.
					- now, add a new field set to saveAPIdataForm, to hold returned data */

					//set itemIndex as whole number of just OCLC API-related fields
					 	/*var examineFormFields=""; //use this block to see all the hiddens
							for(var x=0; x<elementsLength; x++){
								examineFormFields += "elements[" +x+ "].name=val: '" +allForms[f].elements[x].name+ "=" +allForms[f].elements[x].value + "\n";
							} alert(examineFormFields); */
				var defaultFieldsLength 	= 3; 	//	subtract extra fields like emailAddressBox, fileNameBox, and the button
				var APIfieldsetLength 		= 6;	//  change if we start saving more fields of data from the API
				var itemIndex = ( (elementsLength - defaultFieldsLength) / APIfieldsetLength); //array syntax for NAME field: PHP uses them to build $dataBody when form submitted.

			/*	OCLC_NUMBER[] form fields are stored with array syntax,
						allowing PHP to loop through them,
						easily build $dataBody when form submitted.
					Javascript will fill TITLE[], numHolders[] and holdingsData[] fields.
					ID field is for the Javascript, NAME field is the array syntax that PHP uses.   			*/

				//var oclcField = '<hr class="first_date" />f=' +f+ '; elementsLength=' +elementsLength+ '; e=' +e+ '; itemIndex=' +itemIndex+ ')<input type="text" ';
				var oclcField = '<input type="hidden" ';
					oclcField +=  'value="' +OCLCnumber+ '"';
					oclcField +=  'name="OCLC_NUMBER['+itemIndex+']" id="OCLC_NUMBER'+OCLCnumber+'" />';
				$('#saveAPIdataForm').append(oclcField);

				var requestedOCLCfield = '<input type="hidden" name="OCLCrequested['+itemIndex+']" id="OCLCrequested'+OCLCrequested+'" ';
					if (OCLCnumber != OCLCrequested) { //save OCLCrequested if not same as OCLCnumber in json
						//alert("OCLCnumber ('" +OCLCnumber+ "') NOT EQ OCLCrequested ('" +OCLCrequested+ "')");
						requestedOCLCfield +=  'value="' +OCLCrequested+ '"';
						var mergeMsg = 'OCLC returned merged record data: ' +OCLCrequested+ '&nbsp;&gt;&gt;&nbsp;';
							mergeMsg += '<span class="apiMergeNumber">' +OCLCnumber+'</span>; see below, will be saved with ' +OCLCrequested+ ' in separate field.'
						$("#dataResult" + OCLCrequested).html(mergeMsg);
					} else {
						//alert("OCLCnumber ('" +OCLCnumber+ "') == OCLCrequested ('" +OCLCrequested+ "')");
						requestedOCLCfield +=  'value=""';
					}//end if/else OCLCrequested
					requestedOCLCfield +=  ' />';
				$('#saveAPIdataForm').append(requestedOCLCfield);

				var ISSNfield = '<input type="hidden" name="ISSN['+itemIndex+']" id="ISSN'+OCLCnumber+'" ';
					ISSNfield +=  'value="' +ISSN+ '" />';
				$('#saveAPIdataForm').append(ISSNfield);

				var titleField = '<input type="hidden" name="TITLE['+itemIndex+']" id="TITLE'+OCLCnumber+'" ';
					titleField +=  'value="' +title+ '" />';
				$('#saveAPIdataForm').append(titleField);

				var numHoldersField = '<input type="hidden" name="numHolders['+itemIndex+']" id="numHolders'+OCLCnumber+'" ';
					if (title.indexOf("diagnostic message") != -1) { //JSON WAS COMPILED BY applicationConfiguration: there is no record
						numHoldersField +=  'value="0" '; //THERE ARE NO HOLDERS
					}
					else if (oclcSymbol == "---") {
							//no holders were returned, but no diagnostic message (check libtype setting; or another error)
						numHoldersField +=  'value="0" '; //THERE ARE NO HOLDERS
					}
					 else { //THE NORMAL case
						numHoldersField +=  'value="' +numHolders+ '" ';
					}
					numHoldersField +=  '" />';
				$('#saveAPIdataForm').append(numHoldersField);

				var holdingsDataField = '<input type="hidden" name="holdingsData['+itemIndex+']" id="holdingsData'+OCLCnumber+'" ';
					holdingsDataField +=  'value="' +holdingsData+ '" />';
				$('#saveAPIdataForm').append(holdingsDataField);
			} /*
					end if adding MERGED RECORD (new) OCLC number's data to saveAPIdataForm form, in new fields
					- new data is added to the display below
				*/

		}//end for e
	}//end for f
	/******************END FILLING FORM FIELDS WITH RETURNED DATA*********************/

	//prompt(strDebug, strDebug);
	//buildMFHDrecord( OCLCnumber ); //defined earlier in this file: now called directly in checkDB
}
/****************************************************************************
	end function libraryLocationHandler
******************************************************************************/


</script>