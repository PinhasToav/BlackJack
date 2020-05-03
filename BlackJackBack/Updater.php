<?php
/**
 * Class Updater
 * Updaters will be responsible to gather the info from the database.
 */
abstract class Updater {
    /**
     * The table name.
     * @var string
     */
    protected $table_name;

    /**
     * The specific updater id.
     * @var int
     */
    public $id;

    /**
     * Updater constructor.
     * @param $name string  The table name will be `$name`.
     * @param $id   string  The specific Updater ID.
     */
    function __construct($name, $id){
        $this->table_name = $name;
        $this->id = $id;
    }

    /**
     * Gets the data for a specific class,
     * This functionality will make sure that we can bring back the whole data
     * for each updater.
     */
    public function getData() {
        // Do we have an ID?
        if( $this->id !== 0) {
            // Make a new database instance
            $db = new Database($this->table_name);
            // Save the `SELECT` result
            return $db->select(array($this->id), array('id'));
        } else {
            // We have no ID
            throw new BlackJackException('Cannot getData() without ID');
        }
    }

    /*
     * Save method will stand for either create or update.
     * @param $data array   Array of data.
     */
    public abstract function save($data);
}