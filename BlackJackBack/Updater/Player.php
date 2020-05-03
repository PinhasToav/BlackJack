<?php
/**
 * Class UpdaterPlayer
 * This class will be responsible to update player in game status, update hes bet, etc..
 */
class UpdaterPlayer extends Updater {
    /**
     * Class short name.
     * @var string
     */
    private $name = 'player';

    /**
     * The player type
     * @var int
     */
    private $type;

    /**
     * UpdaterUser constructor.
     * @param $id int   The player/virtual/dealer ID.
     * @param $type int The Player type, 1 - player, 2 - virtual player, 3 - dealer.
     * @throws BlackJackException In case of invalid type player value.
     */
    function __construct($type = 1, $id = 0){
        // Check the player type
        if( array_key_exists($type, PLAYER_TYPE) ){
            // It is, set the variables
            $this->type = $type;
            // Set ID for virtual player and dealer
            $id = PLAYER_TYPE[$type] < 0 ? PLAYER_TYPE[$type]: $id;
        } else {
            // Can't use this type of user
            throw new BlackJackException('Cannot initial user with type value '.$type);
        }
        parent::__construct($this->name, $id);
    }

    /**
     * @inheritDoc
     */
    public function save($data) {
        // Make a new database instance
        $db = new Database($this->table_name);
        // Save the `INSERT` result
        $result = $db->insert($data, array('board_id', 'user_id', 'hand', 'bet', 'status'));

        // Return the value
        return array(
            'board_id' => $data[0],
            'player' => array(
                array(
                    'user_id' => $data[1],
                    'hand' => $data[2],
                    'bet' => $data[3],
                    'status' => $data[4]
                )
            ),
            'success' => $result
        );
    }
}