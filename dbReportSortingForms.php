<!--begin include dbReportSortingForms.php -->
<?php

	if (isset($_REQUEST["summarizeBox"]) && ($_REQUEST["summarizeBox"] == "summarize")){
		$summarizeValue = "summarizeTrue";
	} else {
		$summarizeValue = "summarizeFalse";
	}

?>
<!--BY OCLC NUMBER -->
<form action="<?php echo $thisFile; ?>?action=queryDB" method="post" name="sortOCLC_NUMBERform">
	<input name="sortByField" type="text" value="OCLC_NUMBER" />
	<input name="title" type="hidden" value="<?php echo $_REQUEST['title']; ?>" />
	<input name="titletype" type="hidden" value="<?php echo $_REQUEST['titletype']; ?>" />
	<!--input name="LCCN" type="hidden" value="<?php echo $_REQUEST['LCCN']; ?>" /-->
	<input name="fromOCLC_NUMBER" type="hidden" value="<?php echo $_REQUEST['fromOCLC_NUMBER']; ?>" />
	<input name="toOCLC_NUMBER" type="hidden" value="<?php echo $_REQUEST['toOCLC_NUMBER']; ?>" />
	<input name="from_numHolders" type="hidden" value="<?php echo $_REQUEST['from_numHolders']; ?>" />
	<input name="to_numHolders" type="hidden" value="<?php echo $_REQUEST['to_numHolders']; ?>" />
	<input name="summarizeBox" type="hidden" value="<?php echo $summarizeValue ?>" />
</form>




<!--BY TITLE -->
<form action="<?php echo $thisFile; ?>?action=queryDB" method="post" name="sortTITLEform">
	<input name="sortByField" type="text" value="title" />
	<input name="title" type="hidden" value="<?php echo $_REQUEST['title']; ?>" />
	<input name="titletype" type="hidden" value="<?php echo $_REQUEST['titletype']; ?>" />
	<!--input name="LCCN" type="hidden" value="<?php echo $_REQUEST['LCCN']; ?>" /-->
	<input name="fromOCLC_NUMBER" type="hidden" value="<?php echo $_REQUEST['fromOCLC_NUMBER']; ?>" />
	<input name="toOCLC_NUMBER" type="hidden" value="<?php echo $_REQUEST['toOCLC_NUMBER']; ?>" />
	<input name="from_numHolders" type="hidden" value="<?php echo $_REQUEST['from_numHolders']; ?>" />
	<input name="to_numHolders" type="hidden" value="<?php echo $_REQUEST['to_numHolders']; ?>" />
	<input name="summarizeBox" type="hidden" value="<?php echo $summarizeValue ?>" />
</form>






<!--BY NUM_HOLDERS -->
<form action="<?php echo $thisFile; ?>?action=queryDB" method="post" name="sortForm_numHolders">
	<input name="sortByField" type="text" value="numHolders" />
	<input name="title" type="hidden" value="<?php echo $_REQUEST['title']; ?>" />
	<input name="titletype" type="hidden" value="<?php echo $_REQUEST['titletype']; ?>" />
	<!--input name="LCCN" type="hidden" value="<?php echo $_REQUEST['LCCN']; ?>" /-->
	<input name="fromOCLC_NUMBER" type="hidden" value="<?php echo $_REQUEST['fromOCLC_NUMBER']; ?>" />
	<input name="toOCLC_NUMBER" type="hidden" value="<?php echo $_REQUEST['toOCLC_NUMBER']; ?>" />
	<input name="from_numHolders" type="hidden" value="<?php echo $_REQUEST['from_numHolders']; ?>" />
	<input name="to_numHolders" type="hidden" value="<?php echo $_REQUEST['to_numHolders']; ?>" />
	<input name="summarizeBox" type="hidden" value="<?php echo $summarizeValue ?>" />
</form>


<!--end include dbReportSortingForms.php-->