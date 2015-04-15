#!/usr/bin/perl
use strict;
use warnings;
use DBI;

# The form of this script is used from:
# http://www.tutorialspoint.com/sqlite/sqlite_perl.htm

# Initial constant values
my $driver = "SQLite";
my $database = "temp.db";
my $dsn = "DBI:$driver:dbname=$database";
my $userid = "";
my $password = "";
my $table = "T";
my $column = "Temperature";
my $create_table_stmt = "CREATE TABLE IF NOT EXISTS $table(
	Date TEXT DEFAULT (DATE('now', 'localtime')) NOT NULL, 
	Time TEXT DEFAULT (TIME('now', 'localtime')) NOT NULL, 
	$column REAL);";
my $update_table_stmt = "INSERT INTO $table($column) ";
my $query = "./query_sensor.pl";

# Open database or create one if one doesn't exist
my $dbh = DBI->connect($dsn, $userid, $password, 
	{RaiseError => 1, PrintError => 0}) or die $DBI::errstr;
print "Opened $database database successfully\n";

# Create table if necessary
$dbh->do($create_table_stmt) or die $DBI::errstr;

# Sample and update table every minute
while(1) {
	# Take a sample
	my $temp = `$query`;
	# If sample is valid, insert temp, otherwise insert NULL
	$dbh->do($update_table_stmt."VALUES(".
		($temp > -500 ? $temp:"NULL").
		");") or warn $DBI::errstr;
	sleep 60;	
}

# Explicitly disconnect (should never happen)
$dbh->disconnect();
