<?php
class response {
    public static function success($data, $message = '') {
        $response = [
            'success' => true,
            'data' => $data,
            'message' => $message
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public static function errores($data, $message = '') {
        $response = [
            'success' => false,
            'data' => $data,
            'message' => $message
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }


    public static function error($message) {
        $response = [
            'success' => false,
            'message' => $message
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
