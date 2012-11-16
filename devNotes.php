
<!--bgn include devNotes.php-->
<div id="devNotes" class="boxData">
	<div class="smallPageHeader">
		Development notes (things to be fixed or enhanced, other uses of OCLC API for the CRL OPAC, or staff):

		<span id="showHideDevNotes" class="actionButton toggler">[toggle&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;devnotes]</span>
		<script language="javascript" type="text/javascript">
			$(document).ready(function(){
				$("#showHideDevNotes").click(function(){
					$("#devNoteList").toggle();
					$("#dbInstructionsHeader").toggle();
					$("#dbInstructionsList").toggle();
			});//end click func for showHideInstructions
		});//end doc.ready function

		</script>
	</div><!--.smallPageHeader-->

	<ul id="devNoteList" style="display:none">
		<li>
				The first version of this application (Dec. 2010) included the ability to update records saved in the database.
				With time it became clear that the usefulness of a front-end application for the OCLC Library Locations API was not
				in saving the data and using it via a web page, because the datasets being saved are constantly changing.
				One month, we wanted to compare CRL's holdings to Library X's,
				next month to examine how widely-held CRL's holdings in a specific subject area are, etc.
				Since the questions are not fixed, neither are the datasets we need to examine, and in any case the OCLC data itself is changing as institutions add and delete holdings.</li>
				<li>The best way to handle the situation seems to be:
					<ol>
						<li>just download data as partners ask us to</li>
						<li>save it in a mySQL database</li>
						<li>do all the examination and manipulation of the data via that interface.</li>
					</ol>
				</li>
				<li>
				The <strong>following documentation points are maintained for historical interest</strong>, and <strong>do not apply</strong> as of 2012-Nov-13.
				<ol id="dbInstructionsList" style="display:none">
					<li id="dbInstructionsHeader" style="display:none">
						Instructions for using this system to fill a database with holding libraries information:
					</li><!--#dbInstructionsHeader-->
					<li>Sept. 2011-Nov. 2012: this part of the system is rarely used: input is usually via file, with results loaded into MySQL and data accessed via MySQL Workbench.</li>
					<li>The page will pull OCLC #s from the table and add each to an array.</li>
					<li>Requests to OCLC are made, responses received, and data can be saved, all as described above for text or file input.</li>
					<li>
						Update adds new or updated OCLC Holdings information to the database, and then confirms success or failure.</li>
					<li>
						If successful, each Update page window closes automatically in about 15 seconds, and you can go back to the Processing page.
						Pages with errors will remain open and have to be manually closed: this allows staff to see what record has failed to Update.</li>
					<li>
						To see what records on the page were <strong>not</strong> submitted to the Update step, reload the Processing page.</li>
						<li>The <u>Previous set</u> and <u>Next set</u> links allow the user to page through other record sets.</li>
						<li>The system also allows users to
						<a href="index.php?action=queryDB" target="_blank">generate reports</a>
					 	on what's in the table, sortable by OCLC number and other database fields. </li>
				</ol><!--#dbInstructionsList-->
				<ul type="square">
					<li>Database report interface needs to present data beyond the individual record level ('<em>there are X# of items held by between Y# and Z# libraries</em>') and break down Class Number data,
					the closest thing in the database to subject headings.<br />
					Some of this might be more easily done in the mySQL Workbench, MS Access or Excel interfaces
					once the database is filled (i.e. with charts and graphs),
					and then linked from these pages.			</li>

					<li>The reports generated need to do a better job of combining conditions:
					<em>Title contains 'Astro', AND LCCN contains 'QB' AND number of holders &gt; 5
					AND number of holders &lt; 8</em>, for example.</li>

					<li>If there is a need, we could also implement wildcard searches
					in the report code.</li>
				</ul>
		</li>
	</ul>
</div><!--#devNotes-->
<!--end include devNotes.php-->

