<!--begin include 'navigateDBrecords.php'-->

<div class="processNavigationLink" style="text-align:center;">
	<?php
		setPrevNextPageLinks();
		if (($endID - $recordStep) > $minDBid) echo $prevPageLink;
	?>

navigate DB records...

	<?php
		if (($endID + $recordStep) < $maxDBid) echo $nextPageLink;
	?>
</div>
<!--end include 'navigateDBrecords.php'-->
