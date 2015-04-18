<HTML>
<TITLE>RUMSEY Project 2</TITLE>
<BODY>
<pre>
<?php
define ("DPTS", 1440);

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
# Close database connection
$dbh = NULL;

basic_graph($data);
#smart_graph($data);


function basic_graph($data) {
	# Followed tutorial from:
	# http://www.plus2net.com/php_tutorial/gd-linegp.php
	# This doesn't work though: image is garbage text

	# Bounds and spacing constants
	$x_gap = 10;
	$x_max = DPTS*$x_gap;
	$y_max = 300;
	$y_min = -150;
	$count = 0;
	
	# Collect most recent 24 hours WORTH of temp data
	# Not actually limited to the past 24 hours
	$temps_24h = array_fill(0, DPTS, -500);
	for ($i=0; $i<DPTS; $i++) {
		$temps_24h[$i] = $data[$i]['Temperature'];
		$count++;
	}
	
	$ps = @ImageCreate($x_max, $y_max)
		or die ("E CANNOT CREATE PLOT SPACE\n");
	$background_color = ImageColorAllocate ($ps, 234, 234, 234);
	$text_color = ImageColorAllocate ($ps, 233, 14, 91);
	$graph_color = ImageColorAllocate ($ps,25,25,25);

	$x1 = 0;
	$y1 = 0;
	$first_p = True;
	foreach ($temps_24h as $p) {
		$x2=$x1+$x_gap; // Shifting in X axis
		$y2=$y_max-$y_min-$p; // Coordinate of Y axis
		if (!$first_p) {
			imageline ($ps,$x1, $y1,$x2,$y2,$text_color); // Drawing the line between two points
		}
		$x1=$x2; // Storing the value for next draw
		$y1=$y2;
		$first_p = False;
	}
	ImageJPEG ($ps);
}

echo "END OF PAGE";
?>
</pre>
</BODY>
</HTML>
