<?php
require_once('init.php');

// Gets the full url
$url = $_SERVER['REQUEST_URI'];
// Save the url splited by `/` character
$url = explode('/', $url);
// Initial some variables
$folder = $url[count($url)-3];
$class = $url[count($url)-2];
$method = $url[count($url)-1];

try {
    /* Validate the data */
    $folder = Security::validateData('folder', $folder, false);
    $class = Security::validateData('class', $class, false);
    $method = Security::validateData('method', $method, false);

    // Upper/lower case the data
    $folder = ucfirst(strtolower($folder));
    $class = ucfirst(strtolower($class));
    $method = strtolower($method);

    // There is an access to controllers only (MVC)
    if ($folder === 'Controller') {
        // Do we have the class we need in the folder?
        if (in_array($class . '.php', scandir($folder))) {
            // We do, include the file
            require_once(__DIR__ . "\\$folder.php");
            require_once(__DIR__ . "\\$folder\\$class.php");
            // Does the method exist in the class?
            if (is_callable($folder . $class, $method)) {
                // It is, make an instance of object
                $class_of_instance = $folder . $class;
                $instance = new $class_of_instance();

                // Do we have the method in actions array?
                /* NEEDED TO REMIND THE PROGRAMMER TO ADD EACH METHOD TO THE ACTIONS ARRAY */
                if (array_key_exists($method, $instance->actions)) {
                    // The method does'nt require authentication?
                    if (!$instance->actions[$method]) {
                        // It doesn't, call the method
                        $instance->$method();
                        // Otherwise, it is
                    } else {
                        // Does the user authenticated?
                        if( Security::isAuthenticated() ) {
                        // He is, call the method
                        $instance->$method();
                        }
                    }
                }
            }
        }
    }
} catch (BlackJackException $e){
    // If we have `BlackJackException`
    $e->outputError($e->getMessage());
} catch (Exception $e) {
    // Otherwise, we have unknown error
    Output::outputData('Cannot access '.$folder.'/'.$class.'/'.$method, false, $e->getMessage());
}
