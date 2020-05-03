<?php
/* INPUT */
define('INPUT_MAX_LENGTH', 100);
define('INPUT_MIN_PASSWORD_LENGTH', 8);
define('INPUT_MAX_PASSWORD_LENGTH', 50);

/* OUTPUT */
define('OUTPUT_RANDOM_STRING_LENGTH', 50);

/* REQUIREMENT */
define('REQUIREMENT_MAX_AGE', 150);
define('REQUIREMENT_MIN_AGE', 18);

/* CARD */
define('CARD_NUMBER', array(1,2,3,4,5,6,7,8,9,10,11,12,13));
define('CARD_UNIQUE_NUMBER', [
    'ACE' => 1,
    'J' => 11,
    'Q' => 12,
    'K' => 13,
    'TEN' => 10,
    'ELEVEN' => 11
    ]);
define('CARD_SHAPE', [0 => 'C', 1 => 'D', 2 => 'S', 3 => 'H']); // Club, Diamond, Spade, Heart
define('CARD_MAX_PACKS', 6);
define('CARD_BLACKJACK', 21);

/* BET */
define('BET_MAX', 50);
define('BET_MIN', 1);

/* PLAYER */
define('PLAYER_TYPE', [1 => 0, 2 => -1 , 3 => -2]); // type 2 -> virtual player, hes ID is -1, type 3 -> dealer, hes ID is -2
define('PLAYER_STATUS', [
    'waiting' => '-2',
    'playing' => '0',
    'win' => '1',
    'lose' => '-1'
]);
define('PLAYER_SUM_EMPTY', 0);
define('PLAYER_MAX_HANDS', 2);
define('PLAYER_WIN_RATIO', 1.5);
define('PLAYER_INIT_BALANCE', 10);

/* VIRTUAL */
define('VIRTUAL_ACTION', [ 'hit' => 0, 'stand' => 1, 'split' => 2, 'double' => 3]);