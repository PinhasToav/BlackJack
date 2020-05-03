<?php
/**
 * Class ControllerPlayer
 * ControllerPlayer class will be responsible for any player movements in the game,
 * Player can `HIT`, `DOUBLE`, `SURRENDER`, etc..
 * For each move that the user doing the virtual player and dealer will play they'r turn as well if needed.
 */
class ControllerPlayer extends Controller {

    function __construct() {
        parent::__construct(array(
            'hit' => true,
            'stand' => true,
            'double' => true,
            'surrender' => true,
            'split' => true,
            'playvirtualturns' => true,
        ));
    }

    /**
     * @inheritDoc
     * Check list:
     * - Is the game still running? or finished already?
     * - Does the player still playing? Or he finished hes turn?
     * - Does the player hand is greater then 21?
     */
    protected function validateData($data) {
        // Create an instance of the current board object
        $current_board = new UpdaterBoard($data['board_id']);
        // Gets board data
        $board_data = $current_board->getData();

        // Does the game still on going?
        if( !$board_data['success'] || !$board_data[0]['status'] ) {
            throw new BlackJackException('Cannot '.debug_backtrace()[1]['function'].', game finished');
        }

        // Gets the current board's players
        $current_players = $current_board->getBoardPlayers();

        // Get the user statuses
        $user_statuses = explode('||', $current_players[0]['status']);
        $counter = 0;

        // Loop through the status
        foreach( $user_statuses as $status ) {
            // Can the player hit?
            if ($status !== PLAYER_STATUS['playing']) {
                $counter++;
            }
        }

        // Check if all the statuses are `done`
        if( count($user_statuses) === $counter ) {
            throw new BlackJackException('Cannot ' . debug_backtrace()[1]['function'] . ', you have finished the turn');
        }

        // Get the user hands
        $user_hands = explode('||', $current_players[0]['hand']);
        $counter = 0;

        // Loop through the hands
        foreach( $user_hands as $hand ) {
            // Get the user hands
            $user_hand = explode('&', $hand);
            // Sum player hand
            $user_sum = sumPlayerHand($user_hand);

            // Check if user hand is burned
            if (count($user_sum) === PLAYER_SUM_EMPTY) {
                $counter++;
            }
        }

        if( count($user_hands) === $counter ) {
            throw new BlackJackException('Cannot ' . debug_backtrace()[1]['function'] . ', You reached more then 21');
        }

        // Return validated data
        return array(
            'current_board' => $current_board,
            'current_players' => $current_players
        );
    }

    /**
     * User choose to `HIT`, gives him one more card.
     */
    public function hit() {
        /* Validate data */
        $user_id = Security::validateData('user_id');
        $board_id = Security::validateData('board_id');

        $user_id = intval($user_id);
        $board_id = intval($board_id);

        Security::validate_number($user_id);
        Security::validate_number($board_id);

        // Make sure the user can do the action `HIT`
        $validated_data = $this->validateData(array('board_id' => $board_id));
        $current_players = $validated_data['current_players'];

        // Gets the player's hand
        $user_hands =  explode('||', $current_players[0]['hand']);
        $user_statuses =  explode('||', $current_players[0]['status']);
        $user_cards = [];

        // Gets the cards in the player's hand so we can get random card
        foreach( $user_hands as $hand ) {
            $user_cards = array_merge($user_cards,(explode('&', $hand)));
        }

        // Loop through the statuses
        for( $i = 0; $i < count($user_statuses); $i++ ) {
            // In case of editable status do `HIT`
            if( $user_statuses[$i] === PLAYER_STATUS['playing'] ) {
                /* The player can hit! */
                // Gets the rest of the players' hands
                $virtual_hand = explode('&', $current_players[1]['hand']);
                $dealer_hand = explode('&', $current_players[2]['hand']);

                // Gives the user another card
                $random_card = getRandomCard(array_merge($user_cards, $virtual_hand, $dealer_hand));
                // Save the new card in the current hand as array to check the sum
                $user_hand = array_merge(explode('&', $user_hands[$i]),array($random_card));
                // Add the new card to the current player hand
                $user_hands[$i] .= '&'. $random_card;

                // Sum player hand to know if hes `status` have been changed
                $user_sum = sumPlayerHand($user_hand);

                // Check the sum
                if( count($user_sum) === PLAYER_SUM_EMPTY ) {
                    $user_statuses[$i] = PLAYER_STATUS['lose'];
                } else {
                    $user_statuses[$i] = PLAYER_STATUS['playing'];
                }
                break;
            }
        }

        // Turn the arrays to strings
        $hand = controller_arrayToString($user_hands);
        $status = controller_arrayToString($user_statuses);

        // Update the player data
        $player = new UpdaterPlayer(1, $user_id);
        $result = $player->save(
            array(
                $board_id,
                $user_id,
                $hand,
                $current_players[0]['bet'],
                $status
            )
        );

        // Return the data
        Output::outputData($result);
    }

