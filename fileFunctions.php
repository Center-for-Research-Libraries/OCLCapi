<?php

/*********************************************************

	AJE 7/21/2011
	functions related to file input-output all belong here.

	first declare our globals: files + directories can be reset in php calling file as needed:
		see wgetterUSND1690.php for examples

*/

$dirsList		= fill_dirsList(); 					//array of directories
$indir	 		= setDirpath("./", "in");		//directory for input
$outdir 		= setDirpath("./", "out");	//directory for output

$infile			= setFilepath("defaultInput.txt", "in");	//file for input
$bUseInputFile = "FALSE";

$outfile		= setFilepath("defaultOutput.txt", "out");	//file for output
$logfile		= setFilepath("defaultLog.txt", "out");
$failureLog	= setFilepath("defaultLog_FAILED.txt", "out");

$lineArray 		= array();	//contents depend on file
$numLines 		= 0;				//lineArray length
$targetLines 	= array();  //fill it in the file that needs it
$numTargets 	= 0;				//targetLines length

$files_to_search = $infile;	//file targets for search and replace ops
$search_string  = array();  //what to find
$replace_string = array();	//what to change it to

function readFileLinesIntoArray( $fileHandle, $inputArray ){
	while (!feof($fileHandle)) { //feof - file, end of file
		$line_of_text = fgets($fileHandle);
		array_push( $inputArray, $line_of_text );
	}//end while
	return $inputArray;
}//end readFileLinesIntoArray



/*******************************************************************/
function checkForFileExistence( $filename ){
	if (file_exists($filename)) {
    return 1;
	} else {
	  return -1;
	}
}//end checkForFileExistence


/*******************************************************************/
function appendFile( $filename, $filedata ){
	global $readFileOpenError, $writeFileOpenError, $writeFileWritingError, $logfile;
	//echo "appendFile( '" . $filename . "', '". $filedata . "')...\nlogfile == '" . $logfile . "'\n";
	if ($filename == "") $filename	 = $logfile;
	$mode 			= "a"; //Open for writing only; place file pointer @ end of file. If file does not exist, attempt to create
	$filehandle = fopen( $filename, $mode ) or die( str_replace("REPLACE_DUMMY_STRING", $filename, $writeFileOpenError) );
	$numBytes 	= fwrite( $filehandle, $filedata ) or die( str_replace("REPLACE_DUMMY_STRING", $filename, $writeFileWritingError) . "\n" . $filedata );
	fclose( $filehandle );
}//end appendFile


/*******************************************************************/
function fill_dirsList(){
	/*
	PHP manual:
		array scandir ( string $directory [, int $sorting_order = 0 [, resource $context ]] )
		Returns an array of files and directories from the directory.
	*/

	global $dirsList;
	$dirsList 	= scandir("/var/www/html/datasources/");
	$dirsCount 	= count( $dirsList );
	$strDebug = "fill_dirsList: has $dirsCount files and directories<br/>";
	for ($i = $dirsCount; $i >= 0; $i--) { //remove file references
		$bIsDirectory = is_dir( $dirsList[ count( $dirsList )-1 ] );
		$strDebug .= '<h2>i = ' . $i . ') ' . $dirsList[ count( $dirsList )-1 ];
		$strDebug .= ' is dir? ' . var_export( $bIsDirectory, TRUE ) . ' ';
		if ( ! $bIsDirectory  ) {
			array_pop( $dirsList );
			$strDebug .= ' : popped!';
		}//end if
		$strDebug .= '</h2>';
	}//end for
	//echo $strDebug;
	return $dirsList; //no need to return bc global, but this makes it useable elsewhere
}//end fill_dirsList



/********************************************************************
	setDirpath is to set a directory path on the server for input or output files
*/
function setDirpath( $pathParam, $dirParam ){
	$path = "";
	$strDirpath = "\nsetDirpath( '" . $pathParam . "', '" . $dirParam . "')\n";
	if ($dirParam == "in"){
		if (isset($_REQUEST["indir"]) && ($_REQUEST["indir"] != "")) $path =  './' . $_REQUEST["indir"];
		else $path = $pathParam; //no form data
	} else if ($dirParam == "out"){
			if (isset($_REQUEST["outdir"]) && ($_REQUEST["outdir"] != "")) $path =  './' . $_REQUEST["outdir"];
			else $path = $pathParam;
	} else $path = $pathParam;
	$strDirpath .= " --> setDirpath == '" . $path ."'\n";
	//echo $strDirpath;
	//appendFile('./appConfigLog.txt', $strDirpath);
	return $path;
}//end setDirpath



