<?php
/**
 * Class Controller
 * Controllers will be responsible to get data from the user, manipulate
 * and send it to the Updater.
 * Controllers will be responsible to validate data.
 */
abstract class Controller {
    /**
     * Each method will be action.
     * For each key action the value will be 'does the method require authentication?'
     * true if it is, false if it doesn't.
     * @var array
     */
    public $actions = array();

    /**
     * Controller constructor.
     * @param $acts array   The actions array.
     */
    function __construct($acts){
        $this->actions = $acts;
    }

    /**
     * Validate the data to make sure that we are sending validate data to the updaters.
     * This method will make sure that the data inserted is up to the program requirement.
     * @param $data array   The data we are currently validating.
     */
    abstract protected function validateData($data);
}