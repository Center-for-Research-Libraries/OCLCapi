
<!-- begin inputFileDataForm.php

	this is the form used to upload a file on the server
	which will contain OCLC numbers to check.

	info gets processed by fileUploadHandler.php

-->

<div id="getFileDataForm" class="boxData">

<div class="pageHeader">File input -- upload a file of OCLC #s:</div>


<h3 class="highlightProcessing">
	<ol>
	<li>'Browse' or 'Choose file' button (first one, on the left)</li>
	<li>Select file, which should have <span class="highlightCode">only 1 OCLC number per line</span></li>
	<li>'Upload' or 'Upload file' button (second one, on the right)</li>
	<li>If the upload is successful, you'll get a link to check OCLC holdings using the uploaded file.</li>
	<li>When holdings results are returned, you will be able to save this information to a file on the server.</li>
	</ol>
</h3>

<form enctype="multipart/form-data"
	name="OCLC_NUMBERupload" id="OCLC_NUMBERupload" method="post"
	action="index.php?action=queryAPI&datasource=fileupload">

<input type="hidden" name="MAX_FILE_SIZE" value="4000000" />
	<!--http://www.php.net/manual/en/features.file-upload.post-method.php
		MAX_FILE_SIZE hidden field (measured in BYTES) must PRECEDE the FILE INPUT FIELD-->


<label for="filenameBox" id="uploadSpan" class="actionButton" style="padding:5px;">
	<input type="file" id="filenameBox" name="filenameBox">
	<input type="submit" id="submitFileBtn" name="submitFileBtn" value="Upload file" >
</label>


<script language="javascript" type="text/javascript">
	$("#filenameBox").click(function(){
			// code setting classes on these form elements: inputFileDataForm.php, fileUploadHandler.php, queryAPIfromFile.php, queryAPIfromText.php
		$("#uploadSpan").removeClass("actionButton");
		$("#uploadSpan").addClass("alert");
		$("#submitFileBtn").addClass("actionButton");
	});//end click func for filenameBox
</script>

</form>

</div><!--end getFileDataForm-->

<!--end inputFileDataForm.php-->