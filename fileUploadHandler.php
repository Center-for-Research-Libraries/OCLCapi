<?php

/***********************************************************************

fileUploadHandler.php is include for fileUploadHandler.php; used to be independent and could do so now with link to processing page after upload success
- receives data from inputFileDataForm.php

About file uploading:

	fopen() doesn't actually read the contents of a file, only sets pointer to file, returns file handle.

	$fileHandle = fopen("includes/junk.txt", "r"); //r == read-only
	//print $fileHandle; // prints "Resource ID#10" number

	Mode 	Meaning
	r 	Read a file only. The pointer is set to the start of the file.
	r+ 	Read + write to a file. The pointer is set to the start of the file.
	w 	Write to a file only. It will erase the entire contents of the file you have open. If no file exists with your chosen name, then it will create one for you
	w+ 	Same as "w", but used to read + write.
	a 	Write to a file only, + Append data to the end of the file. Doesn't erase contents.
	a+ 	Same as "a", but with read access as well.
	x 	Create a file to write only. But gives you a special warning called E_WARNING.
	x+ 	Same as x but with read access as well.
	t 	In Windows, a line break is \r\n. The t converts \n line breaks created on other Operating Systems so that they are readable with Windows
	b 	Force PHP to open the file in binary mode.

	//////////////////////////////////////////////////////////////////////////////

	$_FILES associative array - where PHP stores all info about files

	2 elements of this array that we will need to understand for this example.
    * "filenameBox" is the reference we assigned in our HTML form
    * $_FILES["filenameBox"]["name"] - original path of the user uploaded file.
    * $_FILES["filenameBox"]["tmp_name"] - path to temporary file that resides on the server.
    The file should exist on the server in a temporary directory with a temporary name.

	$_REQUEST["MAX_FILE_SIZE"] is in the HTML form:
		http://www.php.net/manual/en/features.file-upload.post-method.php
		MAX_FILE_SIZE hidden field (measured in BYTES) must precede the file input field,
		and its value is the maximum filesize accepted by PHP.
		This form element should always be used as it saves users the trouble of waiting for a big file being transferred only to find that it was too large and the transfer failed. Keep in mind: fooling this setting on the browser side is quite easy, so never rely on files with a greater size being blocked by this feature. It is merely a convenience feature for users on the client side of the application. The PHP settings (on the server side) for maximum-size, however, cannot be fooled.

***********************************************************************/

$target_path 	= "../datasources/uploads/"; // Where the file is going to be placed
$filename 		= basename( $_FILES["filenameBox"]["name"]);


/* Add the original filename to our target path: Result is "datasources/uploads/filename.extension" */
$target_path .= $filename;

$fileinfo = "<p>Upload: " . $_FILES["filenameBox"]["name"] . "; ";
$fileinfo .= "Type: " . $_FILES["filenameBox"]["type"] . "; ";
$fileinfo .= "Size: " . ($_FILES["filenameBox"]["size"] / 1024) . " Kb. ";
$fileinfo .= "Next is save the file...; ";
$fileinfo .= "Stored in: " . $_FILES["filenameBox"]["tmp_name"] . "<br/>";
$fileinfo .= "Actual target path to uploaded file: '" . $target_path . "'; </p><hr/>";
//echo $fileinfo;

if ($_FILES["filenameBox"]["error"] > 0){ // fileFunctions.php --> generateFileUploadErrorMessage()
	echo "<h3 class='alert'>Error: " . generateFileUploadErrorMessage($_FILES["filenameBox"]["error"]) . "<br />";
} else { //was good
	?>
		<script language="javascript"type="text/javascript">
			// code setting classes on these form elements: inputFileDataForm.php, fileUploadHandler.php, queryAPIfromFile.php, queryAPIfromText.php
			$("#uploadSpan").removeClass("alert"); //these are in inputFileDataForm.php
			$("#submitFileBtn").removeClass("actionButton");
		</script>
	<?php
}//end if-else

if( move_uploaded_file($_FILES["filenameBox"]["tmp_name"], $target_path) ) {
    echo "<h3 class='highlightCode'>The file <a href='" . $target_path . "'>" .  $filename . "</a> has been uploaded.</h3>";
} else {
    echo "<h3 class='alert'>There was an error moving the uploaded file " .  $filename . " , please try again!</h3>";
}
?>

<!--end include fileUploadHandler.php-->