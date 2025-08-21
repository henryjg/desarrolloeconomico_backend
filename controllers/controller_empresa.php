<?php
include_once './utils/response.php';
include_once './config/database.php';

class EmpresaController {
    private $database;

    public function __construct() {
        global $database;
        $this->database = $database;
    }

    // ------------------------------------------------------
    public function getEmpresa() {
        $conexion = new Conexion();
        $query = "SELECT 
                    emp_nombrecorto as nombreCorto,
                    emp_razonsocial as razonSocial,
                    emp_ruc as ruc,
                    emp_nosotros as nosotros,
                    emp_mision as mision,
                    emp_vision as vision,
                    emp_valores as valores,
                    emp_fechalimite as fechalimite,
                    emp_celular as celular,
                    emp_celular2 as celular2,
                    emp_direccion as direccion,
                    emp_email as email,
                    emp_contacto as contacto,
                    emp_slogan as slogan,
                    emp_titulopagina as tituloPagina,
                    emp_metatag as metaTag,
                    emp_facebook as facebook,
                    emp_instragram as instragram,
                    emp_youtube as youtube,
                    emp_pixel as pixel,
                    emp_reglamentoUrl as reglamentoUrl
                FROM empresa 
                WHERE emp_id = '1'";
        $result = $conexion->ejecutarConsulta($query);

        if ($result && $result->num_rows > 0) {
            $empresa = $result->fetch_assoc();
            response::success($empresa, 'Consulta de empresa exitosa');
        } else {
            response::error('No se encontró la empresa');
        }
    }

    // ------------------------------------------------------
    public function updateEmpresa(
        $emp_nombrecorto,
        $emp_razonsocial,
        $emp_ruc,
        $emp_gerente,
        $emp_nosotros,
        $emp_mision,
        $emp_vision,
        $emp_valores,
        $emp_fechalimite,
        $emp_celular,
        $emp_celular2,
        $emp_direccion,
        $emp_email,
        $emp_contacto,
        $emp_slogan,
        $emp_titulopagina,
        $emp_metatag,
        $emp_facebook,
        $emp_instragram,
        $emp_youtube,
        $emp_pixel
    ) {
        $conexion = new Conexion();
        $query = "UPDATE empresa SET 
                    emp_nombrecorto = '$emp_nombrecorto',
                    emp_razonsocial = '$emp_razonsocial',
                    emp_ruc = '$emp_ruc',
                    emp_gerente = '$emp_gerente',
                    emp_nosotros = '$emp_nosotros',
                    emp_mision = '$emp_mision',
                    emp_vision = '$emp_vision',
                    emp_valores = '$emp_valores',
                    emp_fechalimite = '$emp_fechalimite',
                    emp_celular = '$emp_celular',
                    emp_celular2 = '$emp_celular2',
                    emp_direccion = '$emp_direccion',
                    emp_email = '$emp_email',
                    emp_contacto = '$emp_contacto',
                    emp_slogan = '$emp_slogan',
                    emp_titulopagina = '$emp_titulopagina',
                    emp_metatag = '$emp_metatag',
                    emp_facebook = '$emp_facebook',
                    emp_instragram = '$emp_instragram',
                    emp_youtube = '$emp_youtube',
                    emp_pixel = '$emp_pixel'
                WHERE emp_id = 1";

        $result = $conexion->save($query);

        if ($result > 0) {
            response::success($result, 'Empresa actualizada correctamente');
        } else {
            response::error('Error al actualizar la empresa');
        }
    }
    // ------------------------------------------------------
    public function updateCampoEmpresa($campo,$valor) {
        $nombreCampo = "emp_".$campo;
        $conexion = new Conexion();
        $query = "UPDATE empresa SET ".$nombreCampo." = '$valor'
                WHERE emp_id = 1";

        $result = $conexion->save($query);

        if ($result > 0) {
            response::success($result, 'Empresa actualizada correctamente');
        } else {
            response::error('Error al actualizar la empresa');
        }
    }

