<?php
/**
 * Initial the program.
 */

// Headers
header('Access-Control-Allow-Origin: *');

// Session
session_start();

// Include files
require_once('Constants.php');
require_once('Security.php');
require_once('Database.php');
require_once('Output.php');
require_once('autoloader.php');
require_once('BlackJackException.php');
require_once('Functions.php');