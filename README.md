# CipherSweetBenchmarks

1) install ciphersweet (https://github.com/paragonie/ciphersweet). If you already have PHP installed it needs: 

`composer require paragonie/ciphersweet`

2) run benchmarks:

`php run.php` - with default benchmark parameters.

Or you can specify benchmark parameters, for example:

`php run.php --rows=200 --requests=3 --data_length=100` - this will inserts 200 equal records with 100 bytes length and selects them 3 times.