    /**
     * User choose to `STAND`.
     */
    public function stand() {
        /* Validate data */
        $user_id = Security::validateData('user_id');
        $board_id = Security::validateData('board_id');

        $user_id = intval($user_id);
        $board_id = intval($board_id);

        Security::validate_number($user_id);
        Security::validate_number($board_id);

        // Make sure the user can do the action `STAND`
        $validated_data = $this->validateData(array('board_id' => $board_id));
        $current_board = $validated_data['current_board'];
        $current_players = $validated_data['current_players'];

        $user_statuses =  explode('||', $current_players[0]['status']);

        // Loop through the statuses
        for( $i = 0; $i < count($user_statuses); $i++ ) {
            // In case of editable status do `STAND`
            if( $user_statuses[$i] === PLAYER_STATUS['playing'] ) {
                // Change the user status
                $user_statuses[$i] = PLAYER_STATUS['waiting'];
                break;
            }
        }

        // Turn the array to string
        $status = controller_arrayToString($user_statuses);

        // Update the player data
        $player = new UpdaterPlayer(1, $current_players[0]['user_id']);
        $result = $player->save(
            array(
                $current_board->id,
                $current_players[0]['user_id'],
                $current_players[0]['hand'],
                $current_players[0]['bet'],
                $status
            )
        );

        // Return the data
        Output::outputData($result);
    }

    /**
     * User choose to `DOUBLE`, double the amount of hes bet, give him card and mark him as 'finished turn'.
     */
    public function double() {
        /* Validate data */
        $user_id = Security::validateData('user_id');
        $board_id = Security::validateData('board_id');

        $user_id = intval($user_id);
        $board_id = intval($board_id);

        Security::validate_number($user_id);
        Security::validate_number($board_id);

        // Make sure the user can do the action `DOUBLE`
        $validated_data = $this->validateData(array('board_id' => $board_id));
        $current_board = $validated_data['current_board'];
        $current_players = $validated_data['current_players'];

        // Check the amount of the user cards in hand
        $cards_amount = count(explode('&', $current_players[0]['hand']));

        // Player must have two cards in hand to call double
        if( $cards_amount !== 2 ) throw new BlackJackException('Cannot double at this stage');

        // Initial player instance
        $user = new UpdaterUser($current_players[0]['user_id']);

        // Check if the player have enough money to double
        if( $user->getData()['wallet'][0]['balance'] - $current_players[0]['bet'] * 2 < 0 ) {
            throw new BlackJackException('Cannot double, no enough money!');
        }

        // Gets the players' hands
        $user_hand = explode('&', $current_players[0]['hand']);
        $virtual_hand = explode('&', $current_players[1]['hand']);
        $dealer_hand = explode('&', $current_players[2]['hand']);

        // Gives the user another card
        $random_card = getRandomCard(array_merge($user_hand, $virtual_hand, $dealer_hand));
        $user_hand[] = $random_card;
        $current_players[0]['hand'] .= '&'. $random_card;

        // Sum player hand to know if hes `status` have been changed
        $user_sum = sumPlayerHand($user_hand);

        if( count($user_sum) === PLAYER_SUM_EMPTY ) {
            $user_status = PLAYER_STATUS['lose'];
        } else {
            $user_status = PLAYER_STATUS['waiting'];
        }

        // Update the player data
        $player = new UpdaterPlayer(1, $current_players[0]['user_id']);
        $result = $player->save(
            array(
                $current_board->id,
                $current_players[0]['user_id'],
                $current_players[0]['hand'],
                $current_players[0]['bet'] * 2,
                $user_status
            )
        );

        // Return the data
        Output::outputData($result);
    }

