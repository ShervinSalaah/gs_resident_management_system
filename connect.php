<?php
// New InfinityFree credentials
$host = 'sql306.infinityfree.com';  
$user = 'if0_41508821';             
$password = 'wVSvWaHdz3NUF';       
$database = 'if0_41508821_gs_rdbms'; 

$link = new mysqli($host, $user, $password, $database);

if ($link->connect_error) {
    die("Connection failed: " . $link->connect_error);
}
?>