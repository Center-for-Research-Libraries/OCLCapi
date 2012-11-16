<hr/>
<?php

echo $strSQLDescribe;
echo $strAppDebug;

echo "Details about the database: $describeResult<br>";
echo get_resource_type( $describeResult ); //"mysqli_result "
/*
mysqli_query() executes the query, but does not return any resulting data from the database.
Call mysqli_fetch_row() after mysqli_query() to get an array with the data resulting from the query.
*/
$describeResult 	= @mysqli_query($strSQLDescribe, $databaseConnection);
$junk = mysqli_fetch_row( $describeResult );

echo "<ol>";

// Lists the table name and then the field name
for ($i = 0; $i < mysqli_num_fields($describeResult); ++$i) {
    $table 	= mysqli_field_table($describeResult, $i);
    $field 	= mysqli_field_name($describeResult, $i);
	$type 	=  mysqli_field_type($describeResult, $i);
    echo  "table $table: field $field: type $type<br>";
}

echo "</ol>";


$selectResult = mysqli_query("SELECT * FROM $tableChoice");
$fields = mysqli_num_fields($selectResult);
$rows   = mysqli_num_rows($selectResult);
$table  = mysqli_field_table($selectResult, 0);
echo "The '" . $table . "' table has " . $fields . " fields and " . $rows . " record(s)<br>";
echo "The table has the following fields:<br>";
for ($i=0; $i < $fields; $i++) {
    $type  = mysqli_field_type($selectResult, $i);
    $name  = mysqli_field_name($selectResult, $i);
    $len   = mysqli_field_len($selectResult, $i);
    $flags = mysqli_field_flags($selectResult, $i);
    echo "name: $name | type: $type | length: $len | flags: $flags.<br>";
}

mysqli_free_result($describeResult);
mysqli_free_result($selectResult);

?>