    /**
     * User choose to `SURRENDER`.
     */
    public function surrender() {
        /* Validate data */
        $user_id = Security::validateData('user_id');
        $board_id = Security::validateData('board_id');

        $user_id = intval($user_id);
        $board_id = intval($board_id);

        Security::validate_number($user_id);
        Security::validate_number($board_id);

        // Make sure the user can do the action `STAND`
        $validated_data = $this->validateData(array('board_id' => $board_id));
        $current_board = $validated_data['current_board'];
        $current_players = $validated_data['current_players'];

        // Check the amount of the user cards in hand
        $cards_amount = count(explode('&', $current_players[0]['hand']));

        // Player must have two cards in hand to call double
        if( $cards_amount !== 2 ) throw new BlackJackException('Cannot surrender at this stage');

        // Update the player data
        $player = new UpdaterPlayer(1, $current_players[0]['user_id']);
        $result = $player->save(
            array(
                $current_board->id,
                $current_players[0]['user_id'],
                $current_players[0]['hand'],
                $current_players[0]['bet'] / 2,
                PLAYER_STATUS['lose']
            )
        );

        // Return the data
        Output::outputData($result);
    }

    /**
     * User choose to `SPLIT`.
     */
    public function split() {
        /* Validate data */
        $user_id = Security::validateData('user_id');
        $board_id = Security::validateData('board_id');

        $user_id = intval($user_id);
        $board_id = intval($board_id);

        Security::validate_number($user_id);
        Security::validate_number($board_id);

        // Make sure the user can do the action `SPLIT`
        $validated_data = $this->validateData(array('board_id' => $board_id));
        $current_board = $validated_data['current_board'];
        $current_players = $validated_data['current_players'];

        // Check the amount of the user cards in hand
        $hand_cards = explode('&', $current_players[0]['hand']);

        // Player must have two cards in hand to call double
        if( count($hand_cards) !== 2 ) throw new BlackJackException('Cannot split at this stage');

        // The two cards must be equal to split
        if(  intval(substr($hand_cards[0], 0, -2)) !==  intval(substr($hand_cards[1], 0, -2)) ) {
            throw new BlackJackException('Cannot split, cards must be equal');
        }

        // Initial player instance
        $user = new UpdaterUser($current_players[0]['user_id']);

        // Check if the player have enough money to double
        if( $user->getData()['wallet'][0]['balance'] - $current_players[0]['bet'] * 2 < 0 ) {
            throw new BlackJackException('Cannot split, no enough money!');
        }

        /* If we gets here we can split */
        // Gets the rest of the players' hands
        $virtual_hand = explode('&', $current_players[1]['hand']);
        $dealer_hand = explode('&', $current_players[2]['hand']);

        // Gets random cards for the first hand, and one more for the second hand
        $random_card = getRandomCard(array_merge($hand_cards, $virtual_hand, $dealer_hand));
        $hand_cards[] = $random_card;
        $random_card = getRandomCard(array_merge($hand_cards, $virtual_hand, $dealer_hand));
        $hand_cards[] = $random_card;

        /* Change the structure of the hand, status, bet for the player */
        // Player's hand to be CARD&CARD||CARD&CARD
        $new_hand = $hand_cards[0].'&'.$hand_cards[2].'||'.$hand_cards[1].'&'.$hand_cards[3];
        // Player's status to be STATUS||STATUS
        $new_status = $current_players[0]['status'].'||'.$current_players[0]['status'];
        // Player's bet to be BET||BET
        $new_bet = $current_players[0]['bet'].'||'.$current_players[0]['bet'];

        // Update the player data
        $player = new UpdaterPlayer(1, $current_players[0]['user_id']);
        $result = $player->save(
            array(
                $current_board->id,
                $current_players[0]['user_id'],
                $new_hand,
                $new_bet,
                $new_status
            )
        );

        // Return the data
        Output::outputData($result);
    }

