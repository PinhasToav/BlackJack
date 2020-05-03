<?php
/**
 * Class ControllerUser
 * ControllerUser Class will be responsible to sign in, sign up new users etc..
 */
class ControllerUser extends Controller {
    /**
     * ControllerUser constructor.
     */
    function __construct(){
        parent::__construct(array(
            'login' => false,
            'register' => false,
            'update' => true,
            'isloggedin' => false,
            'myaccount' => true,
        ));
    }

    /**
     * @inheritDoc
     */
    protected function validateData($data) {
        // Loop through the data we validate
        foreach( $data as $key => $value) {
            // Case of `age` checking
            if( $key === 'age' ) {
                // Age must be valid
                if( $value > REQUIREMENT_MAX_AGE || $value < REQUIREMENT_MIN_AGE ) {
                    throw new BlackJackException('The user does not meet the age requirement');
                }
            // Case of `password_1` checking
            } else if( $key === 'password_1' ) {
                // Passwords must be equal
                if( $data['password_1'] !== $data['password_2'] ) {
                    throw new BlackJackException('Passwords must be equal');
                }
            }
        }
    }

    /**
     * The method will log in a user.
     * It will get parameters from by POST method and validate them.
     */
    public function login() {
        /* Secure validate the data */
        $email = Security::validateData('email');
        $password = Security::validateData('password');

        // Validate email and password data
        Security::validate_email($email);
        Security::validate_password($password);

        // Create user instance
        $user = new UpdaterUser();
        // Call login
        $result = $user->login(array('email' => $email, 'password' => $password));
        
        // Check if we have logged in
        if( $result['success'] ) {
            // Login successfully done
            Output::outputData(array(
                'full_name' => $result[0]['full_name'],
                'id' => $result[0]['id'],
                'random_string' => $result['random_string']
            ));
        } else {
            // Otherwise we have an error
            throw new BlackJackException('Incorrect email or password');
        }
    }

    /**
     * The method will sign up a new user.
     * It will get parameters from by POST method and validate them.
     */
    public function register(){
        /* Validate data */
        $email = Security::validateData('email');
        $full_name = Security::validateData('full_name');
        $password_1 = Security::validateData('password_1');
        $password_2 = Security::validateData('password_2');
        $age = Security::validateData('age');

        Security::validate_email($email);
        Security::validate_password($password_1);
        Security::validate_password($password_2);

        // Check age value
        $age = intval($age);

        Security::validate_number($age);

        // Make sure the data meet the requirements
        $this->validateData(array('age' => $age, 'password_1' => $password_1, 'password_2' => $password_2));

        // Create updater user instance
        $user = new UpdaterUser();
        // Call register
        $result = $user->register(
            array($email, $full_name, $password_1, $age)
        );
        // Check if the user successfully created
        if( $result ){
            // Login successfully done
            Output::outputData();
        } else {
            // Otherwise we have an error
            throw new BlackJackException('Could not create the new user');
        }
    }

    /**
     * Gets the user information.
     * The information that we will return is:
     * balance, wins, loses, virtual players statistics.
     */
    public function myAccount() {
        /* Secure validate the data */
        $user_id = Security::validateData('user_id');
        $user_id = intval($user_id);

        // Validate the ID
        Security::validate_number($user_id);

        // Create updater user instance
        $user = new UpdaterUser();
        // Call register
        $result = $user->myAccount(array($user_id));

        // Get the user balance
        $user_balance = $result['wallet'][0]['balance'];

        // Save the user total statuses
        $user_total_statuses = 0;
        $user_win = 0;
        $user_lose = 0;

        // Loop through the user statuses
        foreach ($result['user'] as $each_status_array) {
            // Gets the statuses
            $statuses = explode('||', $each_status_array['status']);

            // Loop through the statuses
            foreach($statuses as $status) {
                // Increase the total statuses
                $user_total_statuses++;

                // Check the status value
                if($status === '-1') $user_lose++;
                else if($status === '1') $user_win++;
                else $user_total_statuses--;
            }
        }

        // Save the user total statuses
        $virtual_total_statuses = 0;
        $virtual_win = 0;
        $virtual_lose = 0;

        // Loop through the user statuses
        foreach ($result['virtual'] as $each_status_array) {
            // Gets the statuses
            $statuses = explode('||', $each_status_array['status']);

            // Loop through the statuses
            foreach($statuses as $status) {
                // Increase the total statuses
                $virtual_total_statuses++;

                // Check the status value
                if($status === '-1') $virtual_lose++;
                else if($status === '1') $virtual_win++;
                else $virtual_total_statuses--;
            }
        }

        // Return the data
        return Output::outputData(array(
            'balance' => $user_balance,
            'user_games' => $user_total_statuses,
            'user_win' => $user_win,
            'user_lose' => $user_lose,
            'virtual_games' => $virtual_total_statuses,
            'virtual_win' => $virtual_win,
            'virtual_lose' => $virtual_lose,
        ));
    }
}