<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'gs_rdbms';

$link = new mysqli($host, $user, $password, $database);

if ($link->connect_error) {
    die("Connection failed: " . $link->connect_error);
}
?>