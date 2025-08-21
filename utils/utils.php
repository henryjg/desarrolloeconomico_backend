<?php

class utils {
    private $database;

    public function __construct() {
        global $database;
        $this->database = $database;
    }

    
    public function NombreMes($numeroDelMes) {
        // Definir el array de meses
        $meses = array(
            1 => "Enero",
            2 => "Febrero",
            3 => "Marzo",
            4 => "Abril",
            5 => "Mayo",
            6 => "Junio",
            7 => "Julio",
            8 => "Agosto",
            9 => "Septiembre",
            10 => "Octubre",
            11 => "Noviembre",
            12 => "Diciembre"
        );
    
        if (is_numeric($numeroDelMes) && $numeroDelMes >= 1 && $numeroDelMes <= 12) {
            return $meses[$numeroDelMes];
        } else {
            return "Número de mes inválido";
        }
    }
}
