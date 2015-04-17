<HTML>
<TITLE>RUMSEY Project 2</TITLE>
<BODY>
<pre>
<?php
$database = "temp.db";
$table = "T";
$qry = "SELECT * FROM T ORDER BY Date DESC, Time DESC;";
$data;

# Connect to database and pull all data
try {
	# Connect
	$dbh = new PDO("sqlite:$database");
	# Set errormode to Exceptions
	$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	# Capture table data
	$db_data = $dbh->query($qry);
	#$dbh->closeCursor();
	# Put into PHP array
	$data = $db_data->fetchAll();
} catch (PDOException $e) {
	echo $e->getMessage()."\n";
}
$dbh = NULL;
print_r($data);
echo "WHY CAN'T NO ONE SEE ME?\n";
?>
</pre>
</BODY>
</HTML>
