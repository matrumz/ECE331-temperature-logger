<?php
header("Content-Type: image/png");
define ("DPTS", 1440);
# YSCALAR => how many pts per degF
define ("YSCALAR", 1);
# XSCALAR => how many pts per minute
#define ("XSCALAR", 1);
$protocol = "sqlite";
$database = "temp.db";
$table = "T";
$qry = "SELECT * FROM T ORDER BY Date DESC, Time DESC;";
$data;

# Connect to database and pull all data
try {
	# Connect
	$dbh = new PDO("$protocol:$database");
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

# Develop a "dumb" basic graph intially that displays
# most recent 24 hours WORTH of temp data, but not limited to past 24 hours
basic_graph($data);

# Time permitting, a smart graph will be developed that will interpret time
# and will show blank spaces for missing data in actual past 24 hours
#smart_graph($data);

function basic_graph($data) {
	# Follows tutorial from:
	# http://www.plus2net.com/php_tutorial/gd-linegp.php

	# Bounds and spacing constants
	$x_gap = 1;
	$x_max = (DPTS+1)*$x_gap;
	$y_max = 300;
	$count = 0;
	
	# Collect most recent 24 hours WORTH of temp data
	# Not actually limited to the past 24 hours
	$temps_24h = array_fill(0, DPTS, -500);
	for ($i=0; $i<DPTS; $i++) {
		$temps_24h[$i] = $data[$i]['Temperature'] * YSCALAR;
	}
	
	# Create plot-space
	$ps = @imagecreate($x_max, $y_max)
		or die ("E CANNOT CREATE PLOT SPACE\n");

	# Create some colors to use
	$background_color = imagecolorallocate($ps, 234, 234, 234);
	$text_color = imagecolorallocate($ps, 233, 14, 91);

	# Start plotting points
	$x1 = 0;
	$y1 = 0;
	# Set first_p value as true to prevent drawing line for first point
	$first_p = True;
	# Plot each point
	foreach ($temps_24h as $p) {
		# Calculate x coordinate from previous value and gap-space
		$x2 = $x1 + $x_gap;
		# Calculate y coordinate from plot size and Temp value
		# Bottom of image is y=y_max, so dividing by 2 goes to 
		# middle of plot and subtracting the Temp value gives offical 
		# y coordinate
		$y2 = ($y_max/2) - $p;
		# Draw a line connecting current and previous points if this
		# is not the first point
		if (!$first_p) {
			imageline($ps, $x1, $y1, $x2, $y2, $text_color);
		}
		# Store values for next line-draw
		$x1 = $x2; 
		$y1 = $y2;
		# No longer the first point
		$first_p = False;
	}

	# Overlay a simple grid
	$ps = basic_grid($ps, $x_max, $y_max);

	# Export image and remove from memory
	imagepng($ps);
	imagedestroy($ps);
}

function basic_grid($im, $x_max, $y_max) {
	# Spacing between x-grids -> measured in hours
	$x_spacing = 1;
	# Spacing between y-grids -> measured in degF
	$y_spacing = 10 * YSCALAR;
#	$x_lines = 24;
#	$y_lines  = 15;
	$grid_color = imagecolorallocate($im, 0, 0, 0);

	# Vertical grid lines
	

	# Horizontal grid lines
	# Start with solid y=0degF line
	$y_0 = $y_max / 2;
	imageline($im, 0, $y_0, $x_max, $y_0, $grid_color);
	# Draw remaining horizontal lines
	for ($i=1; 1; $i++) {
		# Check to make sure still in bounds of image
		# This controls exit of loop instead of for-statement
		# Also calculates how far this line will be from y=0
		if (($y = $y_spacing * $i) + $y_0 > $y_max) {
			break;
		}
		# Draw grid lines on either side of y=0
		imagedashedline($im, 0, $y_0-$y, $x_max, $y_0-$y, $grid_color);
		imagedashedline($im, 0, $y_0+$y, $x_max, $y_0+$y, $grid_color);
	}
	
	
	# Vertical grid lines
#	for ($i=1; $i<$x_lines; $i++) {
#		$x = ($x_max / $x_lines) * $i;
#		imagedashedline($im, $x, 0, $x, $y_max, $grid_color);
#	}
	
	# Horizontal grid lines
#	for ($i=1; $i<$y_lines; $i++) {
#		$y = ($y_max / $y_lines) * $i;
#		imagedashedline($im, 0, $y, $x_max, $y, $grid_color);
#	}

	return $im;
}
?>
