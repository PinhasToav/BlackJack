<?php
/**
 * Class UpdaterBoard
 * Responsible to get board data, store board data,
 * and other board uses.
 */
class UpdaterBoard extends Updater{
    /**
     * Class short name.
     * @var string
     */
    private $name = 'board';

    /**
     * UpdaterBoard constructor.
     * @param $id   int The board ID.
     */
    function __construct($id = 0){
        parent::__construct($this->name, $id);
    }

    /**
     * @inheritDoc
     */
    public function save($data){
        /* Gets the data if it's exist */
        // Board
        $board_id = isset($data['board_id']) ? $data['board_id'] : ( (!$this->id) ? -1 : $this->id );
        // User
        $user_id = $data['user_id'];
        $user_bet = $data['user_bet'];
        $user_hand = $data['user_hand'];
        $user_status = isset($data['user_status']) ? $data['user_status'] : '0';
        // Virtual
        $virtual_bet = isset($data['virtual_bet']) ? $data['virtual_bet'] : $user_bet;
        $virtual_hand = $data['virtual_hand'];
        $virtual_status = isset($data['virtual_status']) ? $data['virtual_status'] : '0';
        // Dealer
        $dealer_bet = isset($data['dealer_bet']) ? $data['dealer_bet'] : $user_bet;
        $dealer_hand = $data['dealer_hand'];
        $dealer_status = isset($data['dealer_status']) ? $data['dealer_status'] : '0';

        // Do we have board ID?
        if( $board_id === -1 ){
            /* We don't have, check if the user have game on process */
            // Make a new database instance
            $db = new Database($this->table_name);
            // Save the `SELECT` result
            $result = $db->select(array($user_id, 1), array('user_id', 'status'), array('id'));

            // Do we need to create new game for this user?
            if( !$result['success'] ) {
                // We do, create the new game
                // Make a new database instance
                $db = new Database($this->table_name);
                // `INSERT` the data
                $db->insert(array($user_id, $user_bet), array('user_id', 'entrance_fee'));
                // Save the new board ID
                $board_id = $db->getLastKeyInserted('id');
            } else {
                // We don't need to create a new game for this user, he has a game on going
                // Gets the board ID
                $board_id = $result[0]['id'];
                // Gets the board players
                $board_players = $this->getBoardPlayers($board_id);

                return array(
                    'board_id' => $board_id,
                    'player' => $board_players
                );
            }
        }

        // Create the players instances
        $player = new UpdaterPlayer(1, $user_id);
        $virtual = new UpdaterPlayer(2);
        $dealer = new UpdaterPlayer(3);

        // Save the data
        $player->save(array($board_id, $player->id, $user_hand, $user_bet, $user_status));
        $virtual->save(array($board_id, $virtual->id, $virtual_hand, $virtual_bet, $virtual_status));
        $dealer->save(array($board_id, $dealer->id, $dealer_hand, $dealer_bet, $dealer_status));

        // Return the data
        return array(
            'board_id' => $board_id,
            'player' => array(
                array(
                    'user_id' => $user_id,
                    'hand' => $user_hand,
                    'bet' => $user_bet,
                    'status' => $user_status,
                ),
                array(
                    'user_id' => PLAYER_TYPE[2],
                    'hand' => $virtual_hand,
                    'bet' => $virtual_bet,
                    'status' => $virtual_status,
                ),
                array(
                    'user_id' => PLAYER_TYPE[3],
                    'hand' => $dealer_hand,
                    'bet' => $dealer_bet,
                    'status' => $dealer_status,
                ),
            )
        );
    }

    /**
     * Gets the board players using board ID.
     * @param $board_id int         The board id.
     * @return array $result        The data.
     * @throws BlackJackException   In case of error in the `select` section.
     */
    public function getBoardPlayers($board_id = 0){
        // Gets this board id if set.
        if(!$board_id) $board_id = $this->id;

        // Make a new database instance
        $db = new Database('player');
        // Save the `SELECT` result
        $result = $db->select(array($board_id), array('board_id'), array('user_id', 'hand', 'bet', 'status'));
        unset($result['success']);

        // Make sure we return sorted array when arr[0] is user etc..
        function cmp( $a, $b ) {
            return ($a['user_id'] > $b['user_id']) ? -1 : 1;
        }

        usort($result,'cmp');

        return $result;
    }

    /**
     * Close a game,
     * This method will be called when a game is finished.
     * @param $data array   The board ID and user ID.
     * @throws BlackJackException   In case of exception in the DB calls, catches in the index.php.
     */
    public function finishGame($data) {
        $board_id = $data['board_id'];
        $user_id = $data['user_id'];

        // Make a new database instance
        $db = new Database($this->table_name);
        // `UPDATE` the data
        $db->insert(array($board_id, $user_id, 0), array('id', 'user_id', 'status'));
    }
}