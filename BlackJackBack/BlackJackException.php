<?php
/**
 * Class BlackJackException
 * Specific Exception class for BlackJack program.
 * The class will know to return the relevant data per Exception.
 */
class BlackJackException extends Exception {
    /**
     * Output the data with error.
     * @param $message  string  The error data itself.
     */
    function outputError($message){
        Output::outputData('',false, $message);
    }
}