/*******************************************************************
	$argc == number of arguments passed to script: use to see if script called with parameter
	$argv == array of all arguments passed to script when running from command line.
	$argv[0] always == name used to run script.
*/
function setFilepath( $pathParam, $dirParam ){
	global $indir, $outdir;

	if ( isset($_SERVER['argc']) && $_SERVER['argc'] >= 1 ) {
	  $argc = $_SERVER['argc'];
	} else $argc = -1;

	if ( isset($_SERVER['argv']) && $_SERVER['argv'] >= 1 ) {
	  $argv = $_SERVER['argv'];
	} else $argv = -1;

	$strFilepath = "\nsetFilepath( '" . $pathParam . "', '" . $dirParam . "')... argc=='" . $argc . "' ";

	if ($argc > 1){ //use command line args
		if ($argv[1] != ""){
			$path 		= $indir . $argv[1];
			$strFilepath .= "argv[1]=='" . $argv[1] . "' ";
		}
		if ($argv[2] != ""){
			$outfile 	= $outdir . $argv[2];
			$strFilepath .= "argv[2]=='" . $argv[2] . "' ";
		}
	} else { //just have argc[0]
		if ($dirParam == "input") 			$path = $indir . $pathParam;
		else if ($dirParam == "output") $path = $outdir . $pathParam;
		else 														$path = $pathParam;
	}
	$strFilepath .= " --> setFilepath == '" . $path ."'\n";
	//echo $strFilepath;
	//appendFile('./appConfigLog.txt', $strFilepath);
	return $path;
}//end setFilepath


/*******************************************************************
	fillFileArrays sets array contents and counts
	- utility for functions that read $infile and use $targetLines
*/
function fillFileArrays(){
	global $infile, $lineArray, $numLines, $targetLines, $numTargets;
	echo "\nfillFileArrays: infile is '" . $infile . "'\n";
	$lineArray 		= file($infile);
	$numLines 		= count($lineArray);
	$numTargets 	= count($targetLines);
	echo "\nfillFileArrays: numLines='" . $numLines . "'; numTargets='" . $numTargets . "'\n";
}//end fillFileArrays





/*******************************************************************
	extractTargetedLines opens files, ingests contents into array,
		goes through each line, checks for target string, saves that line to $outfile
	calling script will specify the files to open and targets to be found
*/
function extractTargetedLines(){
	global $indir, $outdir, $infile, $bUseInputFile, $outfile, $separator;
	global $logfile, $failureLog, $sampleURL, $wgetWait;
	global $numStartQuery, $numQueryLimit;
	global $lineArray, $numLines, $targetLines, $numTargets; //array, values are set in calling script

	$strDebug = $separator . "extractTargetedLines()\n";

	fillFileArrays(); //sets array contents and counts

	$strDebug .= "\nextractTargetedLines has input from: " . $infile . ", has " . $numLines . " lines\noutput to: " . $outfile . "\n...next is loop\n";
	echo $strDebug;
	//appendFile( $logfile, $strDebug );

	for($c = 0; $c < $numLines; $c++){
		$data = "";
		$strDebug = "\nline " . $c . ") " . $lineArray[$c];
		//echo $strDebug;
		for($p = 0; $p < $numTargets; $p++){

			/*
			//DEBUGGING
			$tPos = strpos($lineArray[$c], $targetLines[$p]);
			$bPos = ($tPos > -1);
			echo "\n\tposition of '" . $targetLines[$p] . "' in '" . $lineArray[$c] . "' is '" . $tPos . "', and bPos is now '" . $bPos . "'";
			//UNCOMMENT FOR DEBUGGING
			*/

			if (strpos($lineArray[$c], $targetLines[$p]) != 0){ //USE THIS CONDITION IN LCchonAmerica/extractGenealogy functions
			//if (strpos($lineArray[$c], $targetLines[$p]) != 0){ //USE THIS CONDITION IN LCchonAmerica/extractHoldings functions
			//if (strpos($lineArray[$c], $targetLines[$p]) > -1){ //USE THIS CONDITION IN WNA/extractPubIDs: THIS MIGHT BREAK THE LCchonAmerica/extractHoldings functions
				$strDebug .= "target " . $p . ", " . $targetLines[$p]. " FOUND IN: " . $lineArray[$c] . "\n";
				echo $strDebug;

				//$data = trim($lineArray[$c]) . "\n";
				$data = $lineArray[$c];
				$data = preg_replace( '/ +/', ' ', $data); //this doesn't actually work to replace multiple spaces

				/*****************************************************************
				//force reading ahead to multiple lines of data (all data between targeted lines)
					- this section was pretty specialized for extractHoldings_step1.php but as long as nothing else matches readingBgnTag etc, is fine to use
					- for extractHoldings,
				*/

					/*   //to get HOLDINGS dates
					$readingBgnTag = "<ul class=";
					$readingEndTag = "</ul>";
					*/

				/*   //to get GENEALOGY info   */
				$readingBgnTag = $targetLines[$p];
				$readingEndTag = "</datafield>";


				$bgnTagPos = strpos($lineArray[$c], $readingBgnTag );
				$strDebug .= "\tbgnTagPos start ='" . $bgnTagPos . "' in lineArray[" . $c . "] '" . $lineArray[ $c ] . "'  \n";
				if ( $bgnTagPos > -1 ){ //force reading ahead for data between start and end strings
					$h = $c + 1; // next line index
					$strDebug .= "\nINTO THE IF with lineArray[h=" . $h . "], value:'" . $lineArray[$h] .  "'\n";

					do {
						$data .= $lineArray[$h];
						$endTagPos 	= strpos($lineArray[$h], $readingEndTag);
						$strDebug .= "\n\t\tDO - WHILE in lineArray[h=" . $h . "],\t data=" . $data . ",\t...endTagPos=" . $endTagPos;
						if ( $endTagPos > -1 ){ //we found readingEndTag
							$h = $numLines+1; //exit loop on next check
							$strDebug .= "\nendTagPos matched empty string: Now exit while: h=" . $h . ", numLines=" . $numLines . ", data='" . $data . "',\t...";
							$c++;	//advance outer loop to get past targeted data area + continue with rest of file
						}
						echo $strDebug;
						//appendFile( $logfile, $strDebug );
						$h++;
					} while ($h < $numLines); //end do-while
					$strDebug .= "\nending the IF h=" . $h . ",\t data='" . $data . "',\t...";
				}//end if
				/*
				//force reading ahead for data between start and end strings
				*****************************************************************/


			//takeNap( 2 );
			//appendFile( $logfile, $strDebug );
			}//end if targetLines found
		}//end for each item in targetLines array

		$lineLength = strlen($data);
		echo "\nline " . $c . ") " . $data;
		if (($data != "") && ($lineLength != 0)){
			appendFile( $outfile, $data );
		}

		//appendFile( $logfile, $strDebug . $separator );

	}//end for each line infile
	echo $separator . $strDebug;
}//end extractTargetedLines()


