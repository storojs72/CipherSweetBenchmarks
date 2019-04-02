<?php

use ParagonIE\CipherSweet\Backend\ModernCrypto;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\KeyProvider\StringProvider;
use ParagonIE\CipherSweet\EncryptedField;
use ParagonIE\CipherSweet\BlindIndex;

class CipherSweetPostgresBenchmark {

    private $rows;
    private $requests;
    private $dataLength;
    private $cipherSweet;

    function __construct($rows, $requests, $data_length) {
        $this->rows = $rows;
        $this->requests = $requests;
        $this->dataLength = $data_length;
        $this->cipherSweet = new CipherSweet(new StringProvider(new ModernCrypto(), '4e1c44f87b4cdf21808762970b356891db180a9dd9850e7baf2a79ff3ab8a2fc'));
    }

    function Run() {
        $db = Connect();
        DropCreateTableCipherSweet($db);

        $dataUnit = random_bytes($this->dataLength);
        $encryptedField = (new EncryptedField($this->cipherSweet, 'test_raw_ciphersweet', 'ciphertext'))
        ->addBlindIndex(new BlindIndex('ciphertext_index', [], BLOOM_FILTER_BIT_LENGTH));

        $input = [];
        $start = microtime(true);
        for ($i = 0; $i < $this->rows; $i++) {
            list($ciphertext, $indexes) = $encryptedField->prepareForStorage($dataUnit);
            $input[0] = '\x'.bin2hex($dataUnit);
            $input[1] = '\x'.bin2hex($ciphertext);
            $input[2] = '\x'.$indexes['ciphertext_index']['value'];
            if (!pg_query_params($db, "INSERT INTO test_raw_ciphersweet(plaintext, ciphertext, blind_index_full) VALUES ($1, $2, $3)", $input)) {
                throw new Exception("error on INSERT query occurred");
            }
        }
        $time_elapsed_secs = microtime(true) - $start;
        printf("INSERT CIPHERSWEET took %f sec\n", $time_elapsed_secs);

        $input = [];
        $start = microtime(true);
        for ($i = 0; $i < $this->requests; $i++) {
            $indexValue = $encryptedField->getBlindIndex($dataUnit, 'ciphertext_index');
            $input[0] = '\x'.$indexValue['value'];
            $result = pg_query_params($db, "select id, plaintext, ciphertext from test_raw_ciphersweet where blind_index_full=$1", $input);
            if (!$result) {
                throw new Exception("error on SELECT query occurred");
            }
            if (pg_num_rows($result) != $this->rows) {
                throw new Exception("error on pg_num_rows occurred");
            }
            $result = pg_fetch_all($result);
            //Decryption
            foreach ($result as $row) {
                $decrypted = $encryptedField->decryptValue(hex2bin(substr($row['ciphertext'], 2)));
                if ($decrypted != $dataUnit)
                {
                    throw new Exception("error on decryption occurred");
                }
            }
        }
        $time_elapsed_secs = microtime(true) - $start;
        printf("SELECT CIPHERSWEET (100%% rows) took %f sec\n", $time_elapsed_secs);

        $start = microtime(true);
        for ($i = 0; $i < $this->requests; $i++) {
            // Include time of getting blind index
            $encryptedField->getBlindIndex($dataUnit, 'ciphertext_index');
            $result = pg_query($db, "select id, plaintext, ciphertext from test_raw_ciphersweet where blind_index_full='not_existed'");
            if (!$result) {
                throw new Exception("error on SELECT query occurred");
            }
            if (pg_num_rows($result) != 0) {
                throw new Exception("error on pg_num_rows occurred");
            }
        }
        $time_elapsed_secs = microtime(true) - $start;
        printf("SELECT CIPHERSWEET (0%% rows) took %f sec\n", $time_elapsed_secs);

        pg_close($db);
    }
}
