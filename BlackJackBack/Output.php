<?php
/**
 * Class Output
 * Responsible of everything we returning to the front end.
 */
class Output {
    /**
     * This method will send the front end failed status with the message and the data.
     * @param $data     array|string    The data we sending to the front end.
     * @param $status   boolean         `true` if successfully done, otherwise `false`.
     * @param string $error_message     The message if we have an error
     */
    static function outputData($data = '', $status = true, $error_message = ''){
        // Set the return array, in case of success `true` don't insert error
        $return_arr = $status ? array('success' => $status) : array('success' => $status, 'error' => $error_message);
        // In case of error $data will keep being empty
        $return_arr = is_array($data) ? $return_arr  + $data: $return_arr;
        // Echo the data
        echo json_encode($return_arr, false);
    }
}