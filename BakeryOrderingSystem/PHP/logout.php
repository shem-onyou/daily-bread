<?php
session_start();
session_destroy();
header("Location: /BakeryOrderingSystem/PHP/index.php");
exit;
?>
