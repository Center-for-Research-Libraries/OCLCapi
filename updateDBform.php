<!--
	begin include updateDBform.php - contain DB record data + submit to updateDBprocess.php
	2012-11-13 - stopped offering update; uncomment in index.php to reinstate
-->

<div id="updateDBdiv<?php echo $strOCLC_NUMBER; ?>" class="apiDisplayBox" style="display:block;">

	<form
		name="update<?php echo $strOCLC_NUMBER; ?>"
		id="update<?php echo $strOCLC_NUMBER; ?>"
		action="index.php?action=update" method="post" target="_blank">

		<input name="dbUpdateTrigger" type="hidden" value="dbUpdateTrigger" />

		<!--textarea name="textarea<?php echo $strOCLC_NUMBER; ?>" id="textarea<?php echo $strOCLC_NUMBER; ?>" cols="60" rows="5" wrap="virtual" onfocus="select();" onchange="updateFormData(this, this.value);"></textarea-->
		<input name="tableChoice" id="tableChoice" type="text" value="<?php echo $tableChoice; ?>" />
		<input name="id" id="id<?php echo $strOCLC_NUMBER; ?>" type="text" value="<?php echo $strID; ?>" />
		<input name="OCLC_NUMBER" id="OCLC_NUMBER<?php echo $strOCLC_NUMBER; ?>" type="text" value="<?php echo $strOCLC_NUMBER; ?>" />
		<!--<input name="LCCN" id="LCCN<?php echo $strOCLC_NUMBER; ?>" type="text" value="<?php echo $strLCCN; ?>" />-->
		<input name="title" id="title<?php echo $strOCLC_NUMBER; ?>" type="text" value="<?php echo $strTITLE; ?>" />
		<input name="numHolders" id="numHolders<?php echo $strOCLC_NUMBER; ?>" type="text" value="<?php echo $strNumHolders; ?>" />
		<input name="CRLholds" id="CRLholds<?php echo $strOCLC_NUMBER; ?>" type="text" value="<?php echo $strCRLholds; ?>" />
		<input name="allNames" id="allNames<?php echo $strOCLC_NUMBER; ?>" type="text" value="<?php echo $strAllNames; ?>" />
		<input type="submit" id="updater<?php echo $strOCLC_NUMBER; ?>" name="updater<?php echo $strOCLC_NUMBER; ?>" value="update <?php echo $strOCLC_NUMBER; ?> in DB" class="actionButton" />
	</form>
</div>
<!--end include updateDBform.php -->