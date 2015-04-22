<?php
header("Content-Type: image/png");
# Number of data points in 24 hours where 1 point=1 minute
define ("DPTS", 1440);
# YSCALAR => how many pts per degF
define ("YSCALAR", 1);
# XSCALAR => how many pts per minute
define ("XSCALAR", 1);
define ("FONT", 
	"/usr/share/fonts/truetype/freefont/FreeMonoOblique.ttf");
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

function basic_graph($data) 
{
	# Follows tutorial from:
	# http://www.plus2net.com/php_tutorial/gd-linegp.php

	# Lower bounds and frame sizes of image
	$x_min = 0;
	$y_min = 0;
	$x_left_frame = 100;
	$x_right_frame = 10;
	$y_top_frame = 10;
	$y_bottom_frame = 100;
	$x_plot_gap = 1 * XSCALAR;

	# Bounds of plot
	$x_plot_min = $x_min + $x_left_frame;
	$x_plot_max = (DPTS+1) * $x_plot_gap + $x_plot_min;
	$y_plot_min = $y_min + $y_top_frame;
	$y_plot_max = 300 + 1 + $y_plot_min;

	# Upper bounds of Image
	$x_max = $x_plot_max + $x_right_frame;
	$y_max = $y_plot_max + $y_bottom_frame;
	
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
	$x1 = $x_plot_min;
	$y1 = $y_plot_min;
	# Set first_p value as true to prevent drawing line for first point
	$first_p = True;
	# Plot each point
	foreach ($temps_24h as $p) {
		# Calculate x coordinate from previous value and gap-space
		$x2 = $x1 + $x_plot_gap;
		# Calculate y coordinate from plot size and Temp value
		# Bottom of image is y=y_max, so dividing by 2 goes to 
		# middle of plot and subtracting the Temp value gives offical 
		# y coordinate
		$y2 = (($y_plot_max-$y_plot_min)/2) + $y_plot_min - $p;
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
	$ps = basic_grid($ps, $x_plot_min, $x_plot_max, $y_plot_min, 
								$y_plot_max);

	# Add axis labels
	$axis_label_color = imagecolorallocate($ps, 0, 0, 0);
	imagettftext($ps, 20, 90, $x_min+30, $y_max*.60, $axis_label_color, 
						FONT, 'DEGREES (F)');
	imagettftext($ps, 20, 0, $x_max*.45, $y_max-10, $axis_label_color, 
						FONT, 'TIME FROM NOW (Hr)');

	# Export image and remove from memory
	imagepng($ps);
	imagedestroy($ps);
}

function basic_grid($im, $x_min, $x_max, $y_min, $y_max) 
{
	# Spacing between x-grids -> measured in minutes
	$x_spacing = 60 * XSCALAR;
	# Spacing between y-grids -> measured in degF
	$y_spacing = 10 * YSCALAR;
	$grid_color = imagecolorallocate($im, 0, 0, 0);

	# Vertical grid lines
	for ($i=0; 1; $i++) {
		# Start @ "current" time (far right is x_max and longest ago)
		# Draw lines from left to right, do not go beyond x_max
		# This controls exit of loop instead of for-statement
		if (($x = ($x_spacing * $i) + $x_min) > $x_max) {
			break;
		}
		imagedashedline($im, $x, $y_min, $x, $y_max, $grid_color);

		# X grid labels
		imagettftext($im, 6, 45, $x-5, $y_max+17, $grid_color, 
							FONT, "-$i");

	}

	# Horizontal grid lines
	# Start with solid y=0degF line
	$y_0 = ($y_max - $y_min)/2 + $y_min;
	imageline($im, $x_min, $y_0, $x_max, $y_0, $grid_color);
	# Draw remaining horizontal lines
	for ($i=1; 1; $i++) {
		# Check to make sure still in bounds of image
		# This controls exit of loop instead of for-statement
		# Also calculates how far this line will be from y=0
		if (($y = $y_spacing * $i) + $y_0 > $y_max) {
			break;
		}
		# Draw grid lines on either side of y=0
		imagedashedline($im, $x_min, $y_0-$y, $x_max, $y_0-$y, 
								$grid_color);
		imagedashedline($im, $x_min, $y_0+$y, $x_max, $y_0+$y, 
								$grid_color);
		# Y grid labels
		$label = $i * $y_spacing;
		imagettftext($im, 6, 0, $x_min-16, $y_0-$y+3, $grid_color, 
							FONT, "$label");
		imagettftext($im, 6, 0, $x_min-21, $y_0+$y+3, $grid_color, 
							FONT, "-$label");
	}

	return $im;
}
?>
