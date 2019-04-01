<?php

const DEFAULT_ROWS_COUNT = 100;
const DEFAULT_REQUESTS_COUNT = 1;
const DEFAULT_DATA_LENGTH_BYTES = 10;
const BLOOM_FILTER_BIT_LENGTH = 256;

const DB_CONNECTION_STRING="host=127.0.0.1 port=9393 dbname=test user=test password=test";

function DropCreateTable($db) {
    if (!pg_query($db, "DROP TABLE IF EXISTS test_raw;")) {
        throw new Exception("error on <DROP TABLE IF EXISTS test_raw> query");
    }
    if (!pg_query($db, "DROP SEQUENCE IF EXISTS test_raw_seq;")) {
        throw new Exception("error on <DROP SEQUENCE IF EXISTS test_raw_seq> query");
    }
    if (!pg_query($db, "CREATE SEQUENCE test_raw_seq START 1;")) {
        throw new Exception("CREATE SEQUENCE test_raw_seq START 1");
    }
    if (!pg_query($db, "CREATE TABLE IF NOT EXISTS test_raw(id INTEGER PRIMARY KEY DEFAULT nextval('test_raw_seq'), plaintext BYTEA, ciphertext BYTEA);")) {
        throw new Exception("error on <CREATE TABLE IF NOT EXISTS test_raw(id INTEGER PRIMARY KEY DEFAULT nextval('test_raw_seq'), plaintext BYTEA, ciphertext BYTEA)> query");
    }
}

function DropCreateTableCipherSweet($db) {
    if (!pg_query($db, "DROP TABLE IF EXISTS test_raw_ciphersweet;")) {
        throw new Exception("error on <DROP TABLE IF EXISTS test_raw_ciphersweet> query");
    }
    if (!pg_query($db, "DROP SEQUENCE IF EXISTS test_raw_ciphersweet_seq;")) {
        throw new Exception("error on <DROP SEQUENCE IF EXISTS test_raw_ciphersweet_seq> query");
    }
    if (!pg_query($db, "CREATE SEQUENCE test_raw_ciphersweet_seq START 1;")) {
        throw new Exception("CREATE SEQUENCE test_raw_ciphersweet_seq START 1");
    }
    if (!pg_query($db, "CREATE TABLE IF NOT EXISTS test_raw_ciphersweet(id INTEGER PRIMARY KEY DEFAULT nextval('test_raw_ciphersweet_seq'), plaintext BYTEA, ciphertext BYTEA, blind_index_full BYTEA);")) {
        throw new Exception("error on <CREATE TABLE IF NOT EXISTS test_raw_ciphersweet(id INTEGER PRIMARY KEY DEFAULT nextval('test_raw_ciphersweet_seq'), plaintext BYTEA, ciphertext BYTEA, blind_index_full BYTEA)> query");
    }
}

function Connect() {
    $db_connection = pg_connect(DB_CONNECTION_STRING);
    if (!$db_connection) {
        throw new Exception("connection error");
    }
    return $db_connection;
}


function ValidateParameter($args, $param, $defaultValue) {
    if ($args[$param] < 0) {
        printf("@%s parameter can't be negative. Skip to default\n", $param);
        $args[$param] = $defaultValue;
    }
}