    //---------------------------------------------------------------------------------
    // ------------------------------------------------------
    public function Guardar_pdf($file) {
        // Subir archivo y obtener la URL
        $file_url = $this->subir_archivo_pdf($file);
        $nombreCampo = "emp_reglamentoUrl";
        $conexion = new Conexion();
        $query = "UPDATE empresa SET ".$nombreCampo." = '$file_url'
                WHERE emp_id = 1";
        $result = $conexion->save($query);

        if ($result > 0) {
            response::success($result, 'Empresa actualizada correctamente');
        } else {
            response::error('Error al actualizar la empresa');
        }

    }
  
    // ------------------------------------------------------
    public function listarFotos() {
        $conexion = new Conexion();
        $query = "SELECT 
                    foto_id as id,
                    foto_url as url
                FROM fotos";
        $result = $conexion->ejecutarConsulta($query);

        if ($result && $result->num_rows > 0) {
            $fotos = array();
            while ($foto = $result->fetch_assoc()) {
                $fotos[] = $foto;
            }
            response::success($fotos, 'Lista de fotos obtenida correctamente');
        } else {
            response::error('No se encontraron fotos');
        }
    }

    // ------------------------------------------------------
    public function insertFoto($foto_file) {
        // Subir archivo y obtener la URL
        $foto_url = $this->subir_archivo($foto_file);

        if ($foto_url != "") {
            $conexion = new Conexion();
            $query = "INSERT INTO fotos (foto_url) VALUES ('$foto_url')";

            $result = $conexion->insertar($query);

            if ($result > 0) {
                response::success($result, 'Foto insertada correctamente');
            } else {
                response::error('Error al insertar la foto');
            }
        } else {
            response::error('Error al subir la foto');
        }
    }

    // ------------------------------------------------------
    public function deleteFoto($foto_id) {
        $conexion = new Conexion();
        $query = "DELETE FROM fotos WHERE foto_id = $foto_id";
        $result = $conexion->save($query);

        if ($result > 0) {
            response::success($result, 'Foto eliminada correctamente');
        } else {
            response::error('Error al eliminar la foto');
        }
    }
    //---------------------------------------------------------------------------------

    //RUTAS 

    // ------------------------------------------------------
    public function listarFotoRuta() {
        $conexion = new Conexion();
        $query = "SELECT 
                    foto_id as id,
                    foto_url as url
                FROM rutas";
        $result = $conexion->ejecutarConsulta($query);

        if ($result && $result->num_rows > 0) {
            $fotos = array();
            while ($foto = $result->fetch_assoc()) {
                $fotos[] = $foto;
            }
            response::success($fotos, 'Lista de fotos obtenida correctamente');
        } else {
            response::error('No se encontraron fotos');
        }
    }

    // ------------------------------------------------------
    public function insertFotoRuta($foto_file) {
        // Subir archivo y obtener la URL
        $foto_url = $this->subir_archivo($foto_file);

        if ($foto_url != "") {
            $conexion = new Conexion();
            $query = "INSERT INTO rutas (foto_url) VALUES ('$foto_url')";

            $result = $conexion->insertar($query);

            if ($result > 0) {
                response::success($result, 'Foto insertada correctamente');
            } else {
                response::error('Error al insertar la foto');
            }
        } else {
            response::error('Error al subir la foto');
        }
    }

    // ------------------------------------------------------
    public function deleteFotoRuta($foto_id) {
        $conexion = new Conexion();
        $query = "DELETE FROM rutas WHERE foto_id = $foto_id";
        $result = $conexion->save($query);

        if ($result > 0) {
            response::success($result, 'Foto eliminada correctamente');
        } else {
            response::error('Error al eliminar la foto');
        }
    }
    //---------------------------------------------------------------------------------
    
    public function detecta_extension($mi_extension)
    {
        $ext = explode(".", $mi_extension);
        return end($ext);
    }

