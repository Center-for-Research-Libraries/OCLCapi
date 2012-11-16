<!--begin include queryAPIfromText.php-->
<?php
/*
	queryAPIfromText.php
		- Reads OCLC numbers (submitted via form input) into array OCLCnums
		- depends on appConfigOCLCapi.php for insertAPItargetDiv(), getJSONfromOCLC(), and global vars
		- Uses insertAPItargetDiv() to add a div to the page for each in OCLCnums
		- Uses getJSONfromOCLC() to call OCLC Library Location API for Holdings, and get results into a JSON object
		- getJSONfromOCLC() uses Holdings.js to display JSON results in the div put there by insertAPItargetDiv()


	- calls PHP function to get JSON-P results
		from OCLC Library Location API for Holdings information.

	See queryAPIfromFile.php for the version that pulls OCLC numbers from a file and queries OCLC API
	See queryAPIfromDB.php for "" from mySQL database "".
*/
?>
<script language="javascript" type="text/javascript">
	// code setting classes on these form elements: inputFileDataForm.php, fileUploadHandler.php, queryAPIfromFile.php, queryAPIfromText.php
	$("#uploadSpan").removeClass("alert");
	$("#submitFileBtn").removeClass("actionButton");
</script>

<?php

if (isset($_REQUEST["OCLC_NUMBERlist"])){
		// split input into array, delimited by any number of commas, periods, space + other chars, including " ", \r, \t, \n and \f
	$OCLCnums 	= preg_split( "/[\s,.|]+/", trim($_REQUEST["OCLC_NUMBERlist"]) );
	$OCLClength = count($OCLCnums);
} else {
	$OCLCnums = "";
	$OCLClength = 0;
}


if ( $OCLClength > 0 ){ // include submittable form and divs to fill with API data
	include("goToSubmit.php");  // link to submit button at bottom
	insertAPItargetDiv($OCLCnums); 			// one div for each OCLCnums
	include("saveAPIdataForm.php"); //include submittable form
	for($i = 0; $i < $OCLClength; $i++){ // call OCLC and update submittable form's data
		getJSONfromOCLC(trim($OCLCnums[$i]));
	}//end for
}//end if OCLClength > 0

?>

<!-- //end queryAPIfromText.php -->

