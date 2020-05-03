<?php
/**
 * Class ControllerWallet
 * Take responsibility of the user's wallet.
 * This class will include checking if user can bet and more stuff.
 */
class ControllerWallet extends Controller {
    /**
     * ControllerUser constructor.
     */
    function __construct(){
        parent::__construct(array(
            'canuserbet' => true,
        ));
    }

    /**
     * @inheritDoc
     */
    protected function validateData($data) {
        // TODO: Implement validateData() method.
    }

    /**
     * Gets the user details from the front end,
     * and return whether or not the user can bet this amount of money.
     */
    public function canUserBet(){
        /* Secure validate the data */
        $user_id = Security::validateData('user_id');
        $bet = Security::validateData('bet');

        $user_id = intval($user_id);
        $bet = intval($bet);

        // Make sure that this are validate numbers
        Security::validate_number($user_id);
        Security::validate_number($bet);

        // Create user instance
        $user = new UpdaterUser($user_id);

        // Gets the logged in user's data
        $result = $user->getData();

        // Check if we have logged in
        if( $result['wallet']['balance'] >= $bet ) {
            // Login successfully done
            Output::outputData();
        } else {
            // Otherwise we have an error
            throw new BlackJackException('Not enough money for this bet');
        }
    }
}