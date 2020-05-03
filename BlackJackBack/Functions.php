<?php
/**
 * This function will return a random card from the card that haven't been used.
 * @param $cards array      The cards that have been already used.
 * @return string            Return the random card.
 */
function getRandomCard($cards = array()){
    /* Initial deck of cards */
    $card_decks = array();

    // Initial six card decks
    for( $i = 0 ; $i < CARD_MAX_PACKS ; $i++ ) {
        // Loop through the shapes
        foreach (CARD_SHAPE as $shape) {
            // Loop through the numbers
            foreach (CARD_NUMBER as $number) {
                // Set the new card
                $card_decks[$number . $shape . $i] = false;
            }
        }
    }

    // Do we have any used cards?
    if( count($cards) > 0 ) {
        // We do,
        // Loop through the cards we have already used
        foreach ($cards as $card) {
            $card_decks[$card] = true;
        }
    }

    // Keep looking for a card that is not used
    do {
        // Generate random deck, card number, and card shape
        $random_deck = rand(0,5);
        $random_card_number = rand(1, 13);
        $random_card_shape = rand(0,3);
    } while($card_decks[$random_card_number . CARD_SHAPE[$random_card_shape] . $random_deck] === true);

    // Return the array of the random card
    return $random_card_number.CARD_SHAPE[$random_card_shape].$random_deck;
}

/**
 * Returns summation of the hand, in case of ACE it will return sum possible summations.
 * @param $cards    Array of cards in format: number . shape . deck
 *                                            Example: 10H3
 * @return array    Possible summations of the current hand.
 */
function sumPlayerHand($cards) {
    // The summation variable
    $sums = array(0);

    // Loop through the cards
    foreach( $cards as $card ) {
        // Gets the integer value of the card
        $num = intval(substr($card, 0, -2));

        // Numbers 11/12/13 equal to 10
        if( $num === CARD_UNIQUE_NUMBER['J'] || $num === CARD_UNIQUE_NUMBER['Q'] || $num === CARD_UNIQUE_NUMBER['K'] ) {
            $num = CARD_UNIQUE_NUMBER['TEN'];
        }

        // In case of ACE card we wants to create summation of ACE as 1, and also 11
        if( $num === CARD_UNIQUE_NUMBER['ACE'] ){
            /* We will double the amount of cells in the array */
            // Copy the sums array into two temporary arrays
            for($i = 0; $i < count($sums) ; $i++){
                $cpy_arr_1[$i] = $sums[$i];
                $cpy_arr_2[$i] = $sums[$i];
            }
            // Gets the merged array
            $sums = array_merge($cpy_arr_1, $cpy_arr_2);

            // Loop through the values in sums
            foreach( $sums as $index => &$sum ) {
                /* Update each sum */
                if( $index <= (count($sums)/2) - 1 ) $sum += CARD_UNIQUE_NUMBER['ACE'];
                else $sum += CARD_UNIQUE_NUMBER['ELEVEN'];

                // Check if the current value is valid, unset if it isn't
                if( $sum > CARD_BLACKJACK ) unset($sums[$index]);
            }
        } else {
            // Loop through the sums
            foreach( $sums as $index => &$sum ){
                // Add the number
                $sum += $num;

                // Check if the current value is valid, unset if it isn't
                if( $sum > CARD_BLACKJACK ) unset($sums[$index]);
            }
        }
    }

    // Return the possible summations without duplications
    return array_unique($sums);
}

/**
 * Choose a move for the virtual player.
 * The algorithm will be based on current board status, known strategies and statistic calculations.
 * The known strategies works for the first time we are choosing action, so we need to separate the situations.
 * @param $data array   All the board data, cards for each player.
 * @return int          For each action there is a value in Constant.php file.
 */
