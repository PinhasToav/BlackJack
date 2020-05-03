<?php
/**
 * Class Security
 * Will be responsible for the input validation in security aspect.
 */
class Security {
    /**
     * Validate the data we just get by POST method because we support only POST method requests.
     * The POST parameter itself can be string or array.
     * @param $name string              The parameter name,
     *                                  $_POST[$name].
     * @param $post boolean             By default true, gives an indication about where do we gets the data from:
     *                                  true means by post method, false other.
     * @param $val  string              Optional, the parameter value.
     *                                  If we gets the parameter by POST it will stay empty.
     * @return array                    The secured data.
     * @throws BlackJackException       Throws error if needs
     */
    public static function validateData($name, $val = '', $post = true){
        // Saves the parameter in a temporary variable if its exist
        if ($post && !isset($_POST[$name])) {
            throw new BlackJackException('The parameter name ' . $name . ' required does not exist');
        }

        // Set a temp value to hold the data
        $data = $post ? $_POST[$name] : $val;

        /* WE SUPPORT ONLY STRING AND ARRAY PARAMETERS */
        // Is it string?
        if (is_string($data)) {
            // It is, remove invalid characters
            $data = htmlspecialchars(stripslashes(strip_tags(trim(urldecode($data)))));
            // Check the input's length
            if (strlen($data) > INPUT_MAX_LENGTH) throw new BlackJackException('The parameter length is too big');
            // Is it array?
        } else if (is_array($data)) {
            // It is array, loop through its items
            foreach ($data as $key => &$value) {
                // Remove invalid characters
                $value = htmlspecialchars(stripslashes(strip_tags(trim(urldecode($value)))));
                // Check the input's length
                if (strlen($value) > INPUT_MAX_LENGTH) throw new BlackJackException('The parameter length is too big');
            }
            // Otherwise, the type of the variable is not array or string, we don't support it
        } else {
            throw new BlackJackException('Support only String or Array parameters');
        }
        // Return the secured data
        return $data;
    }

    /**
     * The method will make sure that email we get is valid.
     * @param $word string  The string we are currently checking.
     * @throws BlackJackException   Email invalid format.
     */
    public static function validate_email($word){
        if( !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", $word) ){
            throw new BlackJackException('Email is invalid');
        };
    }

    /**
     * The method will make sure that the word includes only letters and white space.
     * @param $word string  The string we are currently checking.
     * @return boolean      true if the word is valid, otherwise false.
     */
    public static function validate_string($word){
        return preg_match("/^[a-zA-Z ]*$/", $word);
    }

    /**
     * The method will make sure the password includes the parameters required such as,
     * invalid length, one number etc..
     * @param $word string  The string we are currently checking.
     * @throws BlackJackException   Password invalid format.
     */
    public static function validate_password($word){
        // Password length
        if( strlen($word) < INPUT_MIN_PASSWORD_LENGTH || strlen($word) > INPUT_MAX_PASSWORD_LENGTH ) throw new BlackJackException('Password length invalid');
        // Password must contain 1 number
        if( !preg_match("#[0-9]+#",$word) ) throw new BlackJackException('Password must contain at least one 1 number');
        // Password must contain at least one capital letter
        if( !preg_match("#[A-Z]+#",$word) ) throw new BlackJackException('Password must contain at least one capital letter');
        // Password must contain at least one lower letter
        if( !preg_match("#[a-z]+#",$word) ) throw new BlackJackException('Password must contain at least one lower letter');
    }

    /**
     * Make sure that $num data is number
     * @param $num
     * @throws BlackJackException   If not number.
     */
    public static function validate_number($num) {
        if( !preg_match("/^\d+$/", $num) ) throw new BlackJackException('The data is not number');
    }

    /**
     * Check if is authenticated via cookie, using our class.
     * @return bool         `true` if the user is logged in, otherwise `false`.
     * @throws BlackJackException   Throws exception in case of db error.
     */
    public static function isAuthenticated() {
        // Validate data
        $cookie = self::validateData('cookie');
        self::validate_string($cookie);

        $cookie_check = new UpdaterCookie();
        $result = $cookie_check->checkCookie(array('cookie' => $cookie));

        // Check if the user authenticated
        if( $result['success'] ) return true;
        // Otherwise, he is'nt
        else return false;
    }
}