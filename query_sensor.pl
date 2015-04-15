#!/usr/bin/perl
use strict;
use warnings;

# Print Temperature in Degrees Fahrenheit
print get_F_temp()."\n";

# Get raw ADC temperature value from XMEGA-RPi-expansion
sub get_raw_temp {
	# Execute i2c-tools commands to get 2bytes of temp data
	my $command = "sudo i2cget -y 1 0x41 6; sudo i2cget -y 1 0x41 7";
	my @c_ret = `$command`;

	# Return with error if command failed
	return -501 if @c_ret != 2;
	foreach my $line (@c_ret) {
		# Return with error if data is bad
		# Simultaneously take out newline char in case value is good
		return -502 if $line !~ s/^(0x\w\w)\n$/$1/;
	}

	# Convert 12bit ADC value to decimal and return
	return hex($c_ret[1])<<8 | hex($c_ret[0]);
}

# Get raw ADC temperature value and convert to Degrees Kelvin
sub get_K_temp {
	# Conversion factors calculated from calibration data
	my $a = 0.167019209;
	my $b = -34.205534;
	# Get raw ADC temperature value
	my $raw_temp = get_raw_temp();

	# Convert raw ADC temperature value to Kelvin if value is valid
	return $raw_temp if $raw_temp < 0;
	return $a*$raw_temp + $b;
}

# Get raw ADC temperature value and convert to Degrees Fahrenheit
sub get_F_temp {
	# Get Kelvin temperature value
	my $K_temp = get_K_temp();

	# Convert Kelvin temperature value to Fahrenheit if value is valid
	return $K_temp if $K_temp < 0;
	return ($K_temp - 273.15)*1.8 + 32.0;
}
