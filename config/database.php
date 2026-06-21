<?php

echo "<pre>";

echo "MYSQLHOST = ";
var_dump(getenv('MYSQLHOST'));

echo "\nMYSQLUSER = ";
var_dump(getenv('MYSQLUSER'));

echo "\nMYSQLDATABASE = ";
var_dump(getenv('MYSQLDATABASE'));

echo "\nMYSQLPORT = ";
var_dump(getenv('MYSQLPORT'));

exit;