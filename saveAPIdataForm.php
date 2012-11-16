<!--
	saveAPIdataForm.php: will be submitted to create a file on the server
		OK here's a weird thing: this form doesn't have the actual fields that hold the actual data.
		Those are all appended via jQuery in Holdings.js > libraryLocationHandler()
-->

<a name="mergedRecordsAnchor">&nbsp;</a>

<!--mergedRecordsHeader gets shown by Holdings.js, libraryLocationHandler()-->
<div id="mergedRecordsHeader" class="newData"
	style="display:none; font-weight:bold; color:#000000;">
	&nbsp;OCLC API returned data for numbers not in the original list (merged records):
	<br/>
	&nbsp;data is shown below and
	<a href="#saveAPIdataAnchor" class="actionButton">will be saved with the rest,</a> along with the requested OCLC number from the original list.&nbsp;
</div>

<div id="mergedRecordsData">
	<!--mergedRecordsData gets shown by Holdings.js, libraryLocationHandler(),
		which will also append here any merged data returned by the API-->
</div>


<a name="saveAPIdataAnchor"></a>

<form name="saveAPIdataForm" id="saveAPIdataForm" class="boxData" method="post" action="index.php?action=createFile">

	<!--ACTUAL FIELDS THAT HOLD THE ACTUAL DATA ARE ALL APPENDED in Holdings.js > libraryLocationHandler()-->

	Your email:
	<input type="text" id="emailAddressBox" name="emailAddressBox" value="<?php echo setEmailAddress(); ?>" size="50" maxlength="75" />

	name the new file:
	<input type="text" id="filenameBox" name="filenameBox" value="<?php echo $reqFilename . '-' . $startLibrary; ?>" size="50" maxlength="75" />

	<input id="formTextSubmit" name="holdingsAPIformTextSubmit" type="submit" class="actionButton" value="Save data to file" />
</form>


<script language="javascript" type="text/javascript">
	//jQuery binds event handler to the form
	$('#saveAPIdataForm').submit( function() { return checkForEmailAddress(); } );
</script>

<!--end saveAPIdataForm.php-->