function choose_action($data) {
    // The players hands
    $virtual_hand = $data['virtual_hand'];
    $dealer_card = intval(substr($data['dealer_hand'][0], 0, -2));

    // Initial actions variables
    $hit = 1;
    $stand = 1;
    $split = 1;
    $double = 1;
    // The virtual player's hand sum
    $sum = sumPlayerHand($virtual_hand);

    // First time we are checking?
    if( count($virtual_hand) === 2 ) {
        // It is the first time
        // Initial some variables
        $equal =
            substr($virtual_hand[0], 0, -2) === substr($virtual_hand[1], 0, -2) ?
                true : false;
        $has_ace =
            substr($virtual_hand[0], 0, -2) === '1' || substr($virtual_hand[1], 0, -2) === '1' ?
                true : false;

        /* FIRST OF ALL, GOTTA CHECK THE `KNOWN STRATEGY` SITUATIONS */
        // Does the two virtual player's cards equal?
        if( $equal ) {
            // Cards are equal -
            // If hes cards are 8 or ACE - split
            if( substr($virtual_hand[0], 0, -2) === '8' || $has_ace ) {
                return VIRTUAL_ACTION['split'];
            }
            // If the cards are both 10 - stand
            if( substr($virtual_hand[0], 0, -2) === '10' ){
                return VIRTUAL_ACTION['stand'];
            }

            // Gets dealer card value - needed for statistic
            $temp = choose_action_ReturnCardValue($dealer_card);

            // If we gets here we should check statistically which move do we wants to do
            // We must do a move from this situation, we have two equal cards!
            $stand *= 3/70 * (($temp * rand(2,100)) / 1100);
            $split *= 32/70 * (($temp * rand(2,100)) / 1100);
            $double *= 8/70 * (($temp * rand(2,100)) / 1100);
            $hit *= 27/70 * (($temp * rand(2,100)) / 1100);

            // Find the max variable and return the action
            $arr = array(strval($stand) => 'stand', strval($split) => 'split', strval($double) => 'double', strval($hit) => 'hit');
            return VIRTUAL_ACTION[$arr[strval(max($stand, $split, $double, $hit))]];
        }

        // If we have no ACE and the hand's sum is between 5 to 8 - hit
        if( !$has_ace && ($sum >= 5 || $sum <= 8) ) {
            return VIRTUAL_ACTION['hit'];
        }

        // If virtual player has ace, we gotta check some situations
        if( $has_ace ) {
            // If second card value is greater then 7 - stand
            if (intval(substr($virtual_hand[0], 0, -2)) > 7 ||
                intval(substr($virtual_hand[1], 0, -2)) > 7 ) {
                return VIRTUAL_ACTION['stand'];
            }
            // Check the dealer card - if dealer has card value greater then 8 - hit
            if( $dealer_card  > 8 ) {
                return VIRTUAL_ACTION['hit'];
            } else {
                // Gets dealer card value - needed for statistic
                $temp = choose_action_ReturnCardValue($dealer_card);

                // If we gets here we should check statistically which move do we wants to do
                $stand *= 4/42 * (($temp * rand(2,100)) / 1100);
                $double *= 18/42 * (($temp * rand(2,100)) / 1100);
                $hit *= 20/42 * (($temp * rand(2,100)) / 1100);

                // Find the max variable and return the action
                $arr = array(strval($stand) => 'stand', strval($double) => 'double', strval($hit) => 'hit');
                return VIRTUAL_ACTION[$arr[strval(max($stand, $double, $hit))]];
            }
        }
    }

    /* If we gets here, we passed the unique situations */

    // First part is if user hand sum is greater then 16 - stand
    if( $sum > 16 ) {
        return VIRTUAL_ACTION['stand'];
    }

    // Gets dealer card value - needed for statistic
    $temp = choose_action_ReturnCardValue($dealer_card);

    // We needs to split it to three parts:
    // Second part is if dealer card is 7/8/9/10/J/Q/K/ACE
    if( $dealer_card > 6 || $dealer_card === 1 ) {
        // If we gets here we should check statistically which move do we wants to do
        $double *= 7/40 * (($temp * rand(2,100)) / 1100);
        $hit *= 33/40 * (($temp * rand(2,100)) / 1100);

        // Find the max variable and return the action
        $arr = array(strval($double) => 'double', strval($hit) => 'hit');
        return VIRTUAL_ACTION[$arr[strval(max($double, $hit))]];
    }

    // Otherwise,
    // Third part, and last one, if dealer card is 2/3/4/5/6
    $stand *= 3/20 * (($temp * rand(2,100)) / 1100);
    $double *= 14/20 * (($temp * rand(2,100)) / 1100);
    $hit *= 3/20 * (($temp * rand(2,100)) / 1100);

    // Find the max variable and return the action
    $arr = array(strval($stand) => 'stand', strval($double) => 'double', strval($hit) => 'hit');
    return VIRTUAL_ACTION[$arr[strval(max($stand, $double, $hit))]];
}

/**
 * HELP FUNCTION, GETS CARD VALUE AS I WANTED IT TO BE!
 * Check the dealer hand, we wants to take ace as 11 and k,q,j as 10 for the calculation,
 * otherwise return card number.
 * @param $dealer_card  The dealer card as integer.
 * @return int          The card value as I want.
 */
function choose_action_ReturnCardValue($dealer_card){
    // Check the dealer hand, we wants to take ace as 11 and k,q,j as 10 for the calculation
    if( $dealer_card === CARD_UNIQUE_NUMBER['ACE'] ) {
        return 11;
    }
    else if( $dealer_card === CARD_UNIQUE_NUMBER['K'] ||
        $dealer_card === CARD_UNIQUE_NUMBER['Q'] ||
        $dealer_card === CARD_UNIQUE_NUMBER['J'] ) {
        return 10;
    }
    return $dealer_card;
}

/**
 * This function will be help function to controller.
 * The function will get hand, status or bet as array and return them as string.
 * Example: array(6D3&8H4, 3D3&5H4) will return 6D3&8H4||3D3&5H4.
 * @param $data array   The data.
 * @return string   The data formatted.
 *
 */
function controller_arrayToString($data) {
    // Temp variable
    $temp = '';

    // Loop through the data array
    for( $i = 0; $i < count($data); $i++ ) {
        // Save the data for each slot
        $temp .= $data[$i].'||';
    }
    // Return the last '||'
    $temp = substr($temp, 0, -2);
    // Return it
    return $temp;
}

/**
 * Generate random string
 * @return string
 */
function generateRandomString() {
    // Characters available
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    // Characters length
    $charactersLength = strlen($characters);
    // The random string
    $randomString = '';

    // Loop throw the length
    for ($i = 0; $i < OUTPUT_RANDOM_STRING_LENGTH; $i++) {
        // Generate random char
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    // Return the random string
    return $randomString;
}
