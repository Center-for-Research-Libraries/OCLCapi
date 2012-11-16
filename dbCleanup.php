<?php
	if (is_resource($result))
		mysqli_free_result($result);
	if (is_resource($selectResult))
		mysqli_free_result($selectResult);
	if (is_resource($updateResult))
		mysqli_free_result($updateResult);
	if (is_resource($confirmResult))
		mysqli_free_result($confirmResult);
	if (is_resource($describeResult))
		mysqli_free_result($describeResult);				

	mysqli_close( $databaseConnection );
?>