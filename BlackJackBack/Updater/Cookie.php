<?php
/**
 * Class UpdaterCookie.
 * Responsible for saving and getting information about cookies form the DB.
 */
class UpdaterCookie extends Updater {
    /**
     * Class short name.
     * @var string
     */
    private $name = 'cookie';

    /**
     * UpdaterUser constructor.
     * Cookie has no ID.
     */
    function __construct($id = 0){
        parent::__construct($this->name, $id);
    }

    /**
     * @inheritDoc
     */
    public function save($data){
        // We do, set the cookie variable
        $random_string = generateRandomString();
        // Encrypt the cookie
        $random_string_encrypted = sha1($random_string);

        // Make a new database instance
        $db = new Database($this->table_name);
        // Save the `INSERT` result
        $result = $db->insert(array($data['user_id'], $random_string_encrypted), array('user_id', 'cookie'));

        // Return the random string
        if( $result ) return $random_string;
        // Otherwise, error occure
        else throw new BlackJackException('Invalid user ID, cannot find the cookie');
    }

    /**
     * Check if there is a cookie in the DB for the user.
     * @param $data     The cookie data (ID).
     * @return array    Return array result.
     * @throws BlackJackException   Throws exception in case of error in DB.
     */
    public function checkCookie($data){
        // Make a new database instance
        $db = new Database($this->name);
        // Save the `SELECT` result
        $result = $db->select(array(sha1($data['cookie'])), array('cookie'));

        // Check if the cookie is valid
        if( !$result['success'] ) {
            throw new BlackJackException('Invalid cookie');
        } else {
            // Otherwise, return it
            return $result;
        }
    }

    /**
     * Gets the user data by cookie ID.
     * @param $data array       The cookie information.
     * @return array            The user's data.
     * @throws BlackJackException   Throws exception in case of error in DB.
     */
    public function getDataByCookie($data){
        $result = $this->checkCookie(array('cookie' => $data['cookie']));

        // Make a new database instance
        $db = new Database('user');
        // Save the `SELECT` result
        return $db->select(array($result[0]['user_id']), array('id'), array('id', 'full_name'));
    }
}