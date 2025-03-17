<?php
session_start();
session_destroy();
header("Location: klant-login.php");
exit();