/*******************************************************************
	receives string of data from deleteTargetedStrings or elsewhere,
		performs a bunch of replacements and returns the string
*/
function replaceTargetedStrings($data){
	global $logfile;

	//$blort = "\nreplaceTargetedStrings( '" . $data . "') \n";

	$data = str_replace("\t", "", $data);
	$data = str_replace("           ", "", $data);
	$data = str_replace("          ", "", $data);
	$data = str_replace("      ", "", $data);

/*
		//MarcEdit will choke on the < and > so we'd just have to replace them again anyway
	$data = str_replace("&lt;", "<", $data);
	$data = str_replace("&gt;", ">", $data);
*/

	$data = str_replace('" content="', ' = ', $data);
	/*
		$blort .= "\nnow data ='" . $data . "'\t";
		$junkRep = str_replace("&lt;", "<", $data);
		$blort .= "if we do a replace it looks like '" . $junkRep . "'\n";
		appendFile($logfile, $blort);
	*/

	return $data;
}//end replaceTargetedStrings



/*******************************************************************
	- find, extract and capitalize the HOLDING LIBRARY CODE
	 -receives a string like
	 		<a href="/institutions/exg/titles/">View more titles from this institution
	 	where 'exg' is the code
*/
function extractHoldingLibraryCode($data){
	$instHref = '<a href="/institutions/';
	$start 	 = strpos($data, $instHref) + strlen($instHref);

	$libCode = "[code:";
	$libCode .= strtoupper(substr($data, $start, 3)); //pull the actual code
	$libCode .= "]";

	echo "\n" . $libCode . "\n";
	return $libCode;
}//end extractHoldingLibraryCode





