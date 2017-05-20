<?php

include './vendor/autoload.php';

use Dfcplc\ePostcode\ePostcode;

var_dump(ePostcode::lookup_address('', '', 'AB1 2CD', '1'));