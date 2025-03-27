<?php

// Start de sessie om toegang te krijgen tot de sessievariabelen
session_start();
// Vernietigt alle sessiegegevens
session_destroy();
// Stuurt de gebruiker terug naar de homepage
header("Location: index.html");
exit();

?> 