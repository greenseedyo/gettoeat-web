<?php
require_once 'config.php';

$staff = Staff::getByStoreIdAndCode($store->id, $_GET['code']);
echo $staff->name ?: '';
