<?php
include_once './utils/response.php';
include_once './config/database.php';

class ArchivoController {
    private $database;

    public function __construct() {
        $this->database = new Database();
    }

    // ------------------------------------------------------
    public function insertArchivo($archivo_tipo, $archivo_nombre, $archivo_descripcion, $archivo_url, $archivo_fechasub) {
        
        $query = "INSERT INTO siga_archivo (
                    archivo_tipo,
                    archivo_nombre,
                    archivo_descripcion,
                    archivo_url,
                    archivo_fechasub
                  ) VALUES (?, ?, ?, ?, ?)";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $archivo_tipo,
                $archivo_nombre,
                $archivo_descripcion,
                $archivo_url,
                $archivo_fechasub
            ]);
        
            if ($result) {
                response::success($result, 'Archivo insertado correctamente');
            } else {
                response::error('Error al insertar el archivo');
            }
        } catch (PDOException $e) {
            response::error('Error al insertar el archivo: ' . $e->getMessage());
        }
    }
    

    // ------------------------------------------------------
    public function getArchivo($archivo_id) {
        $query = "SELECT 
                    archivo_id AS id,
                    archivo_tipo AS tipo,
                    archivo_nombre AS nombre,
                    archivo_descripcion AS descripcion,
                    archivo_url AS url,
                    archivo_fechasub AS fechasubida
                FROM siga_archivo 
                WHERE archivo_id = ?";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$archivo_id]);

            if ($result && count($result) > 0) {
                response::success($result[0], 'Consulta de archivo exitosa');
            } else {
                response::error('No se encontró el archivo');
            }
        } catch (PDOException $e) {
            response::error('Error al consultar el archivo: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function getArchivos() {
        $query = "SELECT 
                    archivo_id AS id,
                    archivo_tipo AS tipo,
                    archivo_nombre AS nombre,
                    archivo_descripcion AS descripcion,
                    archivo_url AS url,
                    archivo_fechasub AS fechasubida
                FROM siga_archivo";

        try {
            $result = $this->database->ejecutarConsulta($query);

            if ($result && count($result) > 0) {
                response::success($result, 'Lista de archivos obtenida correctamente');
            } else {
                response::error('No se encontraron archivos registrados');
            }
        } catch (PDOException $e) {
            response::error('Error al consultar los archivos: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function updateArchivo($archivo_id, $archivo_tipo, $archivo_nombre, $archivo_descripcion, $archivo_url, $archivo_fechasub) {
        $query = "UPDATE siga_archivo SET 
                    archivo_tipo = ?, 
                    archivo_nombre = ?, 
                    archivo_descripcion = ?, 
                    archivo_url = ?, 
                    archivo_fechasub = ? 
                WHERE archivo_id = ?";

        try {
            $result = $this->database->ejecutarActualizacion($query, [
                $archivo_tipo,
                $archivo_nombre,
                $archivo_descripcion,
                $archivo_url,
                $archivo_fechasub,
                $archivo_id
            ]);

            if ($result > 0) { // Se utiliza > 0 para asegurar que se actualizó al menos un registro
                response::success(null, 'Archivo actualizado correctamente');
            } else {
                response::error('Error al actualizar el archivo o no se encontró el archivo para actualizar');
            }
        } catch (PDOException $e) {
            response::error('Error al actualizar el archivo: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function deleteArchivo($archivo_id) {
        $query = "DELETE FROM siga_archivo WHERE archivo_id = ?";

        try {
            $result = $this->database->ejecutarActualizacion($query, [$archivo_id]);

            if ($result > 0) { // Se utiliza > 0 para asegurar que se eliminó al menos un registro
                response::success(null, 'Archivo eliminado correctamente');
            } else {
                response::error('Error al eliminar el archivo o no se encontró el archivo para eliminar');
            }
        } catch (PDOException $e) {
            response::error('Error al eliminar el archivo: ' . $e->getMessage());
        }
    }

    public function subir_archivo_pdf($file){
        if( $file != "" ){
            // Declarar la ruta
            $ruta = 'uploads/documentos_tramite/';
    
            if (!file_exists($ruta)) {
                mkdir($ruta, 0777, true);
            }
    
            $nuevo_nombre = "pdf_" . date("dmY") . "_" . rand(1000000, 9999999);
            $nuevo_nombre_completo = $nuevo_nombre . '.' . $this->detecta_extension(basename($file['name']));
            $uploadfile = $ruta . $nuevo_nombre_completo;
            $ruta_archivo = $ruta . $nuevo_nombre_completo;
    
    
            // Validamos Tipo --------------------------------------------------------
            $permitidos = array("application/pdf");
            if (in_array($file['type'], $permitidos)) {                
                if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
                    response::success($ruta_archivo, 'Archivo subido correctamente');
                }
            }else{
                response::error('Este documento no fue subido');
            }
        } else {
            response::error('No hay adjunto');
        }
        
    }

    public function subir_archivo_ofimatica($file){
        if( $file != "" ){
            // Declarar la ruta
            $ruta = 'uploads/documentos_tramite/';
    
            if (!file_exists($ruta)) {
                mkdir($ruta, 0777, true);
            }
    
            $extension = $this->detecta_extension(basename($file['name']));
            $nuevo_nombre = "doc_" . date("dmY") . "_" . rand(1000000, 9999999);
            $nuevo_nombre_completo = $nuevo_nombre . '.' . $extension;
            $uploadfile = $ruta . $nuevo_nombre_completo;
            $ruta_archivo = $ruta . $nuevo_nombre_completo;
    
    
            // Validamos Tipo --------------------------------------------------------
            $permitidos_mime = array(
                // PDF
                "application/pdf",
                // Microsoft Word
                "application/msword",
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                // Microsoft Excel
                "application/vnd.ms-excel",
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                // Microsoft PowerPoint
                "application/vnd.ms-powerpoint",
                "application/vnd.openxmlformats-officedocument.presentationml.presentation",
                // PowerBI
                "application/vnd.ms-powerbi",
                "application/x-powerbi",
                // AutoCAD
                "application/acad",
                "application/x-acad",
                "application/autocad_dwg",
                "image/x-dwg",
                "application/dwg",
                "application/x-dwg",
                "application/x-autocad",
                "image/vnd.dwg",
                // Adobe Illustrator
                "application/illustrator",
                "application/postscript",
                // Adobe Photoshop
                "image/vnd.adobe.photoshop",
                "application/photoshop",
                "application/psd",
                "image/psd",
                // Archivos comprimidos
                "application/zip",
                "application/x-rar-compressed",
                "application/vnd.rar",
                "application/x-zip-compressed",
                "application/x-7z-compressed",
                // Otros formatos de texto
                "text/plain",
                "application/rtf",
                // MIME types genéricos que pueden usar algunos navegadores
                "application/octet-stream"
            );

            // Extensiones permitidas como respaldo
            $extensiones_permitidas = array(
                'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 
                'pbix', 'dwg', 'dxf', 'ai', 'psd', 'zip', 'rar', 
                '7z', 'txt', 'rtf'
            );
            
            $extension_archivo = strtolower($extension);
            $mime_valido = in_array($file['type'], $permitidos_mime);
            $extension_valida = in_array($extension_archivo, $extensiones_permitidas);
            
            if ($mime_valido || $extension_valida) {                
                if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
                    response::success($ruta_archivo, 'Archivo subido correctamente');
                } else {
                    response::error('Error al mover el archivo al directorio de destino');
                }
            }else{
                response::error('Tipo de archivo no permitido. Archivo detectado: ' . $file['type'] . ' con extensión: .' . $extension_archivo . '. Tipos permitidos: PDF, Word, Excel, PowerPoint, PowerBI, AutoCAD (.dwg, .dxf), Illustrator, Photoshop, RAR, ZIP, etc.');
            }
        } else {
            response::error('No hay adjunto');
        }
        
    }
    public function detecta_extension($mi_extension) {
        $ext = explode(".", $mi_extension);
        return end($ext);
    }


    public function subir_archivo_imgaen_pdf($file){
        if( $file != "" ){
            // Declarar la ruta
            $ruta = 'uploads/documentos_tramite/';
    
            if (!file_exists($ruta)) {
                mkdir($ruta, 0777, true);
            }
    
            $nuevo_nombre = "pdf_" . date("dmY") . "_" . rand(1000000, 9999999);
            $nuevo_nombre_completo = $nuevo_nombre . '.' . $this->detecta_extension(basename($file['name']));
            $uploadfile = $ruta . $nuevo_nombre_completo;
            $ruta_archivo = $ruta . $nuevo_nombre_completo;
    
    
            // Validamos Tipo --------------------------------------------------------
            $permitidos = array("application/pdf");
            if (in_array($file['type'], $permitidos)) {                
                if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
                    response::success($ruta_archivo, 'Archivo subido correctamente');
                }
            }else{
                response::error('Este documento no fue subido');
            }
        } else {
            response::error('No hay adjunto');
        }
        
    }
}
?>