/*******************************************************************
	deleteTargetedStrings opens files, ingests contents into array,
		goes through each line, checks for target string,
		REMOVES IT from the line, saves that line to $outfile
	calling script will specify the files to be searched and targets to be found
*/
function deleteTargetedStrings(){
	global $indir, $outdir, $infile, $bUseInputFile, $outfile, $separator;
	global $logfile, $failureLog, $sampleURL, $wgetWait;
	global $numStartQuery, $numQueryLimit;
	global $lineArray, $numLines, $targetLines, $numTargets;//array, values are set in calling script

	$strDebug = $separator . "deleteTargetedStrings()\n";

	fillFileArrays(); //sets array contents and counts

	$strDebug .= "\ndeleteTargetedStrings input from: " . $infile . ", has " . $numLines . " lines\noutput to: " . $outfile . "\n...next is loop\nnumTargets is " . $numTargets . "\n";
	echo $strDebug;
	appendFile( $logfile, $strDebug );

	for( $c = 0; $c < $numLines; $c++ ){
		$strDebug 	= "\nline " . $c . ") " . $lineArray[$c];
		echo "line " . $c . ") of " . $numLines . "...\n";
		$data 			= $lineArray[$c];

/*
$data = preg_replace("/[\n\r]/", "", trim($data) );
$data = preg_replace("/\n/", "", trim($data) );
$data = preg_replace("/\r/", "", trim($data) );
if ($data === "") continue;
*/


			//before further manipulation, check for holdings library code and get it
		if (strpos($data, '<a href="/institutions/') > -1){
			$data = extractHoldingLibraryCode($data);
		}//end if institutions/holdings library code

		$thisTarget = 0;
		while($thisTarget < $numTargets){
			$strDebug .= "\ttarget " . $thisTarget . ", " . $targetLines[$thisTarget];
			$pos			= strpos($data, $targetLines[$thisTarget]);

			if ($pos !== false){
				$strDebug .= " FOUND IN: " . $data . "\n";
				$data = str_replace( $targetLines[$thisTarget], "", $data); //replace with nothing
				$strDebug .= "new line is " . $data;
				echo $strDebug;
			}//end if targetLines found
			$thisTarget++;

		}//end while

		if ($data != $lineArray[$c]){ //there was a change, save it
			$data = trim( $data );
			$data = replaceTargetedStrings($data);
			appendFile( $outfile, $data . "\n" );
		} else {
			appendFile( $outfile, $lineArray[ $c ] );
		}
		appendFile( $logfile, $strDebug . $separator );
	}//end for each line infile
	//takeNap( 2 );
	appendFile( $outfile, $separator );
	echo $separator . $strDebug . $separator;

}//end deleteTargetedStrings



/*******************************************************************
	freeSystemMemory: experiment to try to get around memory buffer overload errors.
		never did work correctly as of 9/23/2011
*/
function freeSystemMemory(){
	$systemCall = "sync && echo 3 > /proc/sys/vm/drop_caches";
	echo "\nfreeSystemMemory will call: \t'" . $systemCall . "'\n";
	exec($systemCall);
}//end freeSystemMemory





/*******************************************************************
	processSearchReplace
	- calling script will fill arrays search_string + replace_string
	- those are global, defined at top of this file
	- input param $bUseRegex: Boolean, whether to use Regular Expressions in search/replace

	example from http://www.codediesel.com/php/search-replace-in-files-using-php/
		- also http://pear.php.net/manual/en/package.filesystem.file-searchreplace.intro.php
		- see formatHoldings1_do866withArrays.php in backups directory
				// string to search
			$snr -> setFind( "Er") ;
			// string to find
			$snr -> setReplace( "Sie") ;
*/
function processSearchReplace($bUseRegex){
	global $search_string, $replace_string, $files_to_search, $logfile, $separator;
	/* old strategy: outdir is the directory, every file in there will have the changes made to it
	$snr = new File_SearchReplace($search_string, $replace_string, $files_to_search, $outdir, false);
	*/
	$psrDebug = "\nprocessSearchReplace: will run thru " . count($replace_string) . " replacements...\n";
	echo $psrDebug;
	for ($t = 0; $t < count($search_string); $t++){
			//empty parameter in constructor is where the directory would go
		$psrDebug = "\n\t" . $t . ")next pHSR '" . $search_string[$t] . "' --> '" . $replace_string[$t] . "'\n";
		$snr = new File_SearchReplace($search_string, $replace_string, $files_to_search, '', false);
		$snr->setFind($search_string[$t]);
		$snr->setReplace($replace_string[$t]);
		if ($bUseRegex){
			$snr->setSearchFunction("preg"); //use regex
		}
		$snr->doSearch();
		$psrDebug .= "\n\t'" . $search_string[$t] . "' --> '" . $replace_string[$t] . " \n\tdone " . $snr->getNumOccurences() . " times\n";
		echo $psrDebug;
		//appendFile($logfile, $psrDebug);
		unset($snr); //free up memory
	}//end for t - each search/replace pair

	//freeSystemMemory(); //fails with msg: "sh: /proc/sys/vm/drop_caches: Permission denied"

}//end processSearchReplace






/********************************************************************************
	begin functions related to saving OCLC API data to email or file
*/
global $dataBody;
	$dataBody = "";
global $readFileOpenError, $writeFileOpenError, $writeFileWritingError;
global $writeFileSuccessMsg;


function resetErrorMessages(){
	global $readFileOpenError, $writeFileOpenError, $writeFileWritingError;
	$readFileOpenError 	= "\nCouldn't open REPLACE_DUMMY_STRING for reading!\n";
	$writeFileOpenError = "\nCouldn't open REPLACE_DUMMY_STRING for writing!\n";
	$writeFileWritingError = "\nError writing to REPLACE_DUMMY_STRING !\n";
}//end resetErrorMessages



