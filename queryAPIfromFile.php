<!--begin include queryAPIfromFile.php-->
<?php
/*
	queryAPIfromFile.php
		- Reads OCLC numbers from a text file into array OCLCnums
		- fileUploadHandler.php is included: accepts the $_FILES[] submission and puts it up on server
		- depends on appConfigOCLCapi.php for insertAPItargetDiv(), getJSONfromOCLC(), and global vars
		- Uses insertAPItargetDiv() to add a div to the page for each in OCLCnums
		- Uses getJSONfromOCLC() to call OCLC Library Location API for Holdings, and get results into a JSON object
		- getJSONfromOCLC() uses Holdings.js to display JSON results in the div put there by insertAPItargetDiv()
*/

include("fileUploadHandler.php");
global $fileuploadpath, $filename;

$filepath 		= $fileuploadpath . $filename;	//relative to web root: $filename already set in fileUploadHandler.php
$fullFilePath = "/var/www/html/" . $filepath; //absolute from system root
$filelocation = "/datasources/uploads/" . $filename; //relative to where this PHP file is

$OCLCnums = file($fullFilePath); //file(file name) reads file into array
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
<!-- //end queryAPIfromFile.php-->




