<?php
session_start();
session_destroy();
header("Location: index.html");  // Changed from index.php to index.html
exit();
?>