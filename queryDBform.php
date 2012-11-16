<!--begin include queryDBform.php-->
<?php
	$datasource = "formData";
	fillStringVars($datasource);

	$tableChoice = "TEST_HOLDINGS";
?>


<div id="dbReportBox" class="boxData">

<div id="dbReportHeader" class="pageHeader">
	Database queries:
	<br/>
	data harvested from OCLC WorldCat Search API, saved in table <?php echo $tableChoice; ?>


	<span id="showHideFormInstructions" class="actionButton toggler">[toggle instructions]</span>
	<script language="javascript" type="text/javascript">
		$(document).ready(function(){
			$("#showHideFormInstructions").click(function(){
				$("#dbReportNotes").toggle();
		});//end click func for showHideInstructions
	});//end doc.ready function

	</script>
</div><!--#dbReportHeader-->


	<div id="dbFormDiv" class="boxData">
	<form name="reportdataform" id="reportdataform" method="post"
		action="<?php echo $thisFile; ?>?action=queryDB&beginID=1&endID=<?php echo $recordStep; ?>&sortByField=TITLE">

		<input name="dbQueryTrigger" type="hidden" value="dbQueryTrigger" />

		<span id="titleBox">
			<strong>Title</strong>
			<em>begins</em> with <input name="titletype" type="radio" value="index" checked="checked" />
			or <em>contains</em>: <input name="titletype" type="radio" value="contains"/>
			<input type="text" name="title" id="title" size="50" maxlength="150" value="<?php echo $strTITLE; ?>" onFocus="this.select();"/>
		</span><!--#titleBox-->

		<div id="OCLCbox">
			<strong>OCLC # range</strong> <em>from</em>:
			<input type="text" name="fromOCLC_NUMBER" id="fromOCLC_NUMBER" size="10" maxlength="12" value="<?php echo $minOCLC_NUMBER; ?>" onFocus="this.select();" />
			(<?php echo $minOCLC_NUMBER; ?> is lowest)
			<em>up to</em>:
			<input type="text" name="toOCLC_NUMBER" id="toOCLC_NUMBER" size="10" maxlength="12" value="<?php echo $maxOCLC_NUMBER; ?>" onFocus="this.select();" />
			(<?php echo $maxOCLC_NUMBER; ?> is highest) &nbsp;&nbsp;

			<script language="JavaScript" type="text/javascript">
				function resetOCLCvalues(){
					var minOCLC = "<?php echo $minOCLC_NUMBER; ?>";
					$("#fromOCLC_NUMBER").value = minOCLC;
					var maxOCLC = "<?php echo $maxOCLC_NUMBER; ?>";
					$("#toOCLC_NUMBER").value = maxOCLC;
					//alert(minOCLC + "\n" + maxOCLC);
				}//end resetOCLCvalues
			</script>
			<a href="javascript:resetOCLCvalues();">reset OCLC numbers to defaults</a>
		</div><!--#OCLCbox-->

		<div id="numHoldersBox">
			Number of <strong>holding libraries</strong> between:
			<select name="from_numHolders">
				<option value="1" selected="selected">1</option>
				<?php
					for($option = 2; $option <= 101; $option++) {
						echo '<option value="' . $option . '">' . $option . '</option>';
					}//end for
				?>
				</select>
				and
				<select name="to_numHolders">
					<option value="1">1</option>
					<?php
					for($option = 2; $option <= 100; $option++) {
						echo '<option value="' . $option . '">' . $option . '</option>';
					}//end for
					?>
					<option value="101" selected="selected">101</option>
				</select>
		</div><!--#numHoldersBox-->

		<div id="holderDataBox">
			<strong>OCLC code</strong> among holders:
			<input type="text" name="holderCode" id="holderCode" size="3" maxlength="5" value="LHL" />
		</div><!--#holderDataBox-->

		<div id="submitBox">

			<input type="submit" id="submitReportBtn" name="submitReportBtn" class="actionButton" value="Generate the Report" />

			<span id="summaryBox">
				Summary only, <em>no details</em>: <input name="summarizeBox" type="checkbox" value="summarize" />
			</span><!--#summaryBox-->


		</div><!--#submitBox-->
	</form>
	</div><!--#dbFormDiv-->

	<div id="dbReportNotes" style="display:none;">
		<p>Select the report type, and specify the data you are searching for.</p>
		<ul>
			<li><strong>Enter</strong> search terms in the appropriate fields. </li>
			<li>Initial articles have been retained in the database, so include 'The', 'Le', etc. in title index searches.</li>
			<li>To search for one <strong>OCLC number</strong>, enter it in the <em>first</em> OCLC box and leave the second box <em>blank</em>.</li>
			<li>To search for one amount of <strong>holding libraries</strong>,  choose the same amount in both menus.</li>
			<li> To see only the <em>number of records</em> that meet your search criteria, check the box labeled "Summary only" before submitting the data. </li>
			<li>Searches with details for large ranges of OCLC numbers, short LC Class bits, or common keywords <em><strong>will run very slowly</strong></em>  and may even <em>crash your browser</em>. These may be better to run within the database interface.</li>
		</ul>
	</div><!--#dbReportNotes-->

</div><!--#dbReportBox-->


<div style="clear:both"></div>


<!--end include queryDBform.php-->


