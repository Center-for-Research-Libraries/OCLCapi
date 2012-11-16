<?php
	//begin include emailFunctions.php


/************************************************************************
	sendEmail() and constructHeaders()
	use some sample code from
		http://www.finalwebsites.com/forums/topic/php-e-mail-attachment-script
	original saved in mailAttachmentSample.php
*/

global $emailAddress;
$emailAddress = "";

global $uid;
	$uid = "";

global $fileContent;
	$fileContent = "";

global $dataBody;
	$dataBody = "";

function setEmailAddress(){
	global $emailAddress;
	if(isset($_COOKIE['emailAddress'])) // Holdings.js sets cookie
		$emailAddress = $_COOKIE['emailAddress'];
	else
		$emailAddress = "";
	return $emailAddress;
}//end setEmailAddress


function constructDate(){		//construct a unique subject line using date info
	date_default_timezone_set( "America/Chicago" );

	$timestamp  = time(); //E_STRICT says use this instead of mktime()

		// getDate() returns associative array: indices are strings
	$dateInfo	= getDate( $timestamp );
	//print_r( $dateInfo );
	$strTimeDate = substr( $dateInfo["weekday"], 0, 3 ); //string, start position, length
	$strTimeDate .= " " . substr( $dateInfo["month"], 0, 3 );
	$strTimeDate .= " " . $dateInfo["mday"] . ", " . $dateInfo["year"];
	$strTimeDate .= "(" . $dateInfo["hours"] . ":" . $dateInfo["minutes"] . ")";

	return $strTimeDate;
}//end constructDate


function constructHeaders($filename){ 		// create email headers
	global $emailAddress, $dataBody, $uid, $fileContent;
	$emailHeaders = "From:IMLS Server <aelliott@crl.edu>\r\n";
	$emailHeaders .= 'Reply-To: ' . $emailAddress . "\r\n";
	$emailHeaders .= 'X-Mailer: PHP/' . phpversion();
  $emailHeaders .= "MIME-Version: 1.0\r\n";
  $emailHeaders .= 'Content-Type: multipart/mixed; boundary="' . $uid . '"' . "\r\n\r\n";
  $emailHeaders .= "This is a multi-part message in MIME format.\r\n";
  $emailHeaders .= "--" . $uid . "\r\n";
  $emailHeaders .= "Content-type:text/plain; charset=iso-8859-1\r\n";
  $emailHeaders .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
  $emailHeaders .= $dataBody . "\r\n\r\n";
  $emailHeaders .= "--"  . $uid . "\r\n";

  $emailHeaders .= "Content-Type: application/octet-stream; name=\"" . basename($filename) . "\"\r\n"; // use different content types here
  $emailHeaders .= "Content-Transfer-Encoding: base64\r\n";
  $emailHeaders .= 'Content-Disposition: attachment; filename="' . basename($filename) . '"' . "\r\n\r\n";

  $emailHeaders .= $fileContent."\r\n\r\n";
  $emailHeaders .= "--".$uid."--";

  return $emailHeaders;
}//end constructHeaders


/************************************************************************
	our sendEmail() uses some sample code from
	http://www.finalwebsites.com/forums/topic/php-e-mail-attachment-script
	original saved in mailAttachmentSample.php
*/
function sendEmail($filename, $emailSubject){
	global $dataBody, $emailAddress, $uid, $fileContent;

	$emailAddress = setEmailAddress();

	$email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
	if( !preg_match($email_exp, $emailAddress) || ($emailAddress == "")){
		$emailErrorMsg = "<h3 class='alert'>The email address ('" . $emailAddress . "') appears to be invalid.</h3>";
		echo $emailErrorMsg;
		return false;
	} //end if bad email

	if ($filename == ""){ //if we have no attachment
		$emailSubject .= " IMLS server mail " . constructDate();
	} else { //we have attachment
		$emailSubject .= basename($filename) . " " . constructDate();
	  $fileSize 		= filesize($filename);
	  $handle 			= fopen($filename, "r");
	  $fileContent 	= fread($handle, $fileSize);
	  fclose($handle);
	  $fileContent 	= chunk_split(base64_encode($fileContent));
	  //$name 				= basename($filename); //from example: not used
	  $uid 					= md5(uniqid(time()));
	}
	$emailHeaders = constructHeaders($filename);

	//echo "<h3 class='newData'>look for the file in your email as an attachment sent to " . $emailAddress . "<br/>";
	//echo " with subject " . $emailSubject . "<br/>";
	//echo "debugging: dataBody='" . substr($dataBody, 0, 500) . "'...<br/><br/>";
	//echo "debugging: emailHeaders='" . $emailHeaders . "'</h3>";

	$emailBody = "Data from the OCLC Library Locations API is in an attached file.";
	//$emailStatus = mail($emailAddress, $emailSubject, $dataBody, $emailHeaders);
	$emailStatus = mail($emailAddress, $emailSubject, $emailBody, $emailHeaders);

	$displayFilename = strrchr( $filename, "/");
	$displayFilename = str_replace( "/", "", $displayFilename);
	$statusMessage = "<div>The email message with attachment " . $displayFilename . " was sent ";
	if ($emailStatus == 1){
		//$statusMessage .= "Message status <span class='highlightNumbers'>&nbsp;'" . $emailStatus . "'&nbsp;</span> indicates success. ";
		$statusMessage .= "<span class='highlightNumbers'>successfully</span>.</div>";
	} else {
		$statusMessage .= "<span class='alert'>but there was a problem: '" . $emailStatus . "' </span></div>";
	}
	//echo $statusMessage;
}//end sendEmail


?>