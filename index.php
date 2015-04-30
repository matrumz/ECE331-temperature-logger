<!-- 	
	This is the home-page of ECE 331 Project #2.
	It simply creates a page titled RUMSEY Temp Logger, with a background
	of Sheaff's head, and a .png image of the temperature graph generated
	by plotter.php

	It has been successfully tested on 
		OS X: Firefox & Chrome
		Raspbian: Midori and Dillo

	Note:
		If page loads slowly (i.e. plot generation takes too long 
		over current internet connection) the string "PLOT FAILED"
		may appear temporarily until the .png image is generated
		and transferred to the web-broswer. The plot will then appear
		without any user interaction necessary.
-->
<HTML>
<TITLE>RUMSEY Temp Logger</TITLE>
<BODY background="midsheaff.jpg" alt="background">
<?php
echo '<img src="plotter.php" alt="PLOT FAILED"/>';
?>
</BODY>
</HTML>