    /**
     * User finished hes turn, play the virtual player, dealer and also finish the game.
     */
    public function playVirtualTurns(){
        /* Validate data */
        $user_id = Security::validateData('user_id');
        $board_id = Security::validateData('board_id');

        $user_id = intval($user_id);
        $board_id = intval($board_id);

        Security::validate_number($user_id);
        Security::validate_number($board_id);

        // Create an instance of the current board object
        $current_board = new UpdaterBoard($board_id);
        // Gets board data
        $board_data = $current_board->getData();

        // Does the game still on going?
        if( !$board_data['success'] || !$board_data[0]['status'] ) {
            throw new BlackJackException('Cannot play virtual players, game finished!');
        }

        // Gets the current board's players
        $current_players = $current_board->getBoardPlayers();

        // Get the user statuses
        $user_statuses = explode('||', $current_players[0]['status']);
        $counter = 0;

        // Loop through the status
        foreach( $user_statuses as $status ) {
            // Can the player hit?
            if ($status !== PLAYER_STATUS['playing']) {
                $counter++;
            }
        }

        // Make sure the all the statuses are `done`
        if( count($user_statuses) !== $counter ) {
            throw new BlackJackException('Finish the user turns first then play the virtual players');
        }

        // Gets the player cards
        $user_cards = [];

        // Gets the cards in the player's hand
        foreach( explode('||', $current_players[0]['hand']) as $hand ) {
            $user_cards = array_merge($user_cards,(explode('&', $hand)));
        }

        // Virtual player information
        $virtual_cards[0] = explode('&', $current_players[1]['hand']);
        $virtual_statuses = array($current_players[1]['status']);
        $virtual_bets = array($current_players[1]['bet']);

        // Also dealer cards to get random card
        $dealer_cards = explode('&', $current_players[2]['hand']);

        // Count the turns (Split, Double will be available only in turn = 0)
        $turn = 0;
        // Index which hand of the virtual player are we playing
        $hand = 0;

        // Loop till the dealer finishes to play
        do{
            /* Break point */
            // Do we have finished checking the first hand?
            if( $virtual_statuses[0] !== PLAYER_STATUS['playing'] ) {
                // Do we have one more hand?
                if( isset($virtual_statuses[1]) ) {
                    // We have one more hand to check
                    // Does this hand finished the game as well?
                    if( $virtual_statuses[1] !== PLAYER_STATUS['playing'] ) {
                        // Yes, we finished playing both hands
                        break;
                    } else {
                        // Otherwise, we have to check the next hand
                        $hand = 1;
                    }
                } else {
                    // We have no more hands to check
                    break;
                }
            }

            // Choose an action for the virtual player
            $virtual_move = choose_action(array('virtual_hand' => $virtual_cards[$hand], 'dealer_hand' => $dealer_cards));

            // Check each move as case
            switch ($virtual_move) {
                case 0: // 'hit'
                    // Gets random cards for the first hand, and one more for the second hand
                    $random_card = getRandomCard(array_merge($user_cards, $virtual_cards[$hand], $dealer_cards));
                    $virtual_cards[$hand][] = $random_card;

                    // Sum player hand to know if hes `status` have been changed
                    $sum = sumPlayerHand($virtual_cards[$hand]);

                    if( count($sum) === PLAYER_SUM_EMPTY ) {
                        $virtual_statuses[$hand] = PLAYER_STATUS['lose'];
                    } else {
                        $virtual_statuses[$hand] = PLAYER_STATUS['playing'];
                    }
                    break;
                case 1: // 'stand'
                    $virtual_statuses[$hand] = PLAYER_STATUS['waiting'];
                    break;
                case 2: // 'split'
                    if( $turn !== 0 ) break; // Split possible only as a first move

                    // Gets random cards for the first hand, and one more for the second hand
                    $random_card = getRandomCard(array_merge($user_cards, $virtual_cards[0], $dealer_cards));
                    $virtual_cards[1][] = $random_card;
                    $random_card = getRandomCard(array_merge($user_cards, $virtual_cards[0], $virtual_cards[1], $dealer_cards));
                    $virtual_cards[1][] = $random_card;

                    $virtual_cards = array(array($virtual_cards[0][0],$virtual_cards[1][0]),array($virtual_cards[0][1],$virtual_cards[1][1]));
                    $virtual_bets = array($current_players[1]['bet'], $current_players[1]['bet']);
                    $virtual_statuses = array($current_players[1]['status'], $current_players[1]['status']);
                    break;
                case 3: // 'double'
                    if( $turn !== 0 ) break; // Double possible only as a first move

                    // Gets random card
                    $random_card = getRandomCard(array_merge($user_cards, $virtual_cards[0], $dealer_cards));
                    $virtual_cards[0][] = $random_card;
                    $virtual_bets = array($current_players[1]['bet'] * 2);

                    // Sum player hand to know if hes `status` have been changed
                    $sum = sumPlayerHand($virtual_cards[0]);

                    if( count($sum) === PLAYER_SUM_EMPTY ) {
                        $virtual_statuses[0] = PLAYER_STATUS['lose'];
                    } else {
                        $virtual_statuses[0] = PLAYER_STATUS['waiting'];
                    }
                    break;
            }
            // Next turn
            $turn++;

        }while( $turn < 50 ); // Worst case, NEVER HAPPENED

        /* Restructure the hand and bet of the virtual player */
        $virtual_final_hand = '';
        $virtual_final_bet = '';

        // We will need this array to get random cards for the dealer
        $virtual_cards_array = [];

        // Loop through the hands we have for the virtual player
        for( $i = 0; $i < count($virtual_cards); $i++) {
            // Loop through the cards for the virtual player
            foreach( $virtual_cards[$i] as $card ) {
                $virtual_final_hand .= $card.'&';
                $virtual_cards_array[] = $card;
            }
            $virtual_final_hand = substr($virtual_final_hand, 0, -1);
            $virtual_final_hand .= '||';
            $virtual_final_bet .= $virtual_bets[$i].'||';
        }

        // Remove the last '||'
        $virtual_final_hand = substr($virtual_final_hand, 0, -2);
        $virtual_final_bet = substr($virtual_final_bet, 0, -2);

        /* Play the dealer turn */
        // The dealer hand
        $dealer_hand = $dealer_cards[0];

        // Loop until dealer hit 16+ or lose
        do{
            // Gets random card
            $random_card = getRandomCard(array_merge($user_cards, $virtual_cards_array, $dealer_cards));
            $dealer_cards[] = $random_card;
            $dealer_hand .= '&'. $random_card;
            // Sum dealer hand
            $dealer_sum = count(sumPlayerHand($dealer_cards)) === PLAYER_SUM_EMPTY? 100 : max(sumPlayerHand($dealer_cards));
        }while( $dealer_sum < 17 );

        $user_cards_per_hand = [];
        // Gets the cards in each user's hands
        foreach( explode('||', $current_players[0]['hand']) as $hand ) {
            $user_cards_per_hand[] = (explode('&', $hand));
        }

        /* IN THIS PART WE WILL CHECK WHO OWN, WHO LOSE AND WE WILL UPDATE THE DB */
        $virtual_final_status = '';
        $user_final_status = '';

        // Loop through the maximum hands a player can have
        for( $i = 0; $i < PLAYER_MAX_HANDS; $i++) {
            // Check for user
            // Is he on any status that is not 'lose'?
            if( isset($user_statuses[$i]) && $user_statuses[$i] !== PLAYER_STATUS['lose'] ) {
                // He is, check hes hand
                $sum_usr = max(sumPlayerHand($user_cards_per_hand[$i]));
                // Is the dealer hand hit more then 21 or less then the user hand?
                if( $dealer_sum === 100 || $sum_usr >= $dealer_sum ) {
                    // Yes, the user own this hand
                    $user_statuses[$i] = PLAYER_STATUS['win'];
                } else {
                    // No, the user lose this hand
                    $user_statuses[$i] = PLAYER_STATUS['lose'];
                }
            }
            // Check for virtual player
            // Is he on any status that is not 'lose'?
            if( isset($virtual_statuses[$i]) && $virtual_statuses[$i] !== PLAYER_STATUS['lose'] ) {
                // He is, check hes hand
                $sum_virtual = max(sumPlayerHand($virtual_cards[$i]));
                // Is the dealer hand hit more then 21 or less then the user hand?
                if( $dealer_sum === 100 || $sum_virtual >= $dealer_sum ) {
                    // Yes, the player own this hand
                    $virtual_statuses[$i] = PLAYER_STATUS['win'];
                } else {
                    // No, the user lose this hand
                    $virtual_statuses[$i] = PLAYER_STATUS['lose'];
                }
            }
            // Make a final statuses structure for the players
            $virtual_final_status = isset($virtual_statuses[$i])? $virtual_final_status.$virtual_statuses[$i].'||': $virtual_final_status;
            $user_final_status = isset($user_statuses[$i])? $user_final_status.$user_statuses[$i].'||': $user_final_status;
        }

        // Remove the last '||'
        $virtual_final_status = substr($virtual_final_status, 0, -2);
        $user_final_status = substr($user_final_status, 0, -2);

        // Update the virtual player
        $virtual_player = new UpdaterPlayer(2);
        $virtual_player->save(array($board_id, PLAYER_TYPE[2], $virtual_final_hand, $virtual_final_bet, $virtual_final_status));

        // Update the user
        $user_player = new UpdaterPlayer(1, $user_id);
        $user_player->save(array($board_id, $user_id, $current_players[0]['hand'], $current_players[0]['bet'], $user_final_status));

        // Update the dealer
        $dealer_player = new UpdaterPlayer(3);
        $dealer_player->save(array($board_id, PLAYER_TYPE[3], $dealer_hand, $current_players[2]['bet'], $current_players[2]['status']));

        /* Update the user wallet */
        // User reward variable
        $user_reward = 0;
        $games_user_own = 0;
        $games_user_lost = 0;
        $bet = explode('||', $current_players[0]['bet']);

        // Does the user own or lost games?
        if( isset(array_count_values($user_statuses)[1]) ) {
            // He own
            $games_user_own = array_count_values($user_statuses)[1];
            $user_reward += $games_user_own * ($bet[0] * PLAYER_WIN_RATIO);
        } if( isset(array_count_values($user_statuses)[-1]) ) {
            // He lose
            $games_user_lost = array_count_values($user_statuses)[-1];
            $user_reward -= $games_user_lost * ($bet[0]);
        }

        // Gets user wallet
        $usr = new UpdaterUser($user_id);
        $balance = $usr->getData()['wallet'][0]['balance'];
        // Update hes balance
        $balance += $user_reward;

        // Update the wallet
        $wlt = new UpdaterWallet();
        $wlt->save(array('user_id' => $user_id, 'balance' => $balance));

        // Finish the game
        $current_board->finishGame(array('board_id' => $board_id, 'user_id' => $user_id));

        // Return the data
        Output::outputData(array(
            'player' => array(
                array(
                    'user_id' => PLAYER_TYPE[2],
                    'hand' => $virtual_final_hand,
                    'bet' => $virtual_final_bet,
                    'status' => $virtual_final_status,
                ),
                array(
                    'user_id' => PLAYER_TYPE[3],
                    'hand' => $dealer_hand,
                    'bet' => $current_players[2]['bet'],
                    'status' => $current_players[2]['status'],
                ),
            ),
            'user' => array(
                'balance' => $balance,
                'reward' => $user_reward,
                'own' => $games_user_own,
                'lost' => $games_user_lost,
            )
        ));
    }
}
