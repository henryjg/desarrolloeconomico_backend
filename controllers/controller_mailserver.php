<?php
include_once './utils/response.php';
include_once './config/database.php';

class email_server {
    private $database;

    public function __construct() {
        global $database;
        $this->database = $database;
    }
    
    //---------------------------------------------------------------------------------
    function sendmail($codigo, $nombre, $correo){
        $curl = curl_init();

        // Datos que se enviarán mediante POST
        $postData = [
            'op' => "enviar_correo",
            'codigo' => $codigo,
            'nombre' => $nombre,
            'correo' => $correo
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://accom.pe/mailserver/apiweb.php', // URL del API
            CURLOPT_RETURNTRANSFER => true, // Obtener respuesta como string
            CURLOPT_SSL_VERIFYPEER => false, // Evitar verificación SSL (no recomendado en producción)
            CURLOPT_ENCODING => '', // Manejo de encoding
            CURLOPT_MAXREDIRS => 2, // Número máximo de redirecciones
            CURLOPT_TIMEOUT => 0, // Sin límite de tiempo
            CURLOPT_FOLLOWLOCATION => true, // Seguir redirecciones
            CURLOPT_POST => true, // Indicar que es una solicitud POST
            CURLOPT_POSTFIELDS => http_build_query($postData), // Datos POST
            CURLOPT_HTTPHEADER => [
                'Referer: https://accom.pe/mailserver/apiweb.php',
                'Content-Type: application/x-www-form-urlencoded' // Tipo de contenido
            ]
        ]);

        // Ejecutar la solicitud y capturar la respuesta
        $response = curl_exec($curl);

        // Manejo de errores
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            curl_close($curl);
            return "Error en la solicitud: $error_msg";
        }
        curl_close($curl);
        return $response;
    }

    //---------------------------------------------------------------------------------
    function sendmail_html($nombre,$correo,$asunto,$cuerpohtml){
        $curl = curl_init();

        // Datos que se enviarán mediante POST
        $postData = [
            'op' => "enviar_correo_html",
            'nombre' => $nombre,
            'correo' => $correo,
            'asunto' => $asunto,
            'cuerpohtml' => $cuerpohtml
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://accom.pe/mailserver/apiweb.php', // URL del API
            CURLOPT_RETURNTRANSFER => true, // Obtener respuesta como string
            CURLOPT_SSL_VERIFYPEER => false, // Evitar verificación SSL (no recomendado en producción)
            CURLOPT_ENCODING => '', // Manejo de encoding
            CURLOPT_MAXREDIRS => 2, // Número máximo de redirecciones
            CURLOPT_TIMEOUT => 0, // Sin límite de tiempo
            CURLOPT_FOLLOWLOCATION => true, // Seguir redirecciones
            CURLOPT_POST => true, // Indicar que es una solicitud POST
            CURLOPT_POSTFIELDS => http_build_query($postData), // Datos POST
            CURLOPT_HTTPHEADER => [
                'Referer: https://accom.pe/mailserver/apiweb.php',
                'Content-Type: application/x-www-form-urlencoded' // Tipo de contenido
            ]
        ]);

        // Ejecutar la solicitud y capturar la respuesta
        $response = curl_exec($curl);

        // Manejo de errores
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            curl_close($curl);
            return "Error en la solicitud: $error_msg";
        }
        curl_close($curl);
        return $response;
    }
}
?>
