<?php
header("Content-Type: image/png");
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
#demo();

#include "draw.php";

function demo() {
#header("Content-Type: image/png");
$im = @imagecreate(110, 20)
    or die("Cannot Initialize new GD image stream");
$background_color = imagecolorallocate($im, 0, 0, 0);
$text_color = imagecolorallocate($im, 233, 14, 91);
imagestring($im, 1, 5, 5,  "A Simple Text String", $text_color);
imagepng($im);
imagedestroy($im);
}


function basic_graph($data) {
	# Followed tutorial from:
	# http://www.plus2net.com/php_tutorial/gd-linegp.php
	# This doesn't work though: image is garbage text

	# Bounds and spacing constants
	#header("Content-Type: image/png");
	$x_gap = 1;
	$x_max = (DPTS+1)*$x_gap;
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
	
	$ps = @imagecreate($x_max, $y_max)
		or die ("E CANNOT CREATE PLOT SPACE\n");
try {
	$background_color = imagecolorallocate($ps, 234, 234, 234);
	$text_color = imagecolorallocate($ps, 233, 14, 91);
	$graph_color = imagecolorallocate($ps,25,25,25);
	#$background_color = imagecolorallocate($ps, 234, 234, 234) or die("BC\n");
	#$text_color = imagecolorallocate($ps, 233, 14, 91) or die ("tC\n");
	#$graph_color = imagecolorallocate($ps,25,25,25) or die ("gc\n");
} catch (Exception $e) {
	print_r(error_get_last());
	echo $e->getMessage()."\n";
}
	#imagefill($ps, 0, 0, $background_color);

	$x1 = 0;
	$y1 = 0;
	$first_p = True;
	foreach ($temps_24h as $p) {
		$x2=$x1+$x_gap; // Shifting in X axis
		$y2=$y_max+$y_min-$p; // Coordinate of Y axis
		if (!$first_p) {
			imageline ($ps,$x1, $y1,$x2,$y2,$text_color); // Drawing the line between two points
		}
		$x1=$x2; // Storing the value for next draw
		$y1=$y2;
		$first_p = False;
	}
	imagepng($ps);
	imagedestroy($ps);
}

echo "END OF PAGE";
?>
