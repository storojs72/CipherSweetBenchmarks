<?php
include 'vendor/autoload.php';
include 'benchmarks/direct_postgres.php';
include 'benchmarks/ciphersweet_postgres.php';

$my_args = array();
$my_args["rows"] = DEFAULT_ROWS_COUNT;
$my_args["requests"] = DEFAULT_REQUESTS_COUNT;
$my_args["data_length"] = DEFAULT_DATA_LENGTH_BYTES;
for ($i = 1; $i < count($argv); $i++) {
    if (preg_match('/^--([^=]+)=(.*)/', $argv[$i], $match)) {
        $my_args[$match[1]] = $match[2];
    }
}

ValidateParameter($my_args, "rows", DEFAULT_ROWS_COUNT);
ValidateParameter($my_args, "requests", DEFAULT_REQUESTS_COUNT);
ValidateParameter($my_args, "data_length", DEFAULT_DATA_LENGTH_BYTES);

$directDb = new DirectPostgresBenchmark($my_args["rows"], $my_args["requests"], $my_args["data_length"]);
$directDb->Run();

$cipherSweetDb = new CipherSweetPostgresBenchmark($my_args["rows"], $my_args["requests"], $my_args["data_length"]);
$cipherSweetDb->Run();




