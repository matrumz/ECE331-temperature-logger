#!/usr/bin/perl
use strict;
use warnings;

print get_raw_temp()."\n";

sub get_raw_temp {
	my $command = "sudo i2cget -y 1 0x41 6; sudo i2cget -y 1 0x41 7";
	my @c_ret = `$command`;
	return -1 if @c_ret != 2;
	foreach my $line (@c_ret) {
		return -2 if $line !~ s/^(0x\w\w)\n$/$1/;
	}
	return hex($c_ret[1])<<8 | hex($c_ret[0]);
}
