#!/bin/bash
# Creates a text output of Temperature Database temp.db
# The output can also be used to restore temperature (T) table

sqlite3 temp.db .dump > DBOUT
