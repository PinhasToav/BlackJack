<?php
/**
 * Class ControllerBoard
 * Responsible to get the board data and send it
 * to the updater to store board's used cards, users cards etc.
 */
class ControllerBoard extends Controller{
    /**
     * ControllerBoard constructor.
     */
    function __construct(){
        parent::__construct(array(
            'initboard' => true,
        ));
    }

    /**
     * @inheritDoc
     */
    protected function validateData($data) {
        // Loop through the data we validate
        foreach( $data as $key => $value) {
            // Case of `user_bet` checking
            if ($key === 'user_bet') {
                if( $value > BET_MAX || $value < BET_MIN ) {
                    throw new BlackJackException('Initial bet invalid');
                }
                /* Make sure the user have enough money for this bet */
                $user_instance = new UpdaterUser($data['user_id']);
                // Get user's data
                $user_data = $user_instance->getData();

                // Does the user have enough money?
                if( $user_data['wallet'][0]['balance'] < $value) {
                    throw new BlackJackException('Not enough money for this bet');
                }
            }
        }
    }

    /**
     * Initial new board with the players that play the game.
     * At the moment user can play:
     *                              player.vs.virtual player.vs.dealer.
     */
    public function initBoard(){
        /* Validate data */
        $user_id = Security::validateData('user_id');
        $user_bet = Security::validateData('user_bet');

        $user_id = intval($user_id);
        $user_bet = intval($user_bet);

        Security::validate_number($user_id);
        Security::validate_number($user_bet);

        // Make sure the bet is valid
        $this->validateData(array('user_bet' => $user_bet, 'user_id' => $user_id));

        /* Initial the hands of the players */
        $cards = array();
        // Gets five random cards
        for($i = 0; $i < 5 ; $i ++) {
           $cards[] = getRandomCard($cards);
        }

        // Create new updater board
        $board = new UpdaterBoard();
        $result = $board->save(array(
            'user_id' => $user_id,
            'user_bet' => $user_bet,
            'user_hand' => $cards[0]. '&' .$cards[4],
            'virtual_hand' => $cards[1]. '&' .$cards[3],
            'dealer_hand' => $cards[2]
        ));

        // Return the data
        Output::outputData($result);
    }
}