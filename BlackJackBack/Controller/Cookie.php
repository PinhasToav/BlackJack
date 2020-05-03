<?php
/**
 * Class ControllerCookie
 * Responsible for cookies.
 */
class ControllerCookie extends Controller {
    /**
     * ControllerUser constructor.
     */
    function __construct(){
        parent::__construct(array(
            'getdatabycookie' => false,
        ));
    }

    /**
     * @inheritDoc
     */
    protected function validateData($data) {
        // TODO: Implement validateData() method.
    }

    /**
     * Get the data of the user by the cookie.
     */
    public function getDataByCookie() {
        $cookie_id = Security::validateData('cookie');
        Security::validate_string($cookie_id);

        // Create updater user instance
        $cookie = new UpdaterCookie();

        // Call register
        $result = $cookie->getDataByCookie(array('cookie' => $cookie_id));
        
        // Check if the user logged in
        if( $result ){
            // He is
            Output::outputData($result);
        } else {
            // Otherwise we have an error
            throw new BlackJackException('Unauthenticated user');
        }
    }
}