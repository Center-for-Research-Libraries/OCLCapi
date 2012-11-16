<!--begin include inputTextDataForm.php-->

<div id="getTextDataForm" class="boxData">

<div class="pageHeader">Text input -- check a list of OCLC #s:</div>

<h3 class="highlightProcessing">
	<ol>
	<li>Paste a list of OCLC numbers into the box</li>
	<li>'Check OCLC holdings' button to search OCLC for holdings on those numbers</li>
	<li>When holdings results are returned, you will be able to save this information to a file on the server.</li>
	</ol>
</h3>

<form action="index.php?action=queryAPI&datasource=text" method="post" name="checkText" id="checkText" onsubmit="stripBadCharacters(this.OCLC_NUMBERlist.value);">

		<textarea name="OCLC_NUMBERlist" id="OCLC_NUMBERlist" cols="110" rows="5" wrap="virtual" onfocus="select();" onchange="updateFormData(this, this.value);"><?php if (isset($_REQUEST["OCLC_NUMBERlist"])) echo $_REQUEST["OCLC_NUMBERlist"]; ?></textarea>

		<script language="javascript" type="text/javascript">
			$("#OCLC_NUMBERlist").click(function(){
					// code setting classes on these form elements: inputFileDataForm.php, fileUploadHandler.php, queryAPIfromFile.php, queryAPIfromText.php
				$("#uploadSpan").removeClass("alert");
				$("#submitFileBtn").removeClass("actionButton");
			});//end click func for OCLC_NUMBERlist
		</script>

<!--div align="center"-->
<br/>
	<input name="checkTextButton" type="submit" class="actionButton"
	value="Check OCLC holdings for these numbers" />
<!--/div-->
</form>

</div><!--end getTextDataForm-->
<!--end include inputTextDataForm.php-->