    public function subir_archivo($imgFile){
        if ($imgFile != "") {
            $ruta = "./uploads/servicio/";
            if (!file_exists($ruta)) {
                mkdir($ruta);
            }
    
            $nuevo_nombre = "servicio_" . rand(1000000, 9999999);
            $nuevo_nombre_completo = $nuevo_nombre . '.' . $this->detecta_extension(basename($imgFile['name']));
            $uploadfile = $ruta . $nuevo_nombre_completo;
            $ruta_archivo = $ruta . $nuevo_nombre_completo;
    
            $permitidos = array("image/bmp", "image/jpg", "image/jpeg", "image/png");
            if (in_array($imgFile['type'], $permitidos)) {
                if (move_uploaded_file($imgFile['tmp_name'], $uploadfile)) {
                    switch ($imgFile['type']) {
                        case 'image/bmp':
                            $imagen = imagecreatefrombmp($uploadfile);
                            break;
                        case 'image/jpg':
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg($uploadfile);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng($uploadfile);
                            break;
                        default:
                            $imagen = false;
                    }
    
                    if ($imagen !== false) {
                        $ancho_original = imagesx($imagen);
                        $alto_original = imagesy($imagen);
    
                        // Dimensiones deseadas (relación 4:3)
                        $ancho_deseado = 1200;
                        $alto_deseado = 750;
    
                        // Calcular la relación de aspecto original
                        $ratio_original = $ancho_original / $alto_original;
                        $ratio_deseado = $ancho_deseado / $alto_deseado;
    
                        // Redimensionar manteniendo la proporción y luego recortar
                        if ($ratio_original > $ratio_deseado) {
                            // La imagen es más ancha que el 4:3 deseado
                            $nuevo_alto = $alto_original;
                            $nuevo_ancho = intval($alto_original * $ratio_deseado);
                            $x_offset = ($ancho_original - $nuevo_ancho) / 2;
                            $y_offset = 0;
                        } else {
                            // La imagen es más alta que el 4:3 deseado
                            $nuevo_ancho = $ancho_original;
                            $nuevo_alto = intval($ancho_original / $ratio_deseado);
                            $x_offset = 0;
                            $y_offset = ($alto_original - $nuevo_alto) / 2;
                        }
    
                        // Crear una imagen recortada a 4:3
                        $imagen_recortada = imagecrop($imagen, [
                            'x' => $x_offset,
                            'y' => $y_offset,
                            'width' => $nuevo_ancho,
                            'height' => $nuevo_alto,
                        ]);
    
                        if ($imagen_recortada !== false) {
                            // Redimensionar a las dimensiones finales deseadas
                            $imagen_final = imagecreatetruecolor($ancho_deseado, $alto_deseado);
                            imagecopyresampled(
                                $imagen_final,
                                $imagen_recortada,
                                0, 0, 0, 0,
                                $ancho_deseado, $alto_deseado,
                                $nuevo_ancho, $nuevo_alto
                            );
    
                            // Guardar la imagen final
                            imagejpeg($imagen_final, $uploadfile);
    
                            // Liberar memoria
                            imagedestroy($imagen_final);
                            imagedestroy($imagen_recortada);
                        }
    
                        // Liberar la imagen original
                        imagedestroy($imagen);
                    }
                }
            }
        } else {
            $ruta_archivo = "";
        }
    
        return $ruta_archivo;
    }

    public function subir_archivo_pdf($file){
        if($file!=""){
            // Declarar la ruta
            $ruta = 'uploads/documentos/';
    
            if (!file_exists($ruta)) {
                mkdir($ruta, 0777, true);
            }
    
            $nuevo_nombre = "file_".rand(1000000, 9999999);
            $nuevo_nombre_completo = $nuevo_nombre . '.' . $this->detecta_extension(basename($file['name']));
            $uploadfile = $ruta . $nuevo_nombre_completo;
            $ruta_archivo = $ruta . $nuevo_nombre_completo;
    
            $restriccionLogo = "NOPERMITIDO";
    
            // Validamos Tipo --------------------------------------------------------
            $permitidos = array("application/pdf");
            if (in_array($file['type'], $permitidos)) {
                $restriccionLogo = "PERMITIDO";
    
                if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
                    $restriccionLogo = "PERMITIDO";
                }
            }
    
        } else {
            $ruta_archivo = "";
        }
        return $ruta_archivo;
    }
    
}
?>