function replaceDoubleSpaces( $strInput ){ //replace all double spaces with single spaces
	$strInput = preg_replace('#[\s]{2,}#', " ", $strInput, -1, $junk);
	while (strpos($strInput, "  ") > 0) {
		$strInput = preg_replace('#[\s]{2,}#', " ", $strInput, -1, $junk);
	}//end while: done with spaces
	return $strInput;
}//replaceDoubleSpaces



/*******************************************************************
	prepareAPIdataToSave will format data submitted from form:
		- sets up the contents of global $dataBody
		- this is email body, or main contents of .csv/.txt/.xls file
		- composeTextFile and composeXLSfile all use $dataBody
*/
function prepareAPIdataToSave(){
	global $dataBody;
	$strDebug = "prepareAPIdataToSave<br/>";

		//arrays storing the form data from each of the arrayed form fields
	$formOCLC_NUMBERS 	= $_REQUEST["OCLC_NUMBER"];
	$formOCLCrequested 	= $_REQUEST["OCLCrequested"];
	$formISSN						= $_REQUEST["ISSN"];
	$formTITLES				 	= $_REQUEST["TITLE"];
	$formNumHolders 	 	= $_REQUEST["numHolders"];
	$formHoldingsData 	= str_replace("|", "", $_REQUEST["holdingsData"]); //remove our field separator from the data: ruins formatting

	//column titles in first line
	//$dataBody = "OCLC_NUMBER|OCLCalternate|ISSN|title|numHolders|CRLholds|";//see new columns 2012-08-14
	$dataBody = "OCLC_NUMBER|OCLCalternate|ISSN|title|numHolders|CRLholds|memberHolds|";
		//2011: now there will be 4 styles of holdingsData for various uses in XLS, MDB, etc.
		//2012-07-20: nobody needs allNamesList and allCodesList variants with line breaks; skip them
	$dataBody .= "allNames|"; 		//all data, no line breaks
		//$dataBody .= "allNamesList|"; //all data, add line breaks; see thisAllNamesList in code below
	$dataBody .= "allCodes|"; 		//library codes only, no line breaks; see thisAllCodes in code below
		//$dataBody .= "allCodesList|"; //library codes only, add line breaks;
	$dataBody .= "memberNames|memberCodes|memberData|";
	$dataBody .= "\n";
	//end column titles

	$i = 0;
	foreach($_REQUEST["OCLC_NUMBER"] as $OCLC_NUMBER){
		/*$strDebug .= "<p>i='" . $i . "': " . $OCLC_NUMBER . " has title '" . $formTITLES[$i] . "', ";
			 $strDebug .= "with " . $formNumHolders[$i] . " holders, ";
			 $strDebug .= "details are <span class='important'>" . $formHoldingsData[$i] . "</span>";
		echo $strDebug;
		*/

			/*
				- $_REQUEST["OCLC_NUMBER"] is array of OCLC numbers returned by API
				- $_REQUEST["OCLCrequested"] is array of OCLC numbers submitted to API
				- OCLC_NUMBER and OCLCrequested may be different if API returned data for merged number
			*/
		$thisRecord = trim($OCLC_NUMBER) . "|";
		if(trim($formOCLCrequested[$i])){
			$thisRecord .= trim($formOCLCrequested[$i]) . "|";
		} else { // $formOCLCrequested[$i] was blank
			$thisRecord .= "0|";
		}

		$thisRecord .= trim($formISSN[$i]) . "|"; //Holdings.js function parseISSN fills these so no need to handle blanks here

		$thisTitle 	= trim($formTITLES[$i]);
		//echo "<h3>thisTitle='" . $formTITLES[$i] . "'<br/> + is in the encoding '" . mb_detect_encoding($thisTitle) . "'</h3>";

		$thisTitle = str_replace( ",", " ", $thisTitle);
		$thisRecord .= $thisTitle . "|";

		$thisRecord .= trim($formNumHolders[$i]) . "|";  //original line; just add number from form data

			//bgn CRLholds field: does CRL hold this item?
		$boolCRLholds 			= 0;
		$CRLstrpos = strpos($formHoldingsData[$i], "Center for Research Libraries [CRL]");
		if ($CRLstrpos > -1){
			$boolCRLholds = 1;
		}
		$thisRecord .= $boolCRLholds . "|";
		//echo "<br/>after boolCRLholds, thisRecord = " . $thisRecord;
		//end CRLholds field


		/*
			fields: memberHolds, memberNames, memberCodes, memberData
				all need to be filled in from the mySQL interface, not here: leave message to that effect.
				Defined here, ea. is added to $thisRecord in order they appear in database (see column titles at beginning of function)
		*/
			$memberHolds = "0|";
			$memberNames = "[member names]|";
			$memberCodes = "[member codes]|";
			$memberData = "[member data]|";
		$thisRecord .= $memberHolds;

			//allNames: all data, no line breaks, straight holdings data as submitted
		$thisHoldingsData = trim($formHoldingsData[$i]);
		$thisHoldingsData = str_replace( ",", "", $thisHoldingsData);
		$thisHoldingsData = replaceDoubleSpaces($thisHoldingsData);
		$thisRecord .= $thisHoldingsData . "|";
		//echo '<h3>thisHoldingsData = "' . $thisHoldingsData . '"</h3>';
		//echo '<h3>thisHoldingsData is in the encoding "' . mb_detect_encoding($thisHoldingsData) . '"</h3>';
		//end allNames field


		/* this section of the file has been removed: see allNamesList_unused.php
			- no longer providing allNamesList column */


		/* allCodes: library codes only, no line breaks
			regex: PCRE in PHP manual: Perl-Compatible Regular Expressions
			\w 	 <-- means any "word" character (letter or digit or underscore)

			regexCodesWrap + preg_replace:
				strips holdings library names, leaves brackets + codes
				'#[0-9]+\) [A-Za-z0-9\s\w\&\%\$\#\@\-\.\:\"\'\(\)]*#'

			first, replace all the accented characters with non-accented equivalents,
					so regexCodesWrap will match the entire repository names: see
					http://php.net/manual/en/function.strtr.php
						$codes = strtr($thisHoldingsData, "äåö", "aao");
					but user Anonymous 25-Nov-2009 08:09 posted even simpler solution (with our vars plugged in):
						$codes = preg_replace("/[^\x9\xA\xD\x20-\x7F]/", "", $thisHoldingsData);
		*/
		$regexAccentedChars = '#[^\x9\xA\xD\x20-\x7F]#';
		$codes = preg_replace($regexAccentedChars, "", $thisHoldingsData); //replace accented characters
		$regexCodesWrap = '#[0-9]+\) [\s\w\&\%\$\#\@\-\.\:\"\'\(\)]*#';
		$codes = preg_replace($regexCodesWrap, "", $codes, -1, $matchCodes);
		$regexBracket = '#\]\.#';
		$codes = preg_replace($regexBracket, "] ", $codes, -1, $matchBracket);
		$thisAllCodes = trim($codes);
		$thisAllCodes = replaceDoubleSpaces($thisAllCodes);
		$thisRecord .= $thisAllCodes . "|";
		//end allCodes field

		/* this section of the file has been removed: see allCodesList_unused.php
			- no longer providing allCodesList column */

			//add dummy columns that need to be filled depending on the returned data:
			//	that's done in the database
		$thisRecord .= $memberNames . $memberCodes . $memberData;

		if ($thisRecord != "|||") $dataBody .= $thisRecord . "\n"; //if not empty record

		$i++;
	}//end foreach

	//echo $strDebug . "<p>dataBody:<br/>" . str_replace("\n", "<br/>", $dataBody) . "</p><hr/>";
}//end prepareAPIdataToSave


