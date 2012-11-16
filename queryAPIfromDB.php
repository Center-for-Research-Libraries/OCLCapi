<!--begin include queryAPIfromDB.php-->
<?php
	/*
		queryAPIfromDB.php
		Pulls information from mySQL database to query OCLC Library Location API for Holdings information.
		- OCLC numbers are pulled from database table
		- Uses JavaScripts in Holdings.js to query OCLC Library Location API for Holdings information.
	*/

	// Make + Run the query.
mysqli_query($databaseConnection, "SET character_set_client=utf8");
mysqli_query($databaseConnection, "SET character_set_connection=utf8");

	// get the numbers that will be queried
$sourceTable = "TEST_HOLDINGS";
$sourceQuery = 'SELECT OCLC_NUMBER FROM ' . $sourceTable . ' ';
$sourceQuery .= 'WHERE OCLC_NUMBER IN ( 807527, 819064 );';

$sourceResult = mysqli_query($databaseConnection, $sourceQuery) or die( mysqli_error($databaseConnection) );

if (! $sourceResult ) echo "<h4>query problem</h4>";

if ($sourceResult) { // If it ran OK, display the records.
	//echo "<h4>query ok, while is next</h4>";
	$datasource = "database";

	// Fetch all the OCLC numbers, put them in a new list of OCLC numbers to be submitted
$OCLCnums = "";
	while ($row = mysqli_fetch_array($sourceResult, MYSQLI_ASSOC)) {
		if (isset($row["OCLC_NUMBER"])){
			$OCLCnums .= trim($row["OCLC_NUMBER"]) . ",";
		} else {
			$OCLCnums .= "no data in else";
		}//end if-else
	}//end while
}//end if query went fine

$OCLCnums 	= preg_split("/[,]+/", $OCLCnums); //now it's an array
$OCLClength = count($OCLCnums);

for($i = 0; $i < $OCLClength; $i++){ //remove any weird elements
	if (! $OCLCnums[$i]){
		 unset($OCLCnums[$i]);
	} //end if
}//end for

$OCLClength = count($OCLCnums);

if ( $OCLClength > 0 ){ // include submittable form and divs to fill with API data
	include("goToSubmit.php");  // link to submit button at bottom
	insertAPItargetDiv($OCLCnums); 			// one div for each OCLCnums
	include("saveAPIdataForm.php"); //include submittable form
	for($i = 0; $i < $OCLClength; $i++){ // call OCLC and update submittable form's data
		getJSONfromOCLC(trim($OCLCnums[$i]));
	}//end for
}//end if OCLClength > 0

?>

<!--end include queryAPIfromDB.php-->