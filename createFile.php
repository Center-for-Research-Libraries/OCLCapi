<!--begin include createFile.php-->

<div class="boxData" id="createFileStatus">
	<div class="pageHeader">Saved data results:</div>

<?php

$emailBody = "OCLC Library Location API data.\n";

	// Holdings.js sets cookie
$cookieEnableMessage = 	"This application requires cookies to be enabled.<br />Please check your browser settings and resubmit the data.";
$formDataNotSubmittedMessage = "There was a problem with the form data.";

	//make sure we have a cookie and the form submission
if (isset($_COOKIE["emailAddress"]) && isset($_REQUEST["emailAddressBox"])){
	prepareAPIdataToSave(); //fileFunctions.php
	$CSVfilename = composeTextFile(); 			//in fileFunctions.php; echoes $writeFileSuccessMsg + returns new file name
	sendEmail( $CSVfilename, "OCLC Library Location API file, .CSV: " ); //emailFunctions.php

	echo '<p>uncomment xls section to reinstate it.</p>';
	/*
		$XLSfilename = composeXLSfile(); 			//in fileFunctions.php; echoes $writeFileSuccessMsg and returns new file name
		//echo "<h2>XLSfilename='" . $XLSfilename . "'</h2>";
		sendEmail( $XLSfilename, "OCLC Library Location API file, .XLS: " ); //emailFunctions.php
	*/

} else { //handle various problems with error messages
		echo '<h3 class="alert">No file will be created, and no email will be sent.<br/>';
	if (isset($_COOKIE["emailAddress"]) != 1){ echo $cookieEnableMessage; }
	if (isset($_REQUEST["emailAddressBox"]) != 1){ echo $formDataNotSubmittedMessage; }
		echo "</h3>";
}//end else: we won't submit mail
?>

</div><!--#createFileStatus-->

<p>
	Some applications (Excel) may display international characters incorrectly. If using a CSV file, open it as UTF-8; On Windows, Arial Unicode MS font should work, or a font supporting UTF-8 encoding on your system.
	<br/>
	Fields in CSV or XLS file are separated by "pipe" or "vertical bar" character '<span class="highlightCode">&nbsp;|&nbsp;</span>', not by commas.
	<br/>
	In the results file, OCLC holdings information is represented in several columns:
	<ul>
		<li>allNames:</td><td>library names and codes, numbered, without line breaks</li>
		<li>allCodes:</td><td>library codes only, unnumbered, without line breaks</li>
	</ul>
</p>


<!--end include createFile.php-->