<?php
include 'common.php';

class DirectDbBenchmark {

    private $rows;
    private $requests;
    private $dataLength;

    function __construct($rows, $requests, $data_length) {
        $this->rows = $rows;
        $this->requests = $requests;
        $this->dataLength = $data_length;
    }

    public function Run() {
        $db = Connect();
        DropCreateTable($db);
        $singleRow = pg_escape_bytea(random_bytes($this->dataLength));
        $input = array($singleRow, $singleRow);

        $start = microtime(true);
        for ($i = 0; $i < $this->rows; $i++) {
            if (!pg_query_params($db, "INSERT INTO test_raw(plaintext, ciphertext) VALUES ($1, $2)", $input)) {
                throw new Exception("error on INSERT query occurred");
            }
        }
        $time_elapsed_secs = microtime(true) - $start;
        printf("INSERT DB took %f sec\n", $time_elapsed_secs);


        $input = array($singleRow);
        $start = microtime(true);
        for ($i = 0; $i < $this->requests; $i++) {
            $result = pg_query_params($db, "SELECT * FROM test_raw WHERE ciphertext=$1", $input);
            if (!$result) {
                throw new Exception("error on SELECT query occurred");
            }
            if (pg_num_rows($result) != $this->rows) {
                throw new Exception("error on pg_num_rows occurred");
            }
        }
        $time_elapsed_secs = microtime(true) - $start;
        printf("SELECT DB (100%% rows) took %f sec\n", $time_elapsed_secs);


        $start = microtime(true);
        for ($i = 0; $i < $this->requests; $i++) {
            $result = pg_query($db, "SELECT * FROM test_raw WHERE ciphertext='not_existed'");
            if (!$result) {
                throw new Exception("error on SELECT query occurred");
            }
            if (pg_num_rows($result) != 0) {
                throw new Exception("error on pg_num_rows occurred");
            }
        }
        $time_elapsed_secs = microtime(true) - $start;
        printf("SELECT DB (0%% rows) took %f sec\n", $time_elapsed_secs);
    }
}

