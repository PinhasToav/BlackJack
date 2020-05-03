<?php
/**
 * Class Database
 * Responsible of calling the MYSQL using PDO,
 * It will include `DELETE`, `UPDATE` etc.
 */
class Database{
    /**
     * The name of the table we are manipulating.
     * @var string
     */
    private $table;

    /**
     * The PDO service.
     * @var object
     */
    private $pdo;

    /**
     * The sensitive data we have on the system.
     * We will remove the sensitive data before sending it back to the updater.
     * @var array
     */
    private $sensitive_data;

    /**
     * Database constructor.
     * @param $table_name string    The name of the table we manipulating.
     * @throws BlackJackException   Throws exception if creating PDO object fail.
     */
    public function __construct($table_name) {
        // Initial sensitive data
        $this->sensitive_data = array(
          'password',
        );

        // Set the table name
        $this->table = $table_name;

        // Set PDO Connection
        $host = 'localhost';
        $db   = 'blackjack';
        $user = 'blackjack';
        $pass = '1234';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        // Try to create new PDO
        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            // We have an error, throw an exception
            throw new BlackJackException($e->getMessage().', '. (int)$e->getCode());
        }
    }

    /**
     * $DATA AND $DB_ROWS MUST BE IN THE SAME ORDER.
     * which means, if data[0] is password, then $db_row[0] is password.
     * Implement `SELECT` operation.
     * @param $data array           The data.
     * @param $db_rows array        The database rows we would like to select from.
     * @param $select_data array    Optional. the data we wants to select to see, by default all the data.
     * @return boolean              Do we have the selected data in the DB? true if we do, otherwise false.
     */
    public function select($data, $db_rows, $select_data = array('*')){
        // Prepare statement initial
        $prepare_statement = 'SELECT ';

        // Loop through the data we would like to `SELECT`
        foreach( $select_data as $each_select ) {
            // Add each select value we wants
            $prepare_statement .= $each_select.',';
        }
        // Remove the last `,`
        $prepare_statement = substr($prepare_statement, 0, -1);

        $prepare_statement .= ' FROM '.$this->table.' WHERE ';

        // Loop through the rows
        foreach( $db_rows as $row ){
            // Add each row we wants to select
            $prepare_statement.= $row.'=:'.$row.' AND ';
            // Also, prepare the `execute` array
            $exec[':'.$row] = array_shift($data);
        }
        // Remove the last ` AND`
        $prepare_statement = substr($prepare_statement, 0, -5);

        // Prepare the statement
        $stmt = $this->pdo->prepare($prepare_statement);
        // Execute the statement
        $stmt->execute($exec);

        // If the fetch successfully done, save the data in array
        $data_array = $stmt->fetchAll();

        // Loop through the data
        foreach ( $data_array as &$data ) {
            // Remove sensitive data
            $this->removeSensitiveData($data);
        }

        // Do we have data returned?
        if( count($data_array) !== 0 ){
            // We do, successfully selected
            $data_array['success'] = true;
            return $data_array;
        } else {
            // Otherwise, we get no data
            return array('success' => false);
        }
    }

    /**
     * Implement `INSERT` operation.
     * @param $data array   The data dictionary.
     * @param $db_rows      The database rows we would like to insert into.
     * @return bool         true if we successfully inserted, otherwise false.
     * @throws BlackJackException   In case of duplicate key `user_name` (email).
     */
    public function insert($data, $db_rows){
        try {
            // Base prepare statement
            $prepare_statement = 'INSERT INTO ' . $this->table . ' (';
            // Set values for prepare statement
            $values = ' (';

            // Loop through the rows
            foreach ($db_rows as $row) {
                // Add row value 'insert to'
                $prepare_statement .= $row . ', ';

                // As well, increase the amount of values we insert
                $values .= '?,';
            }
            // Remove the last `, `
            $prepare_statement = substr($prepare_statement, 0, -2);
            // Remove the last `,`
            $values = substr($values, 0, -1);
            // Finish the prepare statement
            $prepare_statement .= ') VALUES' . $values . ') ON DUPLICATE KEY UPDATE ';

            // Remove any ID data for ON DUPLICATE so we will update everything besides the ID
            foreach( $db_rows as $row ){
                // Do we have ID on the word?
                if( !strpos($row, 'id') ) {
                    // If we don't add it
                    $prepare_statement.= $row.' = ?, ';
                }
            }

            // Remove the last `, `
            $prepare_statement = substr($prepare_statement, 0, -2);

            // Prepare the statement
            $stmt = $this->pdo->prepare($prepare_statement);

            // Make an array of data to execute
            foreach ($data as $key => $each_data) {
                $exec_data[] = $each_data;
            }

            // For each ON DUPLICATE bind the data
            for( $i = 0 ; $i < count($db_rows); $i++ ) {
                if( !strpos($db_rows[$i], 'id') ) {
                    $exec_data[] = $data[$i];
                }
            }

            // Do we created the new row?
            if ( isset($exec_data) && $stmt->execute($exec_data) ) {
                return true;
            } else {
                // Otherwise, we haven't
                return false;
            }
        } catch (PDOException $e) {
            throw new BlackJackException('Cannot insert the row!');
        }
    }

    /**
     * Remove sensitive data before sending it back to the updater.
     * @param $data array   The data we wants to edit by reference.
     */
    private function removeSensitiveData(&$data) {
        // Loop through the sensitive data
        foreach( $this->sensitive_data as $sensitive_index ) {
            // Remove the sensitive index
            if( isset($data[$sensitive_index]) ) unset($data[$sensitive_index]);
        }
    }

    /**
     * Gets the last key that was inserted.
     */
    public function getLastKeyInserted(){
        return $this->pdo->lastInsertId('id');
    }
}