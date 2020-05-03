<?php
/**
 * Class UpdaterWallet
 * Responsible to get/set/update user's wallet information.
 * Wallet information includes the balance for now.
 */
class UpdaterWallet extends Updater {
    /**
     * Class short name.
     * @var string
     */
    private $name = 'wallet';

    /**
     * UpdaterUser constructor.
     * @param $id   int The wallet's ID.
     */
    function __construct($id = 0){
        parent::__construct($this->name, $id);
    }

    /**
     * @inheritDoc
     */
    public function save($data){
        // If this wallet has no ID
        if( !$this->id ) {
            // Make a new database instance
            $db = new Database($this->table_name);
            // Save the `INSERT` result
            $db->insert(array($data['user_id'], $data['balance']), array('user_id', 'balance'));
        } else {
            // Otherwise, we have wallet ID
            // Make a new database instance
            $db = new Database($this->table_name);
            // Save the `INSERT` result
            $db->insert(array($this->id, $data['balance']), array('id', 'balance'));
        }
    }
}