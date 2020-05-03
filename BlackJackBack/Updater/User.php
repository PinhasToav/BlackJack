<?php
/**
 * Class UpdaterUser
 * UpdaterUser Class will be responsible to sign in, sign up new users etc..
 */
class UpdaterUser extends Updater {
    /**
     * Class short name.
     * @var string
     */
    private $name = 'user';

    /**
     * UpdaterUser constructor.
     * @param $id int   The user's ID.
     */
    function __construct($id = 0){
        parent::__construct($this->name, $id);
    }

    /**
     * @inheritDoc
     */
    public function getData() {
        /* Gets the user wallet information */
        // Make a new database instance
        $db = new Database('wallet');
        // Save the `SELECT` result
        $result_wallet =  $db->select(array($this->id), array('user_id'));

        // Return the user's data
        return array(
            'user' => parent::getData(),
            'wallet' => $result_wallet
        );
    }

    /**
     * Check if the user that tries to log in exist.
     * @param $data array   The data to for the request.
     * @return boolean      `true` if the user exist in the DB, otherwise `false`.
     * @throws BlackJackException   Throws an exception if login failed
     */
    public function login($data) {
        // Encrypt the password
        $data['password'] = sha1($data['password']);
        // Make a new database instance
        $db = new Database($this->table_name);
        // Save the `SELECT` result
        $result = $db->select($data, array('email', 'password'));

        $random_string = '';
        // Do we have the user in the DB?
        if( $result['success'] ) {
            // Save cookie
            $cookie = new UpdaterCookie();
            $random_string = $cookie->save(array('user_id' => $result[0]['id']));

            /* Do we have wallet for the user? */
            // Make a new database instance
            $db = new Database('wallet');
            // Save the `SELECT` result
            $result_wallet_select = $db->select(array($result[0]['id']), array('user_id'));
            // Data is empty?
            if( !$result_wallet_select['success'] ){
                // We have no wallet for this user, initial new wallet for the user
                $result_wallet_insert = $db->insert(array($result[0]['id'], 50), array('user_id','balance'));
            }
        }

        // Add the random string value to the result
        $result['random_string'] = $random_string;
        // Return the value
        return $result;
    }

    /**
     * Sign up a new user
     * @param $data The data for the request
     * @return boolean      `true` if the user successfully created and saved in the DB, otherwise `false`.
     * @throws BlackJackException   In case of invalid data (Email already exist, etc.)
     */
    public function register($data){
        // Encrypt the password
        $data[2] = sha1($data[2]);

        // Make a new database instance
        $db = new Database($this->table_name);
        // Save the `INSERT` result
        $result = $db->insert($data, array('email', 'full_name', 'password', 'age'));

        // Check if user successfully register so we will create a wallet for him
        if( $result ) {
            // Gets the user we have just created ID
            $user_id = $db->getLastKeyInserted('id');

            // Update the wallet
            $wlt = new UpdaterWallet();
            $wlt->save(array('user_id' => $user_id, 'balance' => PLAYER_INIT_BALANCE));
        }

        // Return the value
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function save($data) {}

    /**
     * Gets the user information from the backend.
     * @param $data array   The user ID.
     * @return array        The data.
     * @throws BlackJackException   Throws an exception in case of error in DB.
     */
    public function myAccount($data) {
        $this->id = $data[0];

        // Make a new database instance
        $db = new Database('wallet');
        // Save the `SELECT` result
        $result_wallet =  $db->select(array($this->id), array('user_id'), array('balance'));
        if( !$result_wallet['success'] ) throw new BlackJackException('Cannot get user data');
        unset($result_wallet['success']);

        // Make a new database instance
        $db = new Database('player');
        // Save the `SELECT` result
        $result_user =  $db->select(array($this->id), array('user_id'), array('status'));
        if( !$result_user['success'] ) throw new BlackJackException('Cannot get user data');
        unset($result_user['success']);

        // Save the `SELECT` result
        $result_virtual =  $db->select(array(-1), array('user_id'), array('status'));
        if( !$result_virtual['success'] ) throw new BlackJackException('Cannot get user data');
        unset($result_virtual['success']);

        // Return the data
        return array('wallet' => $result_wallet, 'user' => $result_user, 'virtual' => $result_virtual);
    }
}