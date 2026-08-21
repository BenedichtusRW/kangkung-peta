<?php
require 'config_db.php';
$pdo = getDB();
$s = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_ASSOC);
print_r($s);
