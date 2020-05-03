<?php
// Autoloader part
spl_autoload_register('myAutoLoader');
function myAutoLoader($file_name){
    // Set the variables
    $file_name = substr($file_name, 7);
    $folder = 'Updater';
    $path = __DIR__ . "\\$folder\\$file_name.php";

    require_once(__DIR__ . "\\$folder.php");
    require_once($path);
}