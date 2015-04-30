#!/bin/bash
# Creates a text output of Temperature Database temp.db.
# The output can also be used to restore temperature (T) table.
# This script only exists for human benefit: it serves no purpose in the 
# automated process.

sqlite3 /var/www/temp.db .dump > DBOUT
