
<!--begin include queryDBprocess.php-->
	<!--< ? p h p  echo debuggingData ?>-->
<?php
	setPrevNextPageLinks();
	global $strID, $strOCLC_NUMBER, $strLCCN, $strTITLE, $strCRLholds, $strAllNames, $strNumHolders, $strMFHD;
	global $bTITLE, /* $bLCCN, */ $bOCLC, $b_numHolders, $bSummarize, $bNoChangedFormData, $sortByField;
	global $fromOCLC_NUMBER, $toOCLC_NUMBER, $minOCLC_NUMBER, $maxOCLC_NUMBER;
	global $from_numHolders, $to_numHolders, $min_numHolders, $maximumLibraries;
	global $strReportTitle;

	$datasource = "formData";
	fillStringVars($datasource);
	setBooleansForFields();
	prepareReportVariables(); //parameters for prepareReportSQL are set in prepareReportVariables()
	$strSQL = prepareReportSQL();

	echo "<h4 class='highlightNumbers'>open the database here with strSQL='" . $strSQL . "</h4>";

		// Make + Run the query.
	$selectResult 		= mysqli_query($databaseConnection, $strSQL) or die ('<h3 class="alert">Could not perform the select query: <br /><span class="highlightCode">' . mysqli_error($databaseConnection) . "</h3>" );
	$numReturnedRows 	= mysqli_num_rows($selectResult);

	//echo "<h4>opened!</h4>";
	//echo "<h4 style='background-color:#FF0000'>using strSQL ='" . $strSQL . "'</h4>";

	if (isset($_REQUEST["summarizeBox"]) && ($_REQUEST["summarizeBox"] == "summarize")){
		$summarizeValue = "summarizeTrue";
	} else {
		$summarizeValue = "summarizeFalse";
	}

?>

<!--THIS IS FOR STEP 2: DATA SUBMITTED FROM STEP 1-->
<div id="queryDBsummary" class="boxData">
	<div class="smallPageHeader">Database query search results:</div>
	<h3>
		<?php
			echo $strReportTitle;

			$strRecordsMsg = '&nbsp;&nbsp;<span class="highlightProcessing">';
			$strRecordsMsg .= $numReturnedRows;
			if($numReturnedRows == 1) $strRecordsMsg .= '</span> record meets ';
			else $strRecordsMsg .= '</span> records meet ';
			$strRecordsMsg .= ' the criteria.';
			echo $strRecordsMsg;
		?>
	</h3>
	<p>
		(There are currently
		<span class="holdingsHeader">&nbsp;<?php echo getRowsInDatabase(); ?>&nbsp;</span>
		total records in the database.)
	</p>
</div><!--#queryDBsummary-->


<script language="javascript" type="text/javascript">
	function sortReportResubmit(sortByField){
		$("#sortByField").val(sortByField);
		$("#sortReportForm").submit();
	}//end sortReportResubmit()
</script>

<!--this was include dbReportSortingForms.php - gets submitted to redisplay report with new sort order -->
<form action="<?php echo $thisFile; ?>?action=queryDB" method="post" name="sortReportForm" id="sortReportForm">
	<!--onSubmit="alert('sortReportForm submitting');"-->

		<!--sortByField value gets changed just before the form is submitted-->
	<input name="sortByField" id="sortByField" type="hidden" value="OCLC_NUMBER" />

	<input name="title" type="hidden" value="<?php echo $_REQUEST['title']; ?>" />
	<input name="titletype" type="hidden" value="<?php echo $_REQUEST['titletype']; ?>" />
	<!--input name="LCCN" type="hidden" value="<?php echo $_REQUEST['LCCN']; ?>" /-->
	<input name="fromOCLC_NUMBER" type="hidden" value="<?php echo $_REQUEST['fromOCLC_NUMBER']; ?>" />
	<input name="toOCLC_NUMBER" type="hidden" value="<?php echo $_REQUEST['toOCLC_NUMBER']; ?>" />
	<input name="from_numHolders" type="hidden" value="<?php echo $_REQUEST['from_numHolders']; ?>" />
	<input name="to_numHolders" type="hidden" value="<?php echo $_REQUEST['to_numHolders']; ?>" />
	<input name="summarizeBox" type="hidden" value="<?php echo $summarizeValue ?>" />
