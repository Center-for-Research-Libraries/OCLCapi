<!--begin include updateDBprocess.php
	adds info to db-->

<div class="boxData" id="updateMasthead">
	<div class="pageHeader">CRL Holdings application: Update database records<br />Processing and confirmation</div>
</div>


<?php
	$tableChoice = "TEST_HOLDINGS";
	$strID = trim($_REQUEST["id"]);
	$strSQL = "SELECT * FROM " .$tableChoice . " WHERE id = " . $strID;
	echo "First select old DB data, fill for display: <br />this strSQL = '" . $strSQL . "'<br />";
		// Make + Run the query.
	$selectResult = mysqli_query($databaseConnection, $strSQL) or die ('<h3>Could not perform the select query: <br /><em>' . mysqli_error($databaseConnection) . "</em></h3>" );

	if ($selectResult) { // If select ran OK, save the fields we want
		$row = mysqli_fetch_array($selectResult, MYSQLI_ASSOC);
		$datasource = "database";
		fillStringVars($datasource);
		$strOLDallNames = $strAllNames; // to display below
	}//end if

	 if ($reqAction == "update"){	//fill global string vars now with form data
		$datasource = "formData";
		//echo "will call fillStringVars($datasource)";
		fillStringVars($datasource);
	 }
		// reset $strSQLupdate
	global $tableChoice;
	$strSQLupdate = "UPDATE " . $tableChoice;
	$strSQLupdate .= " SET numHolders = " . $strNumHolders . ",";
			//escape in next line to fix problems with slashes + html
	$strNewOCLCvalue = mysqli_real_escape_string($databaseConnection, $strAllNames);
	$strSQLupdate .= " allNames = '" . $strNewOCLCvalue . "'";
	//$strSQLupdate .= " allNames = 'fake data'";
		//$strSQLupdate .= " allNames = '$strAllNames',";
	$strSQLupdate .= " WHERE ID = " . $strID . ";";

	echo 'OCLC #<strong>' . $strOCLC_NUMBER . '</strong>, database ID=' . $strID . '<br/>';
	echo 'Title: <strong>' . $strTITLE . '</strong>&nbsp;';
	echo 'old from database field: [toggle] ' . $strOLDallNames . '<br />';
	echo 'new from form field: <span class="highlightCode"> ' . $strAllNames . '</span>, ';
	echo '<span class="highlightNumbers"> ' . $strNumHolders . '</span> holders.';
	echo '<p>strSQLupdate: <span class="highlightCode">&nbsp;' . $strSQLupdate . '</span></p>';


// Run the query: PERFORM THE UPDATE
/************************************
PHP manual: mysqli_affected_rows
	When using UPDATE, MySQL will not update columns where the new value is the same as the old value.
	This creates the possibility that mysqli_affected_rows() may not actually equal the number of rows matched,
	only the number of rows that were literally affected by the query.
*/
	$updateResult = mysqli_query($databaseConnection, $strSQLupdate);
	$mysqli_updated_rows = mysqli_affected_rows($databaseConnection);
	if ($mysqli_updated_rows == -1) $bOperationSuccessful = false;
	else $bOperationSuccessful = true;
	if (mysqli_errno($databaseConnection) == 0) $strUpdateError = "no error number...";
	else $strUpdateError = " error # " . mysqli_errno($databaseConnection);
	if (mysqli_error($databaseConnection) == "") $strUpdateError .= "no error message.";
	else $strUpdateError .= ": '" . mysqli_error($databaseConnection) . "' ";
	echo '<h4 style="background-color:#e97100;">&nbsp;update returned:<span class="highlightNumbers">&nbsp;';
	echo $updateResult . '&nbsp;</span>; ';
	echo 'rows affected: <span class="highlightNumbers">&nbsp; ' . $mysqli_updated_rows . '&nbsp;</span> ';
	echo 'status: <span class="highlightNumbers">&nbsp; ' .  $bOperationSuccessful . '&nbsp;</span></h4>';

		//NOW CONFIRM IT TO USER: GO BACK + OPEN IT, GET THAT RECORD + DISPLAY DETAILS (so we know it's the live change)
	$strSQLconfirm = "SELECT * FROM " . $tableChoice . " WHERE " . $tableChoice . ".id = '" . $strID . "';";
	//echo "<br />strSQLconfirm = " . $strSQLconfirm . "<br />";

		// Run the query: select the updated row
	$confirmResult = mysqli_query($databaseConnection, $strSQLconfirm);
	$row = mysqli_fetch_array($confirmResult, MYSQLI_ASSOC);
	$mysqli_selected_rows = mysqli_affected_rows( $databaseConnection );
	$datasource = "database";
	echo '<p>' . $mysqli_selected_rows . ' rows were selected; next call fillStringVars(' . $datasource . ')</p>';
	fillStringVars($datasource);
	echo '<p>Updated data: <strong> ' . $strAllNames . '</strong>';
	echo '&nbsp; database now shows <span class="newData"> ' . $strNumHolders . '</span> holders.<hr />';

	if ($bOperationSuccessful) { // If update ran OK
?>
	<script language="javascript" type="text/javascript">
		function closeWindow() { window.close(); }
			var timeoutFactor = 15000;
			var timeoutDisplay = timeoutFactor / 1000;
			document.write("This window will close automatically after ", timeoutDisplay, " seconds. Thanks for your patience.")
			setTimeout('closeWindow()', timeoutFactor);
		</script>
<?php
	} else echo "<h3 class='alert'>there was a problem with the update operation: <span class='highlightCode'>&nbsp;$strUpdateError </span>.</h3>";
?>

</td></tr>
<tr bgcolor="#adbd90"><th><a href="javascript:window.close();">close this window</a></th></tr>
</table>
<!--} //end if action is update-->