/*******************************************************************
	generateFileUploadErrorMessage builds string explaining why file upload failed.
		- after: 	http://www.php.net/manual/en/features.file-upload.errors.php
			where it's called file_upload_error_message()
*/
function generateFileUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'Uploaded file exceeds the upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'Uploaded file exceeds the MAX_FILE_SIZE directive (<span class="highlightCode">' . $_REQUEST["MAX_FILE_SIZE"] . ' bytes</span>) in the HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'Uploaded file was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload stopped by extension';
        default:
            return 'Unknown upload error';
    }
}//end generateFileUploadErrorMessage



/*******************************************************************
	generateSuccessMessage builds string to confirm that txt/csv or xls file went ok
*/
function generateSuccessMessage($displayFilename, $filename){
	global $writeFileSuccessMsg, $dataBody;
	global $emailAddress; //emailFunctions.php

	$writeFileSuccessMsg = '<h3>';

	$boolCSV = strpos($filename, ".csv");
	if($boolCSV){
			//numRecords functionality fails for XLS file since based on number of newlines in file
		$wordCountCommand = "wc -l < " . $filename;
		$numRecords = exec($wordCountCommand); //gets # of lines
		$numRecords = $numRecords - 1; //header line in file so OCLC numbers processed = 1 less
		$writeFileSuccessMsg .= 'OCLC numbers processed: <span class="highlightProcessing">&nbsp;' . $numRecords . '&nbsp;</span><br/>';
	}//end CSV

	$writeFileSuccessMsg .= getDatasource(); //appConfigOCLCapi.php

	$writeFileSuccessMsg .= '<br/>new file (REPLACE_DUMMY_STRING kb): <a href="' . $filename . '" target="_blank" class="highlightProcessing">&nbsp;' . $displayFilename . '&nbsp;</a> ';
	$writeFileSuccessMsg .= '<br/><span class="highlightCode"> mailing to <a href="mailto:' . $emailAddress . '" class="highlightProcessing">' . $emailAddress . '</a></span>, ';
	$writeFileSuccessMsg .= 'or click highlighted file name to save it now.';

	$writeFileSuccessMsg .= '</h3>';
	//$writeFileSuccessMsg .= $dataBody; //debug
	return $writeFileSuccessMsg;
}//end generateSuccessMessage
/*******************************************************************/