</form><!--#sortReportForm-->

	<?php
		$sortingMessage = '<div class="smallPageHeader">';
		$sortingMessage .= "Click on any OCLC #, Title <!--, LCCN,--> or number of holders data to sort by that field</div>";
		echo $sortingMessage;

		$sortLinkOCLC 				= '<a class="sortableData" id="sortLinkOCLC">';
		$sortLinkOCLCalt			= '<a class="sortableData highlightCode" id="sortLinkOCLCalt">';
		$sortLinkTitle 				= '<a class="sortableData" id="sortLinkTitle">';
		$sortLink_numHolders 	= '<a class="sortableData highlightCode" id="sortLink_numHolders">';

		if (isset($_REQUEST["summarizeBox"]) && ($_REQUEST["summarizeBox"] == "summarize")){
			include("dbReportSummary.php");
		} else { // while row of data exists, put it in $row as an associative array
			$thisIndex = 0;
			while ($row = mysqli_fetch_assoc($selectResult)) {
				$datasource = "database";
				fillStringVars($datasource);
				$safeTitle = str_replace('"', '', $strTITLE);
				$safeTitle = str_replace("'", '', $safeTitle);
				setBGcolor( $thisIndex++ );
				include("displayDiv.php"); 	//provides the spans that jQuery will now fill
				//include("updateDBform.php"); 			//provides the form to update data from DB: 2012-11-13 NO LONGER SUPPORTED
				?>
				<script language="javascript" type="text/javascript">
						$(document).ready(function(){ //spans in displayDiv.php: replace their contents
								//OCLC_NUMBER
							$("#summaryBar<?php echo $strOCLC_NUMBER; ?>").html( '<?php echo $sortLinkOCLC . $strOCLC_NUMBER; ?></a>; ' );
							$("#sortLinkOCLC").click( function(event){sortReportResubmit("OCLC_NUMBER")});//end click func
							<?php
								if ($strOCLCalternate){
							?>
									$("#summaryBar<?php echo $strOCLC_NUMBER; ?>").append( '; superseded by OCLC #: <?php echo $sortLinkOCLCalt . $strOCLCalternate; ?></a>; ' );
									$("#sortLinkOCLCalt").click( function(event){sortReportResubmit("OCLC_NUMBER")});//end click func
							<?php
								}//end if strOCLCalternate
							?>
								//title
							$("#summaryBar<?php echo $strOCLC_NUMBER; ?>").append( '<?php echo $sortLinkTitle . $safeTitle; ?></a>');
							$("#sortLinkTitle").click( function(event){sortReportResubmit("title")});//end click func
								//numHolders
							$("#summaryBar<?php echo $strOCLC_NUMBER; ?>").append( '; held by <?php echo $sortLink_numHolders . $strNumHolders; ?></a> libraries ' );
							$("#sortLink_numHolders").click( function(event){sortReportResubmit("numHolders")});//end click func
							<?php
								if ($strCRLholds){
							?>
									$("#summaryBar<?php echo $strOCLC_NUMBER; ?>").append( ' including CRL' );
							<?php
								}//end if CRLholds
							?>
/*
							var updateButton = '&nbsp;<input type="submit" id="updater<?php echo $strOCLC_NUMBER; ?>" name="updater<?php echo $strOCLC_NUMBER; ?>" value="update <?php echo $strOCLC_NUMBER; ?> in DB" class="actionButton" />';
							$("#summaryBar").append( updateButton );
*/
								//allCodes data goes in different div
							$("#dataResult<?php echo $strOCLC_NUMBER; ?>").html( '&nbsp;<?php echo $strAllCodes; ?>&nbsp;' );
						});//end doc.ready function
				</script>
				<?php
				//continue while
				if (($thisIndex % 20) == 0) echo $sortingMessage;
			}//end while
		}//end if-else summary
	?>





<?php

if (isset($_REQUEST["summarizeBox"]) && ($_REQUEST["summarizeBox"] != "summarize")) { // full details requested
	setBGcolor(1);
	$recNum = 0;
	$datasource = "database";
	while(($row = mysqli_fetch_assoc($selectResult)) != NULL) {
		$recNum = $recNum + 1;
		fillStringVars($datasource);// next, put values fr DB into string vars, see appConfigOCLCapi.php

		include("dbReportRecord.php");

	//figure out buffering in PHP - 50,000 records in Hard_Copy_CRLS
		setBGcolor($recNum+1);
	}//end while
} //end if summarizeBox != "summarize"

//end include queryDBprocess.php
?>
