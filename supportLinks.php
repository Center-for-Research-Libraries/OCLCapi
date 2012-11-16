
<!--begin include supportLinks.php-->
<div id="oclcSupportLinks" class="boxData" >
	<div class="smallPageHeader">
		OCLC API Support links:

	<span id="showHideOCLClinks" class="actionButton toggler">[toggle OCLC links]</span>
		<script language="javascript" type="text/javascript">
			$(document).ready(function(){
				$("#showHideOCLClinks").click(function(){
					$("#OCLClinkList").toggle();
			});//end click func for showHideInstructions
		});//end doc.ready function

		</script>
	</div><!--.smallPageHeader-->

	<ul id="OCLClinkList" style="display:none">
		<li>Register your institution to use WorldCat Search API: you'll need an <a href="http://oclc.org/developer/platform/authentication-documentation" target="_blank">application key (Web service key or WSKey)</a> that will enable your institution to use the WorldCat Search API.</li>
		<li><a href="http://www.worldcat.org/affiliate/tools?atype=wcapi" target="_blank">WorldCat Search API (Web service)</a></li>
		<li><a href="http://oclc.org/developer/services/WCAPI" target="_blank">WorldCat Search API documentation</a></li>
		<li><a href="http://www.oclc.org/developer/services" target="_blank">info links on other APIs</a> (this one is most useful as of Jan. 2011)</li>
		<li><a href="http://worldcat.org/devnet/blog/" target="_blank">blog</a></li>
		<li><a href="https://www3.oclc.org/app/listserv/" target="_blank">mailing list</a></li>
		<li><a href="http://worldcat.org/devnet" target="_blank">wiki</a></li>
		<li>oclcdevnet on twitter</li>
		<li><a href="http://www.worldcat.org/webservices/catalog/evaluator.html" target="_blank">URI evaluator/query builder</a> (generate correct syntax)</li>
		<li>have pdfs of some presentations, printed out developer guide</li>
		<li><a href="http://oclc.org/developer/documentation/worldcat-search-api/service-levels" target="_blank">About service levels</a></li>
		<li><a href="http://oclc.org/developer/documentation/worldcat-search-api/tips-specific-indexes" target="_blank">About the indexes searched via the API</a>, including codes for getting records limited to a given range (amount) of holding libraries</li>
		<li><a href="http://oclc.org/developer/documentation/worldcat-search-api/library-locations" target="_blank">About "Library Locations,"</a> used for finding the holdings info</li>
		<li><a href="http://oclc.org/developer/applications/cite" target="_blank">http://oclc.org/developer/applications/cite</a></li>
		<li><a href="http://oclc.org/developer/applications/bibme" target="_blank">http://oclc.org/developer/applications/bibme</a></li>
		<li><a href="http://www.oclc.org/worldcat/policies/terms/" target="_blank">OCLC terms of service</a> prohibit "use of bots, spiders, or other automated information-gathering devices or programming routines to 'mine' or harvest material amounts of Data;"</li>
		<li>Any website using the WorldCat Search API agrees to display the "This site uses WorldCat" badge graphic ... on the website and provide it as clickable access to WorldCat.org.
			<?php echo $OCLC_LOGO; ?><!--must be present on every page-->
		</li>
	</ul>
</div><!--#oclcSupportLinks-->
<!--end include supportLinks.php-->