/*******************************************************************
	composeTextFile: save form data to a .csv/.txt file,
		returns the path so it can be attached to an email.

	$dataBody is filled with form data by prepareAPIdataToSave(),
		which is called in createFile.php before this function
*/
function composeTextFile(){
	resetErrorMessages();
	global $dataBody, $emailAddress;
	global $writeFileOpenError, $writeFileWritingError;

	//now write to text file
	global $thisURL, $filename;
	$filename	 	= substr($thisURL, strpos($thisURL, "/"));
	$filename	.= "../datasources/savedResults/";
	if(isset($_REQUEST["filenameBox"])){ //use name of uploaded file
		$filename	 	.= check_input($_REQUEST["filenameBox"]);
	} else { // used to be no choice of file name, now we can specify: this is the fallback
			//use beginning of email address (username) plus random number
		$filename	 	.= substr($emailAddress, 0, strpos($emailAddress, "@" ));
		$filename	 	.= rand();
	}
	$filename	 	.= ".csv";
	//strrchr = Find last occurrence of a character in a string
	$displayFilename = strrchr( $filename, "/");
	$displayFilename = str_replace( "/", "", $displayFilename);

	//echo "<span class='highlightNumbers'>&nbsp;filename='" . $filename . "'; displayFilename='" . $displayFilename . "';&nbsp;</span></h2>";

	$writeFileOpenError = str_replace("REPLACE_DUMMY_STRING", $filename, $writeFileOpenError);

	$writeFileWritingError = "<h3 class='alert'>Couldn't write '" . substr($dataBody, 0, 50) . "'... to ";
	$writeFileWritingError .= $displayFilename . "!</h3>";

	$fileHandle = fopen( $filename, "wb" ) or die("<h3 class='alert'>" . $writeFileOpenError . "</h3>");
	$numBytes 	= fwrite( $fileHandle, $dataBody ) or die( "<h3 class='alert'>" . $writeFileWritingError . "</h3>" );
	fclose( $fileHandle );

	$writeFileSuccessMsg = generateSuccessMessage($displayFilename, $filename);
	$numKB							 = $numBytes / 1000;
	$writeFileSuccessMsg = str_replace("REPLACE_DUMMY_STRING", $numKB, $writeFileSuccessMsg);
	echo $writeFileSuccessMsg;

	return $filename;
}//end composeTextFile



/*******************************************************************
	composeXLSfile: save form data to a spreadsheet,
		returns the path so it can be attached to an email

	$dataBody is filled with form data by prepareAPIdataToSave(),
		which is called in createFile.php before this function

	requires presence of PEAR package Spreadsheet_Excel_Writer
*/
//require_once 'Spreadsheet/Excel/Writer.php'; //put this inside the function to suppress deprecation warning in iconDate issue system
function composeXLSfile(){
	require_once 'Spreadsheet/Excel/Writer.php';
	resetErrorMessages();
	global $dataBody, $emailAddress;
	global $writeFileOpenError, $writeFileWritingError;

	global $thisURL, $filename;
	$filename	 	= substr($thisURL, strpos($thisURL, "/"));
	$filename	.= "../datasources/savedResults/";
			/* use name of uploaded file, or specified name from form widget filenameBox
					- or email + random number if no name chosen
		 // used to start with beginning of email address (username)
			*/
	if(isset($_REQUEST["filenameBox"])){
		$filename	 	.= check_input($_REQUEST["filenameBox"]);
	} else { // used to be no choice of file name, now we can specify: this is the fallback
			//use beginning of email address (username) plus random number
		$filename	 	.= substr($emailAddress, 0, strpos($emailAddress, "@" ));
		$filename	 	.= rand();
	}
		$filename	 	.= ".xls";
		//echo "<h4>composeXLSfile has filenameBox: " . $_REQUEST["filenameBox"] . "; filename = '" . $filename . "</h4>";

	$displayFilename = strrchr( $filename, "/"); //strrchr = Find last occurrence of a character in a string
	$displayFilename = str_replace( "/", "", $displayFilename);
		$strDebug = "<br/><span class='highlightNumbers'>&nbsp;filename='" . $filename . "'; displayFilename='" . $displayFilename . "';&nbsp;</span><br/>";



	$dataBody  = str_replace("<hr/>", "", $dataBody);
	$arrayData = explode("|", $dataBody);
		//$strDebug .= "<br/>dataBody = '" . $dataBody . "<br/>";
		for($i=0; $i < count($arrayData); $i++){
			$strDebug .= "- " . $i . ") " . $arrayData[$i] . "<br/>";
		}//end for count(arrayData)
		//echo $strDebug;

	$numCols = 12; //fields, columns, in the Spreadsheet_Excel_Writer
	$numRows = count($arrayData) / $numCols; //number of fields (array elements) by number of columns
		$strDebug .= "numCols='" . $numCols . "'; numRows='" . $numRows . "'<br/>";


	$workbook = new Spreadsheet_Excel_Writer($filename); 		 // Create workbook
	$workbook->setVersion(8);
	$numChars = strlen($displayFilename) - 4; //skip '.xls' in $displayFilename when naming sheet
	$sheetname = "OCLC_HOLDINGS";// . substr($displayFilename, 0, $numChars);
	$worksheet = $workbook->addWorksheet($sheetname); // Create worksheet
	$worksheet->setInputEncoding('utf-8');						//so we get accented characters in XLS
		/*
			//test UTF-8 output: it does work in the XLS
		$greek = "\342\345\353\355\341";
		$russian = "\xD0\xBF\xD0\xBE\xD0\xBA\xD0\xB0";
		$worksheet->setInputEncoding('ISO-8859-7');
		$worksheet->write(6, 0, $greek);
		$worksheet->setInputEncoding('utf-8');
		$worksheet->write(6, 1, $russian);
		*/

		/*set column widths: params to setColumn are:
				1st column index to get this width,
				last column index to get this width,
				width where "column width unit is 'one character' in the 'normal font.' ",
				other params not used here, see http://pear.php.net/manual/en/package.fileformats.spreadsheet-excel-writer.spreadsheet-excel-writer-worksheet.setcolumn.php */

	$worksheet->setColumn(0,0,16); //OCLC_NUMBER
	$worksheet->setColumn(1,1,16); //OCLCalternate
	$worksheet->setColumn(2,2,9); //ISSN
	$worksheet->setColumn(3,3,20); //TITLE
	$worksheet->setColumn(4,4,5); //numHolders
	$worksheet->setColumn(5,5,3); //CRLholds
	$worksheet->setColumn(6,6,5); //memberHolds
	$worksheet->setColumn(7,7,25); //allNames
	$worksheet->setColumn(8,8,25); //allCodes
	$worksheet->setColumn(9,9,5); //memberNames
	$worksheet->setColumn(10,10,5); //memberCodes
	$worksheet->setColumn(11,11,5); //memberData

	/* This freezes the first row of the worksheet: http://pear.php.net/manual/en/package.fileformats.spreadsheet-excel-writer.worksheet.freezepanes.php*/
	$worksheet->freezePanes(array(1, 0, 0, 0, 0));

		//do 1st row, column headings, in bold
	$format_bold = $workbook->addFormat();
	$format_bold->setBold();
	for($i=0; $i < $numCols; $i++){
		$worksheet->write(0, $i, $arrayData[$i], $format_bold); //write(row, col, data, format)
	}//end for column headings

		//now loop thru writing the rows of actual data
	//$index    = 0; //it was 0 but now we've done the first row
	$index			= $numCols;
	for ($row=1;  $row < $numRows-1; $row++){
		for($col=0; $col < $numCols; $col++){
				$strDebug .= "<p>index = " . $index . "; row " . $row . " ";
				$strDebug .= " col " . $col . ": '" . $arrayData[$index] . "'";
			$worksheet->write($row, $col, $arrayData[$index]); //UNFORMATTED: no 4th parameter
			$index++;
		}//end for columns
		$strDebug .= "</p>";
	}//end for rows
	//echo $strDebug;

	$workbook->close();
	//$workbook->send($filename); //this would send the new file to the browser

	$numBytes 					 = filesize($filename); //php.net/manual/en/function.filesize.php
	$numKB							 = $numBytes / 1000;
	$writeFileSuccessMsg = generateSuccessMessage($displayFilename, $filename);
	$writeFileSuccessMsg = str_replace("REPLACE_DUMMY_STRING", $numKB, $writeFileSuccessMsg);
	echo $writeFileSuccessMsg;

	return $filename;
}//end composeXLSfile
/*******************************************************************/
/*
	end functions related to saving OCLC API data to email or file
********************************************************************************/


?>