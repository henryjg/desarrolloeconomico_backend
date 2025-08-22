<?php
include_once './controllers/controllers.php';
include_once './utils/response.php';

ob_start();
header('Content-Type: application/json; charset=utf-8');
// Permite solicitudes desde cualquier origen
header("Access-Control-Allow-Origin: *");
// Permite solicitudes con los siguientes métodos HTTP: GET, POST, PUT, DELETE, OPTIONS
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// Permite que los encabezados personalizados se incluyan en las solicitudes
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Permite que las cookies se incluyan en las solicitudes
header("Access-Control-Allow-Credentials: true");

//Obteniendo Parámetros de Petición
$metodoPeticion = $_SERVER['REQUEST_METHOD'];
$tipoContenidoPeticion = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : null;
// ----------------------
if ($tipoContenidoPeticion === "application/json") {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    $data = array_map('cleanField', $data); // Limpieza de datos JSON
    $peticion = $data['op'];
} else if ($tipoContenidoPeticion !== null && (strpos($tipoContenidoPeticion, "multipart/form-data") !== false ||
    strpos($tipoContenidoPeticion, "application/x-www-form-urlencoded") !== false)) {

    $data = array();
    foreach ($_POST as $key => $value) {
        $data[$key] = cleanField($value); // Limpieza de datos de formulario
    }
    $peticion = $data['op'];
} else {
    // Tipo de contenido desconocido
    Response::error('Tipo de contenido desconocido: ' . $tipoContenidoPeticion);
}

// ----------------------
// Importación de Controladores
// ----------------------

$SUNAT                  = new apiSunat();

$UsuarioController   = new UsuarioController();

$PaseController      = new PaseController();


$AdministradoController = new AdministradoController();
$DocumentoController    = new DocumentoController();
$RequisitoController    = new RequisitoController();
$TramiteController      = new TramiteController();
$LicenciaController     = new LicenciaController();
$ArchivoController      = new ArchivoController();
$UserCasillaController  = new UserCasillaController();
$ControladorUbigeo      = new UbigeoController();

$TipoDocumentoController = new TipoDocumentoController();
$ArchivosController = new ArchivoController();

$EmpresaController      = new EmpresaController();
$BuzonController        = new BuzonController();
$OficinaController     = new OficinaController();
$emailServer = new email_server();

// ----------------------
// Procesamiento de la petición
// ----------------------
if ($metodoPeticion === 'POST') {
    switch ($peticion) {
            // ----------------------
            // DESDE PORTAL WEB 
            // ----------------------   
            // PENDIENTE:
        case 'get_Empresa':
            echo $EmpresaController->getEmpresa();
            break;

            // PENDIENTE:
        case 'upd_campo':
            $campo     =  $data['Campo'];
            $valor     =  $data['Valor'];

            echo $EmpresaController->updateCampoEmpresa($campo, $valor);
            break;

        case 'validarToken':
            $token     =  $data['token'];
            echo $DocumentoController->validarRecaptcha($token);
            break;
    
        // ----------------------
        // SUNAT
        // ----------------------
        
        case 'get_datos_dni':
            $dni = $data['dni'];
            echo $SUNAT->get_datos_X_DNI($dni);
            break;

        case 'get_datos_ruc':
            $ruc = $data['ruc'];
            echo $SUNAT->get_datos_X_RUC($ruc);
            break;

        case 'get_ubigeo_lista':
            echo $ControladorUbigeo->get_ubigeo_lista();
            break;

        case 'get_ubigeo_json':
            echo $ControladorUbigeo->get_ubigeo_json();
            break;

        case 'get_departamentos':
            echo $ControladorUbigeo->obtenerDepartamentos();
            break;

        case 'get_provincias':
            $departamento = $data['departamento'];
            echo $ControladorUbigeo->obtenerProvincias($departamento);
            break;

        case 'get_distritos':
            $provincia = $data['provincia'];
            echo $ControladorUbigeo->obtenerDistritos($provincia);
            break;
            // ----------------------
            // trabajadores
            // ----------------------

            // PENDIENTE:       
        case 'loggin':
            $usuario = $data['user'];
            $clave = $data['pass'];
            echo $UsuarioController->login($usuario, $clave);
            break;

        case 'logginAdm':
            $user = $data['usuario'];
            $pass = $data['contrasena'];
            echo $UserCasillaController->loginUserCasilla($user, $pass);
            break;

            // ----------------------
            // Trabajador 
            // ----------------------
        case 'get_usuarios_tramitedocumentario':
            echo $UsuarioController->getUsuarios_tramitedocumentario();
            break;

        case 'get_usuarios':
            echo $UsuarioController->getUsuarios_tramitedocumentario();
            break;
      
        case 'desactiva_usuario':
            $id = $data['id'];
            echo $UsuarioController->updateEstado($id, 0); // 0 para inactivo
            break;

        case 'activa_usuario':
            $id = $data['id'];
            echo $UsuarioController->updateEstado($id, 1); // 0 para activar
            break;

        case 'del_usuario':
            $id = $data['id'];
            echo $UsuarioController->deleteUsuario($id); // 0 para activar
            break;

        case 'get_usuario':
            $id = $data['id'];
            echo $UsuarioController->getUsuario($id);
            break;

        case 'getUsuario_principaloficina':
            $id_oficina = $data['id_oficina'];
            echo $UsuarioController->getUsuario_principaloficina($id_oficina);
            break;

        case 'add_usuario':

            $tipodocumento = $data['tipodocumento'];
            $numdocumento = $data['numdocumento'];
            $nombres = $data['nombres'];
            $apellidos = $data['apellidos'];
            $correo = $data['correo'];
            $celular = $data['celular'];

            $username = $data['username'];

            $usuario = $data['usuario'];
            $password = $data['password'];

            $rol_id = $data['rol_id'];
            $oficina_id = $data['oficina_id'];

            echo $UsuarioController->addUsuario(
                $tipodocumento,
                $numdocumento,
                $nombres,
                $apellidos,
                $correo,
                $celular,
                $username,
                $usuario,
                $password,
                $rol_id,
                $oficina_id
            );
            break;
        
        case 'add_usuario_buzon':
            $tipodocumento = $data['tipodocumento'];
            $numdocumento = $data['numdocumento'];
            $nombres = $data['nombres'];
            $apellidos = $data['apellidos'];
            $correo = $data['correo'];
            $celular = $data['celular'];

            $username   = $data['username'];
            $usuario    = $data['usuario'];
            $password   = $data['password'];
            $rol_id     = $data['rol_id'];
            $oficina_id = $data['oficina_id'];

            $buzon_nombre = $data['buzon_nombre'];
            $buzon_tipo   = $data['buzon_tipo'];
            $buzon_sigla  = $data['buzon_sigla'];

            echo $UsuarioController->addUsuario_buzon(
                $tipodocumento,
                $numdocumento,
                $nombres,
                $apellidos,
                $correo,
                $celular,
                $username,
                $usuario,
                $password,
                $rol_id,
                $oficina_id,
                $buzon_nombre,
                $buzon_tipo,
                $buzon_sigla
            );
            break;
        case 'upd_usuario':
            $id = $data['id'];

            $tipodocumento = $data['tipodocumento'];
            $numdocumento = $data['numdocumento'];
            $nombres = $data['nombres'];
            $apellidos = $data['apellidos'];
            $correo = $data['correo'];
            $celular = $data['celular'];

            $username = $data['username'];
            $usuario = $data['usuario'];
            $esactivo = $data['esactivo'];
            $rol_id = $data['rol_id'];
            $oficina_id = $data['oficina_id'];

            echo $UsuarioController->updateUsuario(
                $id,
                
                $tipodocumento,
                $numdocumento,
                $nombres,
                $apellidos,
                $correo,
                $celular,

                $username,
                $usuario,
                $esactivo,
                $rol_id,
                $oficina_id
            );
            break;

        case 'get_unidadesorganicas_buzones':
            echo $UsuarioController->get_unidadesorganicas_buzones();
            break;

        case 'get_all_buzones':
            $idoficina = $data['idoficina'];
            echo $UsuarioController->get_all_buzones($idoficina);
            break;

        case 'get_all_buzones_oficina':
            $idoficina = $data['idoficina'];
            echo $UsuarioController->get_buzones_trabajadores_oficina($idoficina);
            break;

        case 'upd_password':
            $id = $data['id'];
            $password = $data['password'];
            echo $UsuarioController->updatePassword($id, $password);
            break;

        case 'upd_usuario_foto':
            $id = $data['id'];
            $fotourl = $data['fotoperfil'];
            echo $UsuarioController->updateFoto($id, $fotourl);
            break;


        case 'subir_imagen':
            if (isset($_FILES['file_fotoperfil'])) {
                $file_fotoperfil = $_FILES['file_fotoperfil'];
            } else {
                $file_fotoperfil = "";
            }
            $ruta = 'uploads/fotos/';
            echo $UsuarioController->Subir_Imagen($file_fotoperfil, $ruta);
            break;

        case 'lista_roles':
            echo $UsuarioController->getRoles();
            break;

        case 'getRolByUserId':
            $id = $data['id'];
            echo $UsuarioController->getRolByUserId($id);
            break;
               
        //----------------------------------- 
        // Pase
        // ---------------------------------
        // case 'addPase':
        //     $pase_oficina_origen = $data['origen'];
        //     $pase_oficina_destino = $data ['destino'];
        //     $pase_tipo = $data['tipo'];
        //     $pase_usuario_id = $data['idUsuario'];
        //     $pase_usuarionombre = $data['usuarioNombre'];
        //     $pase_proveido = $data['proveido'];
        //     $pase_descripcion = $data['descripcion'];           
        //     $pase_estado = $data['estado'];
        //     $pase_documento_id = $data['idDocumento'];
        //     $pase_primogenio_id = $data['primogenio_id'];
        //     $pase_tipoaccion = $data['tipoaccion'];

        //     $pase_id_previo = $data['idpase_previo'];

        //     echo $PaseController->insertPase($pase_oficina_origen,
        //         $pase_oficina_destino,$pase_tipo,
        //         $pase_usuario_id,$pase_usuarionombre,
        //         $pase_proveido,$pase_descripcion,
        //         $pase_estado,$pase_documento_id,$pase_id_previo,$pase_tipoaccion,$pase_primogenio_id
        //     );
        //     break;

            case 'listarPases':
                echo $PaseController->getPases();
                break;

            case 'getPase':
                $pase_id = $data['id'];
                echo $PaseController->getPase($pase_id);
                break;

            case 'getdestino_oficinas':
                $iddoc = $data['iddoc'];
                echo $DocumentoController->get_destinos_por_iddocumento($iddoc);
                break;

            case 'deletePase':
                $pase_id = $data['id'];
                echo $PaseController->deletePase($pase_id);
                break;
            
            // case 'updPase':
            //     $pase_id = $data['id'];
            //     $pase_oficina_origen = $data['origen'];
            //     $pase_oficina_destino = $data['destino'];
            //     $pase_tipo = $data['tipo'];
            //     $pase_usuario_id = $data['usuarioId'];
            //     $pase_usuarionombre = $data['usuarioNombre'];
            //     $pase_proveido = $data['proveido'];
            //     $pase_descripcion = $data['descripcion'];
            //     $pase_estado = $data['estado'];
            //     $pase_documento_id = $data['documentoId'];
            //     echo $PaseController->updatePase(
            //         $pase_id,
            //         $pase_oficina_origen,
            //         $pase_oficina_destino,
            //         $pase_tipo,
            //         $pase_usuario_id,
            //         $pase_usuarionombre,
            //         $pase_proveido,
            //         $pase_descripcion,
            //         $pase_estado,
            //         $pase_documento_id
            //     );
            //     break;

            case 'updPase_recepcionar':
                $pase_id = $data['idpase'];
                echo $PaseController->updPase_recepcionar(
                    $pase_id
                );
                break;

            case 'Confirmar_enviomesapartes':
                $pase_id = $data['id'];
                $pase_estado = $data['estado'];
                echo $PaseController->Update_estado_pase_mesapartes(
                    $pase_id,
                    $pase_estado
                );
                break;

            case 'Confirmar_expediente_proyectado':
                $pase_id = $data['id'];
                $pase_estado = $data['estado'];
                echo $PaseController->Update_estado_expediente_proyectado(
                    $pase_id,
                    $pase_estado
                );
                break;

            case 'getRelacionPaseOficinaUsuario':
                $pase_id = $data['id'];
                echo $PaseController->getRelacionPaseOficinaUsuario($pase_id);
                break;


            
        // ----------------------
        // Usuarios Casilla
        // ----------------------
        // PENDIENTE:
        case 'add_UsuarioCasilla':

            $tipodocumento = $data['tipodocumento'];
            $numdocumento  = $data['numdocumento'];
            $nombre        = $data['nombre'];
            $apellidopat   = $data['apellidopat'];
            $apellidomat   = $data['apellidomat'];
            $razonsocial   = $data['razonsocial'];
            $celular         = $data['celular'];
            $correo          = $data['correo'];
            $password        = $data['password'];


            echo $UserCasillaController->registrarUserCasilla(
                $tipodocumento,
                $numdocumento,
                $nombre,
                $apellidopat,
                $apellidomat,
                $razonsocial,
                $celular,
                $correo,
                $password
            );
            break;
            // PENDIENTE:
        case 'activar_UsuarioCasilla':
            $id = $data['id'];
            $codigo  = $data['codigo'];


            echo $UserCasillaController->verifyUserCasilla(
                $id,
                $codigo
            );
            break;

            // ----------------------
            // solicitantes
            // ----------------------

        case 'add_administrado':
            $tipodocumento = $data['tipodocumento'];
            $tipopersona = $data['tipopersona'];
            $numdocumento = $data['numdocumento'];
            $nombre = $data['nombre'];
            $apellidopat = $data['apellidopat'];
            $apellidomat = $data['apellidomat'];
            $razonsocial = $data['razonsocial'];
            $direccion = $data['direccion'];
            $celular = $data['celular'];
            $correo = $data['correo'];
            $ubigeoid = Null;

            echo $AdministradoController->registrarAdministrado(
                $tipopersona,
                $tipodocumento,
                $numdocumento,
                $nombre,
                $apellidopat,
                $apellidomat,
                $razonsocial,
                $direccion,
                $celular,
                $correo,
                $ubigeoid
            );
            break;
        case 'upd_administrado':
            $adm_id = $data['id'];
            $tipodocumento = $data['tipodocumento'];
            $tipopersona = $data['tipopersona'];
            $numdocumento = $data['numdocumento'];
            $nombre = $data['nombre'];
            $apellidopat = $data['apellidopat'];
            $apellidomat = $data['apellidomat'];
            $razonsocial = $data['razonsocial'];
            $direccion = $data['direccion'];
            $celular = $data['celular'];
            $correo = $data['correo'];

            echo $AdministradoController->updateAdministrado(
                $adm_id,
                $tipopersona,
                $tipodocumento,
                $numdocumento,
                $nombre,
                $apellidopat,
                $apellidomat,
                $razonsocial,
                $direccion,
                $celular,
                $correo
            );
            break;
    
        case 'get_administrado':
            $adm_id = $data['id'];
            echo $AdministradoController->getAdministrado($adm_id);
            break;

        case 'get_administrado_numdoc':
            $numdocumento = $data['numdocumento'];
            echo $AdministradoController->getAdministrado_numdoc($numdocumento);
            break;
        
        case 'get_administrado_search':
            $nombre = $data['nombre'];
            $apellido = $data['apellido'];
            echo $AdministradoController->getAdministrado_busquedanombre($nombre,$apellido);
            break;

        case 'get_administrados':
            echo $AdministradoController->listarAdministrados();
            break;

        
        case 'del_administrado':
            $adm_id = $data['id'];
            echo $AdministradoController->deleteAdministrado($adm_id);
            break;
            
        // ----------------------
        // documentos
        // ----------------------

        
        case 'add_pase':
            $pase_documento_id  = $data['documento_id'];
            $pase_usr_idorigen  = $data['buzonorigen_id'];
            $pase_usr_iddestino = $data['buzondestino_id'];
            $pase_tipopase      = $data['tipopase'];
            $pase_proveido      = $data['proveido'];
            $pase_observacion   = $data['observacion'];
            $pase_estadopase    = $data['estadopase'];            
            $pase_usuario_id    = $data['usuario_id'];
            $pase_usuarionombre = $data['usuarionombre'];

            $pase_primogenio_id = $data['primogenio_id'];
            $pase_tipoaccion = $data['tipoaccion'];

            $pase_id_previo = $data['idpase_previo'];

            echo $PaseController->insertPase(
                $pase_documento_id,
                $pase_usr_idorigen,
                $pase_usr_iddestino,
                $pase_tipopase,
                $pase_proveido,
                $pase_observacion,
                $pase_estadopase,
                $pase_usuario_id,
                $pase_usuarionombre,
                $pase_id_previo,
                $pase_tipoaccion,
                $pase_primogenio_id
            );
            break;

        case 'upd_desarchivar':
            $idpase = $data['idpase'];

            echo $PaseController->desarchivar($idpase);
            break;

        case 'upd_archivar_cambiaestado':
            $idpase = $data['idpase'];
            $estado = $data['estado'];

            echo $PaseController->upd_archivar_cambiaestado($idpase,$estado);
            break;

        case 'add_documento_mesapartes':
            
                    $procedencia = "Externo";
                    $buzonorigen_id = $data['buzonorigen_id'];
                    $buzondestino_id = $data['buzondestino_id'];
                    $prioridad = $data['prioridad'];
                    $cabecera = $data['cabecera'];
                    $asunto = $data['asunto'];
                    $folios = $data['folios'];
                    $administrado_id = $data['administrado_id'];
                    $tipodocumento_id = $data['tipodocumento_id'];
                    $descripcion = $data['descripcion'];
                    $estado = 'Enviado';
                    $referencias_id = "";
                    $otrasreferencias = "";
                    $estupa = $data['estupa'];
                    $usuario_id = $data['usuario_id'];
                    $usuario_nombre = $data['usuario_nombre'];
                    
                    $proyectar = $data['proyectar'];
                    $usuarionombre = $data['usuario_nombre'];

                    //-------------------------------------
                    if($data['fechavencimiento']==""){
                        $fechavencimiento = null;
                    }else{
                        $fechavencimiento = $data['fechavencimiento'];
                    }
                    //-------------------------------------
                    if($data['pdf_principal']==""){
                        $pdf_principal = null;
                    }else{
                        $pdf_principal = $data['pdf_principal'];
                    }
                    //-------------------------------------
                    if($data['pdf_anexo1']==""){
                        $pdf_anexo1 = null;
                    }else{
                        $pdf_anexo1 = $data['pdf_anexo1'];
                    }
                    //-------------------------------------
                    if($data['pdf_anexo2']==""){
                        $pdf_anexo2 = null;
                    }else{
                        $pdf_anexo2 = $data['pdf_anexo2'];
                    }
                    //-------------------------------------
                    if ($estupa) {
                        $tramitetupa_id = $data['tramitetupa_id'];
                        $estupa = "true";
                    } else {
                        $tramitetupa_id = null;
                        $estupa = "false";
                    }

                    //-------------------------------------
                    if ($proyectar) {
                        $proyectar = "true";
                    } else {
                        $proyectar = "false";
                    }
                    
                    //-------------------------------------
                    echo $DocumentoController->registrarDocumento_Externo(
                        $procedencia,
                        $buzonorigen_id,
                        $buzondestino_id,
                        $prioridad,
                        $cabecera,
                        $asunto,
                        $folios,
                        $administrado_id,
                        $tipodocumento_id,
                        $descripcion,
                        $estado,
                        $referencias_id,
                        $otrasreferencias,
                        $estupa,
                        $fechavencimiento,
                        $proyectar,
                        $usuarionombre,
                        $tramitetupa_id,
                        $usuario_id,
                        $usuario_nombre,
                        $pdf_principal,
                        $pdf_anexo1,
                        $pdf_anexo2
                    );
                  
            break;
        
        case 'add_documento_mesapartes_virtual':
            $procedencia = "ExternoVirtual";
            $buzonorigen_id = 1;
            $buzondestino_id = $data['buzondestino_id'];
            $prioridad = $data['prioridad'];
            $cabecera = $data['cabecera'];
            $asunto = $data['asunto'];
            $folios = $data['folios'];
            $administrado_id = $data['administrado_id'];
            $tipodocumento_id = $data['tipodocumento_id'];
            $descripcion = $data['descripcion'];
            $estado = 'Enviado';
            $referencias_id = "";
            $otrasreferencias = "";
            $estupa = $data['estupa'];
            $usuario_id = $data['usuario_id'];
            $usuario_nombre = $data['usuario_nombre'];

            $token = $data['token'];

            
            if($DocumentoController->validarRecaptcha($token)){

                $proyectar = $data['proyectar'];
                $usuarionombre = $data['usuario_nombre'];
                //-------------------------------------
                if($data['fechavencimiento']==""){
                    $fechavencimiento = null;
                }else{
                    $fechavencimiento = $data['fechavencimiento'];
                }
                //-------------------------------------
                if($data['pdf_principal']==""){
                    $pdf_principal = null;
                }else{
                    $pdf_principal = $data['pdf_principal'];
                }
                //-------------------------------------
                if($data['pdf_anexo1']==""){
                    $pdf_anexo1 = null;
                }else{
                    $pdf_anexo1 = $data['pdf_anexo1'];
                }
                //-------------------------------------
                if($data['pdf_anexo2']==""){
                    $pdf_anexo2 = null;
                }else{
                    $pdf_anexo2 = $data['pdf_anexo2'];
                }
                //-------------------------------------
                if ($estupa) {
                    $tramitetupa_id = $data['tramitetupa_id'];
                    $estupa = "true";
                } else {
                    $tramitetupa_id = null;
                    $estupa = "false";
                }
                //-------------------------------------
                if ($proyectar) {
                    $proyectar = "true";
                } else {
                    $proyectar = "false";
                }

                echo $DocumentoController->registrarDocumento_Externo(
                    $procedencia,
                    $buzonorigen_id,
                    $buzondestino_id,
                    $prioridad,
                    $cabecera,
                    $asunto,
                    $folios,
                    $administrado_id,
                    $tipodocumento_id,
                    $descripcion,
                    $estado,
                    $referencias_id,
                    $otrasreferencias,
                    $estupa,
                    $fechavencimiento,
                    $proyectar,
                    $usuarionombre,
                    $tramitetupa_id,
                    $usuario_id,
                    $usuario_nombre,
                    $pdf_principal,
                    $pdf_anexo1,
                    $pdf_anexo2
                );
            }else{
                Response::error('Error al verificar el reCAPTCHA');
            }
            break;
            //-------------------------------------
            
        case 'add_documento_interno':
            $documento = $data['documentoAdd'] ?? [];
            $arrayoriginal = $data['arrayoriginal'] ?? [];
            $arraycopia = $data['arraycopia'] ?? [];

            $procedencia        = 'Interno';
            $prioridad          = $documento['prioridad'];
            $buzonorigen_id     = $documento['buzonorigen_id'];
            $cabecera           = $documento['cabecera'];
            $asunto             = $documento['asunto'];
            $folios             = $documento['folios'];
            $administrado_id    = null;
            $tipodocumento_id   = $documento['tipodocumento_id'];
            $descripcion        = $documento['descripcion'];
            $referencias_id     = $documento['referencias_id'];
            $otrasreferencias   = $documento['otrasreferencias'];
            $estupa             = $documento['estupa'];
            $fechavencimiento   = $documento['fechavencimiento'];
            $pdf_principal      = $documento['pdf_principal'];
            $pdf_anexo1         = $documento['pdf_anexo1'];
            $pdf_anexo2         = $documento['pdf_anexo2'];
            $tramitetupa_id     = $documento['tramitetupa_id'];

            $usuario_id         = $documento['usuario_id'];
            $usuario_nombre     = $documento['usuario_nombre'];

            $proyectar          = $documento['proyectar'];

            $estado             = $documento['estado'];
            $pase_proveido      = $documento['pase_proveido'];


             //-------------------------------------
             if ($proyectar) {
                $proyectar = "true";
                $estado = 'Proyectado';
            } else {
                $proyectar = "false";
            }

            if (isset($data['idpase'])) {
                $pase_idprevio = $data['idpase'];
            } else {
                $pase_idprevio = "";
            }

            if (!empty($referencias_id)) {
                $referenciasArray = explode(',', $referencias_id);
            } else {
                $referenciasArray = [];
            }

            if (!empty($otrasreferencias)) {
                $otrasreferenciasArray = explode(',', $otrasreferencias);
            } else {
                $otrasreferenciasArray = [];
            }
            
            if($fechavencimiento==""){
                $fechavencimiento = null;
            }
            
            //-------------------------------------
            if($pdf_principal==""){
                $pdf_principal = null;
            }else{
                $pdf_principal      = $documento['pdf_principal'];
                $pdf_principal_html = $documento['pdf_principal_html'];
            }
            //-------------------------------------
            if($documento['pdf_anexo1']==""){
                $pdf_anexo1 = null;
            }else{
                $pdf_anexo1 = $documento['pdf_anexo1'];
            }
            //-------------------------------------
            if($documento['pdf_anexo2']==""){
                $pdf_anexo2 = null;
            }else{
                $pdf_anexo2 = $documento['pdf_anexo2'];
            }
            
            $tramitetupa_id = null;
            $estupa = "false";


            //-------------------------------------
            echo $DocumentoController->registrarDocumento_Interno(
                $procedencia,
                $buzonorigen_id,
                $prioridad,
                $cabecera,
                $asunto,
                $folios,
                $administrado_id,
                $tipodocumento_id,
                $descripcion,
                $estado,
                $referencias_id,
                $otrasreferencias,
                $estupa,
                $fechavencimiento,
                $proyectar,
                $tramitetupa_id,
                $usuario_id,
                $usuario_nombre,
                $pdf_principal,
                $pdf_principal_html,
                $pdf_anexo1,
                $pdf_anexo2,
                $arrayoriginal,
                $arraycopia,
                $referenciasArray,
                $otrasreferenciasArray,
                $pase_idprevio,
                $pase_proveido
            );
            break;
        

        case 'upd_documento_proveido':
            $iddoc               = $data['iddoc'];
            $buzonorigen_id      = $data['buzonorigen_id'];
            $usuario_id          = $data['usuario_id'];
            $usuario_nombre      = $data['usuario_nombre'];
            $ComentarioProveido  = $data['comentario'];
            $arrayoriginal       = $data['arrayoriginal'] ?? [];

 //-------------------------------------
            echo $DocumentoController->Actualizar_Documento_proveido(
                $iddoc,
                $buzonorigen_id,
                $usuario_id,
                $usuario_nombre,
                $ComentarioProveido,
                $arrayoriginal
            );
            break;

        case 'add_documento_interno_trabajadoroficina':
            $documento          = $data['documentoAdd'] ?? [];
            $arrayoriginal      = $data['arrayoriginal'] ?? [];
            $arraycopia         = $data['arraycopia'] ?? [];

            $procedencia        = 'Interno';
            $prioridad          = $documento['prioridad'];
            $buzonorigen_id     = $documento['buzonorigen_id'];
            $cabecera           = $documento['cabecera'];
            $asunto             = $documento['asunto'];
            $folios             = $documento['folios'];
            $administrado_id    = null;
            $tipodocumento_id   = $documento['tipodocumento_id'];
            $descripcion        = $documento['descripcion'];
            $referencias_id     = $documento['referencias_id'];
            $otrasreferencias   = $documento['otrasreferencias'];
            $estupa             = $documento['estupa'];
            $fechavencimiento   = $documento['fechavencimiento'];
            $pdf_principal      = $documento['pdf_principal'];
            $pdf_anexo1         = $documento['pdf_anexo1'];
            $pdf_anexo2         = $documento['pdf_anexo2'];
            $tramitetupa_id     = $documento['tramitetupa_id'];

            $usuario_id         = $documento['usuario_id'];
            $usuario_nombre     = $documento['usuario_nombre'];

            $proyectar          = $documento['proyectar'];

            $estado             = $documento['estado'];
            $pase_proveido      = $documento['pase_proveido'];

             //-------------------------------------
             if ($proyectar) {
                $proyectar = "true";
            } else {
                $proyectar = "false";
            }

            if (isset($data['idpase'])) {
                $idpase = $data['idpase'];
            } else {
                $idpase = "";
            }

            if (!empty($referencias_id)) {
                $referenciasArray = explode(',', $referencias_id);
            } else {
                $referenciasArray = [];
            }

            if (!empty($otrasreferencias)) {
                $otrasreferenciasArray = explode(',', $otrasreferencias);
            } else {
                $otrasreferenciasArray = [];
            }
            
            if($fechavencimiento==""){
                $fechavencimiento = null;
            }
            
            //-------------------------------------
            if($pdf_principal==""){
                $pdf_principal = null;
            }else{
                $pdf_principal      = $documento['pdf_principal'];
                $pdf_principal_html = $documento['pdf_principal_html'];
            }
            //-------------------------------------
            if($documento['pdf_anexo1']==""){
                $pdf_anexo1 = null;
            }else{
                $pdf_anexo1 = $documento['pdf_anexo1'];
            }
            //-------------------------------------
            if($documento['pdf_anexo2']==""){
                $pdf_anexo2 = null;
            }else{
                $pdf_anexo2 = $documento['pdf_anexo2'];
            }
            
            $tramitetupa_id = null;
            $estupa = "false";


            //-------------------------------------
            echo $DocumentoController->registrarDocumento_Interno(
                $procedencia,
                $buzonorigen_id,
                $prioridad,
                $cabecera,
                $asunto,
                $folios,
                $administrado_id,
                $tipodocumento_id,
                $descripcion,
                $estado,
                $referencias_id,
                $otrasreferencias,
                $estupa,
                $fechavencimiento,
                $proyectar,
                $tramitetupa_id,
                $usuario_id,
                $usuario_nombre,
                $pdf_principal,
                $pdf_principal_html,
                $pdf_anexo1,
                $pdf_anexo2,
                $arrayoriginal,
                $arraycopia,
                $referenciasArray,
                $otrasreferenciasArray,
                $idpase,
                $pase_proveido
            );
            break;

        case 'upd_documento_interno':
            $documento = $data['documentoUpd'] ?? [];
            $arrayoriginal = $data['arrayoriginal'] ?? [];
            $arraycopia = $data['arraycopia'] ?? [];

            $procedencia    = 'Interno';
            $iddoc      = $documento['iddoc'];
            $prioridad      = $documento['prioridad'];
            $buzonorigen_id = $documento['buzonorigen_id'];
            $cabecera       = $documento['cabecera'];
            $asunto         = $documento['asunto'];
            $folios         = $documento['folios'];
            $administrado_id = null;
            $tipodocumento_id = $documento['tipodocumento_id'];
            $descripcion    = $documento['descripcion'];
            $referencias_id = $documento['referencias_id'];
            $otrasreferencias = $documento['otrasreferencias'];
            $estupa         = $documento['estupa'];
            $fechavencimiento = $documento['fechavencimiento'];
            $pdf_principal              = $documento['pdf_principal'];
            $pdf_principal_html         = $documento['pdf_principal_html'];
            $pdf_principal_estadofirma  = $documento['pdf_principal_estadofirma'];
            $pdf_anexo1                 = $documento['pdf_anexo1'];
            $pdf_anexo1_estadofirma     = $documento['pdf_anexo1_estadofirma'];
            $pdf_anexo2                 = $documento['pdf_anexo2'];
            $pdf_anexo2_estadofirma     = $documento['pdf_anexo2_estadofirma'];
            $tramitetupa_id             = $documento['tramitetupa_id'];

            $usuario_id                 = $documento['usuario_id'];
            $usuario_nombre             = $documento['usuario_nombre'];


            $estado                     = $documento['estado'];
            

        


            if (!empty($referencias_id)) {
                $referenciasArray = explode(',', $referencias_id);
            } else {
                $referenciasArray = [];
            }

            if (!empty($otrasreferencias)) {
                $otrasreferenciasArray = explode(',', $otrasreferencias);
            } else {
                $otrasreferenciasArray = [];
            }
            

            
            //-------------------------------------
            if($pdf_principal==""){
                $pdf_principal = null;
            }else{
                $pdf_principal = $documento['pdf_principal'];
                $pdf_principal_html = $documento['pdf_principal_html'];
            }
            //-------------------------------------
            if($documento['pdf_anexo1']==""){
                $pdf_anexo1 = null;
            }else{
                $pdf_anexo1 = $documento['pdf_anexo1'];
            }
            //-------------------------------------
            if($documento['pdf_anexo2']==""){
                $pdf_anexo2 = null;
            }else{
                $pdf_anexo2 = $documento['pdf_anexo2'];
            }
            
            $tramitetupa_id = null;
            $estupa = "false";
            $pase_proveido ="";

            //-------------------------------------
            echo $DocumentoController->Actualizar_Documento_Interno(
                $iddoc,
                $asunto,
                $folios,
                $pdf_principal,
                $pdf_principal_html,
                $pdf_principal_estadofirma,
                $pdf_anexo1,
                $pdf_anexo1_estadofirma,
                $pdf_anexo2,
                $pdf_anexo2_estadofirma,
                $buzonorigen_id,
                $usuario_id,
                $usuario_nombre,
                $arrayoriginal,
                $arraycopia,
                $referenciasArray,
                $otrasreferenciasArray,
                $pase_proveido
            );
            break;
        // --------------------

        case 'upd_documento_interno_TrabajadorCAS':
            $documento = $data['documentoUpd'] ?? [];
            $arrayoriginal = $data['arrayoriginal'] ?? [];
            $arraycopia = $data['arraycopia'] ?? [];

            $procedencia    = 'Interno';
            $iddoc      = $documento['iddoc'];
            $prioridad      = $documento['prioridad'];
            $buzonorigen_id = $documento['buzonorigen_id'];
            $cabecera       = $documento['cabecera'];
            $asunto         = $documento['asunto'];
            $folios         = $documento['folios'];
            $administrado_id = null;
            $tipodocumento_id = $documento['tipodocumento_id'];
            $descripcion    = $documento['descripcion'];
            $referencias_id = $documento['referencias_id'];
            $otrasreferencias = $documento['otrasreferencias'];
            $estupa         = $documento['estupa'];
            $fechavencimiento = $documento['fechavencimiento'];
            $pdf_principal              = $documento['pdf_principal'];
            $pdf_principal_html = isset($documento['pdf_principal_html']) ? $documento['pdf_principal_html'] : null;
            $pdf_principal_estadofirma  = $documento['pdf_principal_estadofirma'];
            $pdf_anexo1                 = $documento['pdf_anexo1'];
            $pdf_anexo1_estadofirma     = $documento['pdf_anexo1_estadofirma'];
            $pdf_anexo2                 = $documento['pdf_anexo2'];
            $pdf_anexo2_estadofirma     = $documento['pdf_anexo2_estadofirma'];
            $tramitetupa_id             = $documento['tramitetupa_id'];

            $usuario_id                 = $documento['usuario_id'];
            $usuario_nombre             = $documento['usuario_nombre'];
            $pase_proveido              = $data['proveido'];

            $estado                     = $documento['estado'];
            

        


            if (!empty($referencias_id)) {
                $referenciasArray = explode(',', $referencias_id);
            } else {
                $referenciasArray = [];
            }

            if (!empty($otrasreferencias)) {
                $otrasreferenciasArray = explode(',', $otrasreferencias);
            } else {
                $otrasreferenciasArray = [];
            }
            

            
            //-------------------------------------
            if($pdf_principal==""){
                $pdf_principal = null;
            }else{
                $pdf_principal = $documento['pdf_principal'];
                $pdf_principal_html = isset($documento['pdf_principal_html']) ? $documento['pdf_principal_html'] : null;
                // $pdf_principal_html =  $documento['pdf_principal_html'];
            }
            //-------------------------------------
            if($documento['pdf_anexo1']==""){
                $pdf_anexo1 = null;
            }else{
                $pdf_anexo1 = $documento['pdf_anexo1'];
            }
            //-------------------------------------
            if($documento['pdf_anexo2']==""){
                $pdf_anexo2 = null;
            }else{
                $pdf_anexo2 = $documento['pdf_anexo2'];
            }
            
            $tramitetupa_id = null;
            $estupa = "false";


            //-------------------------------------
            echo $DocumentoController->Actualizar_Documento_Correccion_Trabajador(
                $iddoc,
                $asunto,
                $folios,
                $pdf_principal,
                $pdf_principal_html,
                $pdf_principal_estadofirma,
                $pdf_anexo1,
                $pdf_anexo1_estadofirma,
                $pdf_anexo2,
                $pdf_anexo2_estadofirma,
                $buzonorigen_id,
                $usuario_id,
                $usuario_nombre,
                $arrayoriginal,
                $arraycopia,
                $referenciasArray,
                $otrasreferenciasArray,
                $pase_proveido
            );
            break;
        // --------------------
        
        case 'upd_file_documentos_firmado':
           
            $pdf_url        = $data['pdf_url'];
            $tipo           = $data['pdf_tipo'];
            $iddoc          = $data['doc_id'];
            //----------------------------------------------------------
            echo $DocumentoController->Actualizar_archivos(
                $pdf_url,
                $tipo,
                $iddoc
            );
            //----------------------------------------------------------
            break;
        
             // --------------------
        case 'upd_estado_envio_documento_firmado':
           
            $iddoc          = $data['doc_id'];
            //----------------------------------------------------------
            echo $DocumentoController->Confirmar_EnviodocumentoFirmado(
                $iddoc
            );
            //----------------------------------------------------------
            break;
            // --------------------
        case 'upd_Enviado_firmado_manualmente':
           
            $iddoc          = $data['doc_id'];
            //----------------------------------------------------------
            echo $DocumentoController->Enviar_FirmadoManualmente(
                $iddoc
            );
            //----------------------------------------------------------
            break;
        case 'Confirmar_Proveido':
            $iddoc          = $data['doc_id'];
            //----------------------------------------------------------
            echo $DocumentoController->Confirmar_Proveido(
                $iddoc
            );
            //----------------------------------------------------------
            break;
        case 'get_documento_referenciado':
            
            $tipodocumento_id = $data['tipodocumento_id'];
            $numerodocumento  = $data['numerodocumento'];
            $anio             = $data['anio'];
            $buzonorigen_id     = $data['buzonorigen_id'];

            echo $DocumentoController->obtener_referencias($tipodocumento_id, $numerodocumento, $anio, $buzonorigen_id);
            break;

        case 'buscar_documentos_externos_personas':
            $administrado_nombre        = isset($data['nombre']) ? $data['nombre'] : null;
            $administrado_apellidopat   = isset($data['apellidopat']) ? $data['apellidopat'] : null;
            $administrado_apellidomat   = isset($data['apellidomat']) ? $data['apellidomat'] : null;
            $administrado_tipodocumento = isset($data['tipodocumento']) ? $data['tipodocumento'] : null;
            $administrado_numdocumento  = isset($data['numdocumento']) ? $data['numdocumento'] : null;
            $dia                        = isset($data['dia']) ? $data['dia'] : null;
            $mes                        = isset($data['mes']) ? $data['mes'] : null;
            $anio                       = isset($data['anio']) ? $data['anio'] : null;

            echo $DocumentoController->buscar_documentos_externos_persona(
                $administrado_nombre,
                $administrado_apellidopat,
                $administrado_apellidomat,
                $administrado_tipodocumento,
                $administrado_numdocumento,
                $dia,
                $mes,
                $anio
            );
            break;

        case 'buscar_documentos_internos':
            $tipodocumento_nombre = isset($data['tipodocumento_nombre']) ? $data['tipodocumento_nombre'] : null;
            $buzonorigen_nombre   = isset($data['buzonorigen_nombre']) ? $data['buzonorigen_nombre'] : null;
            $dia                  = isset($data['dia']) ? $data['dia'] : null;
            $mes                  = isset($data['mes']) ? $data['mes'] : null;
            $anio                 = isset($data['anio']) ? $data['anio'] : null;

            echo $DocumentoController->buscar_documentos_internos_opcional(
                $tipodocumento_nombre,
                $buzonorigen_nombre,
                $dia,
                $mes,
                $anio
            );
            break;

        case 'get_lista_documento_Interno_buscar':
             $nrodocumento = isset($data['nrodocumento']) ? $data['nrodocumento'] : null;
           $anio = isset($data['anio']) ? $data['anio'] : null;
            echo $DocumentoController->get_lista_documento_Interno_buscar($anio,$nrodocumento);
            break;
            

        case 'genera_numeracion_tipodoc_oficina':
        
            $procedencia            = $data['procedencia'];
            $tipodocumento_id       = $data['tipodocumento_id'];
            $user_buzonorigen_id    = $data['user_buzonorigen_id'];

            echo $DocumentoController->genera_numeracion_tipodoc_oficina_fn($procedencia, $tipodocumento_id, $user_buzonorigen_id);
            break;
        
        
            case 'genera_numeracion_tipodoc_oficina_principal':
        
            $procedencia            = $data['procedencia'];
            $tipodocumento_id       = $data['tipodocumento_id'];
            $user_buzon_oficina_id  = $data['user_buzon_oficina_id'];

            echo $DocumentoController->genera_numeracion_tipodoc_oficina_fn_general($procedencia, $tipodocumento_id, $user_buzon_oficina_id);
            break;
            
        // BUZON DE CASILLA
        case 'get_listadocumentos_administrado_documento':
            $numdocumento            = $data['numdocumento'];
            echo $DocumentoController->listarDocumentos_externo_x_nro_documento($numdocumento);
            break;

      
        // MESA DE PARTES DOCUMENTOS



        case 'generarLibroCargosPDF':
            $desde = $data['desde'];
            $hasta = $data['hasta'];
            echo $DocumentoController->generarLibroCargosPDF($desde,$hasta);
            break;
        case 'get_listadocumentos_mesapartes_iniciado':
            echo $DocumentoController->listarDocumentos_mesapartes_iniciado();
            break;

        case 'get_listadocumentos_mesapartes_enviados':
            echo $DocumentoController->listarDocumentos_mesapartes_enviados();
            break;

        case 'listar_documentos_mesa_partes_enviados_paginado':
            $pagina             = $data['pagina'] ?? 1;
            $registrosPorPagina = $data['registros_por_pagina'] ?? 25;
            $busqueda           = $data['busqueda'] ?? '';
            $campoBusqueda      = $data['campo_busqueda'] ?? '';
            $ordenarPor         = $data['ordenar_por'] ?? 'pase_fechaenvio';
            $direccionOrden     = $data['direccion_orden'] ?? 'DESC';
            
            echo $DocumentoController->listarDocumentos_mesapartes_enviados_paginado(
                $pagina, $registrosPorPagina, $busqueda, $campoBusqueda, $ordenarPor, $direccionOrden
            );
            break;

        // case 'listarDocumentos_interno_iniciado':
        //     echo $DocumentoController->listarDocumentos_interno_iniciado();
        //     break;
        

        case 'get_listadocumentos_usuarioDestino_Estado':
            $estado = $data['pase_estado'];
            $user_buzondestino_id = $data['user_buzondestino_id'];
            echo $DocumentoController->listarDocumentos_deOficina($estado, $user_buzondestino_id);
            break;    

        case 'get_listadocumentos_EnviadosOficina':
            $user_buzonorigen_id = $data['user_buzonorigen_id'];
            echo $DocumentoController->listarDocumentos_Enviados_Oficina($user_buzonorigen_id);
            break;    

        case 'get_listadocumentos_Archivados':
            $user_buzonorigen_id = $data['user_buzonorigen_id'];
            echo $DocumentoController->listarDocumentos_Archivados_Oficina('Archivo', $user_buzonorigen_id, $user_buzonorigen_id);
            break;   
        
        case 'listarDocumentosEmitidos_por_usuario_anio':
            $user_buzonorigen_id = $data['user_buzonorigen_id'];
            $anio = $data['anio'];
            echo $DocumentoController->listarDocumentosEmitidos_por_usuario_anio($user_buzonorigen_id, $anio);
            break;
        case 'listarDocumentos_proyectados_usuario_anio':
            $user_buzonorigen_id = $data['user_buzonorigen_id'];
            echo $DocumentoController->listarDocumentos_Proyectados_Oficina($user_buzonorigen_id);
            break;
         case 'listarDocumentos_proyectados_nombreusuario':
            $nombre_usuario = $data['nombre_usuario'];
            echo $DocumentoController->listarDocumentos_Proyectados_NombreUsuario($nombre_usuario);
            break;
        case 'listarDocumentos_iniciados_usuario_anio':
            $user_buzonorigen_id = $data['user_buzonorigen_id'];
            echo $DocumentoController->listarDocumentos_Iniciados_Oficina($user_buzonorigen_id);
            break;
    
        case 'listarDocumentos_observados_usuario_anio':
            $user_buzonorigen_id = $data['user_buzonorigen_id'];
            echo $DocumentoController->listarDocumentos_Obsevados_Oficina($user_buzonorigen_id);
            break;
        case 'listarDocumentos_observados_nombreusuario':
            $nombre_usuario = $data['nombre_usuario'];
            echo $DocumentoController->listarDocumentos_Obsevados_NombreUsuario($nombre_usuario);
            break;
        //STAT -----------------------
        case 'Obtener_stat_documentos':
            $buzon_id = $data['buzon_id'];
            echo $DocumentoController->Obtener_stat_documentos($buzon_id);
            break;    

         case 'Obtener_stat_documentos_todos':
            echo $DocumentoController->Obtener_stat_documentos_todos();
            break;    

            
        case 'get_documentoCompleto':
            $id = $data['id'];
            echo $DocumentoController->get_lista_documento_Interno($id);
            break;
            
        case 'get_documentoCompleto_externo':
            $id = $data['id'];
            echo $DocumentoController->get_documento_externo($id);
            break;

        case 'get_documentoCompleto_interno':
            $id = $data['id'];
            echo $DocumentoController->get_documento_interno($id);
            break;
    

        case 'get_documentoCompleto_by_nroanio':
            $nrodocumento = $data['nrodocumento'];
            $anio = $data['anio'];
            $codigo = $data['codigo'];
            echo $DocumentoController->get_lista_documento_Interno_anionro($anio,$nrodocumento, $codigo);
            break;
            
        case 'Listar_doc_relacionados':
            $iddocumento = $data['iddocumento'];
            echo $DocumentoController->Documentos_referenciados($iddocumento);
            break;
        
        case 'Listar_ruta_pases':
            $iddocumento = $data['iddocumento'];
            echo $DocumentoController->Listar_trazabilidad($iddocumento);
            break;

        //Buzón de Salida 

        case 'Get_TipoDocumentos_Generados':
            $buzonOrigen_id = $data['buzonorigen_id'];
            echo $DocumentoController->Get_TipoDocumentos_Generados($buzonOrigen_id);
            break;

        case 'Get_estadistico_portipopases':
            $buzonOrigen_id = $data['buzonorigen_id'];
            echo $DocumentoController->Get_estadistico_portipopases($buzonOrigen_id);
            break;

        case 'Get_ListaDocumentos_Generados_x_tipo':
            $buzonOrigen_id = $data['buzonorigen_id'];
            $tipodoc        = $data['tipodocumento_id'];
            echo $DocumentoController->Get_ListaDocumentos_Generados_x_tipo($buzonOrigen_id, $tipodoc);
            break;
        
        
        // -----------------------------------
        case 'Aceptar_documento_proyectado':
            $id_doc             = $data['iddocumento'];
            echo $DocumentoController->Aceptar_documento_proyectado($id_doc);
            break;
            
            // -----------------------------------
        case 'Enviar_a_proyectado':
            $id_doc             = $data['iddocumento'];
            echo $DocumentoController->Enviar_a_proyectado($id_doc);
            break;
            
        // -----------------------------------
        case 'Observar_documento':
            $id_doc             = $data['iddocumento'];
            $motivo             = $data['motivo'];
            echo $DocumentoController->Observar_documento($id_doc, $motivo);

            break;
        // -----------------------------------
        case 'ConfirmarEnvio_documento_proyectado':
            $id_doc            = $data['iddocumento'];
            echo $DocumentoController->ConfirmarEnvio_documento_proyectado($id_doc);
            break;

        // -----------------------------------
        case 'upd_documento_mesapartes':
            $id = $data['iddoc'];
            $buzonorigen_id   = $data['buzonorigen_id'];
            $buzondestino_id  = $data['buzondestino_id'];
            $prioridad        = $data['prioridad'];
            $cabecera         = $data['cabecera'];
            $asunto           = $data['asunto'];
            $folios           = $data['folios'];
            $administrado_id  = $data['administrado_id'];
            $tipodocumento_id = $data['tipodocumento_id'];
            $descripcion      = $data['descripcion'];
            $estupa           = $data['estupa'];
            $pdf_principal    = $data['pdf_principal'];
            $pase_id          = $data['pase_id'];
            $tramitetupa_id   = $data['tramitetupa_id'];

            $proyectar        = $data['proyectar'];
            $usuarionombre    = $data['usuarionombre'];

            if ($proyectar) {
                $proyectar    = "true";
            } else {
                $proyectar    = "false";
            }

            if ($estupa) {
                $tramitetupa_id = $data['tramitetupa_id'];
                $estupa = "true";
            } else {
                $tramitetupa_id = null;
                $estupa = "false";
            }
            if($data['fechavencimiento']==""){
                $fechavencimiento = null;
            }else{
                $fechavencimiento = $data['fechavencimiento'];
            }

            echo $DocumentoController->updateDocumento(
                $id, 
                $buzonorigen_id, 
                $prioridad,
                $cabecera,
                $asunto, 
                $folios, 
                $administrado_id, 
                $tipodocumento_id, 
                $descripcion, 
                $estupa, 
                $pdf_principal,
                $pase_id,
                $buzondestino_id,
                $fechavencimiento,
                $proyectar,
                $usuarionombre,
                $tramitetupa_id
            );
            break;

        case 'get_oficinas':
            echo $OficinaController->getOficinas();
            break;

        case 'getOficinasArbol':
            echo $OficinaController->getOficinasArbol();
            break;

                
        case 'get_oficina':
            $id = $data['id'];
            echo $OficinaController->getOficina($id);
            break;

        case 'add_oficina':
            $nombre = $data['nombre'];
            $padre_id = isset($data['padre_id']) ? $data['padre_id'] : null;
            echo $OficinaController->insertOficina($nombre, $padre_id);
            break;

        case 'update_oficina':
            $id = $data['id'];
            $nombre = $data['nombre'];
            $padre_id = isset($data['padre_id']) ? $data['padre_id'] : null;
            echo $OficinaController->updateOficina($id, $nombre, $padre_id);
            break;

        case 'delete_oficina':
            $id = $data['id'];
            echo $OficinaController->deleteOficina($id);
            break;



            // ----------------------
            // licenciasASD
            // ----------------------
        
            case 'add_licencia_nuevo':
                // Asignar los parámetros desde el objeto $data
                $tipotramite_tupa = $data['tipotramite_tupa'];
                $negocio_ruc = $data['negocio_ruc'];
                $negocio_razonsocial = $data['negocio_razonsocial'];
                $negocio_direccionfiscal = $data['negocio_direccionfiscal'];
                $negocio_nombrecomercial = $data['negocio_nombrecomercial'];
                $negocio_actividadcomercial = $data['negocio_actividadcomercial'];
                $negocio_condicionlocal = $data['negocio_condicionlocal'];   // Respetando la key con "C" mayúscula
                $representantelegal_dni = $data['representantelegal_dni'];
                $representantelegal_nombre = $data['representantelegal_nombre'];
                $negocio_area = $data['negocio_area'];
                $negocio_aforo = $data['negocio_aforo'];
                $negocio_horario = $data['negocio_horario'];
                $pago_monto = $data['pago_monto'];
                $pago_codoperacion = $data['pago_codoperacion'];   // Corrigiendo la key (debería ser pago_coperacion)
                $pago_pagovoucher_url = $data['pago_pagovoucher_url']; // Asignando el URL del voucher de pago
                $dir_direccioncomercial = $data['dir_direccioncomercial'];
                $dir_numero = $data['dir_numero'];
                $dir_letra = $data['dir_letra'];
                $dir_inter = $data['dir_inter'];
                $dir_mz = $data['dir_mz'];
                $dir_lote = $data['dir_lote'];
                $dir_dpto = $data['dir_dpto'];
                $dir_referencia = $data['dir_referencia'];
                $itse_tipoinspeccion = $data['itse_tipoinspeccion'];
                $itse_resultado = $data['itse_resultado'];   // Corrigiendo la key (debería ser resultado_itse)
                $itse_riesgo = $data['itse_riesgo'];          // Corrigiendo la key (debería ser riesgo_itse)
                $itse_observacion = $data['itse_observacion'];
                $procedencia_solicitud = "Interno";
                $usuarioid = $data['usuarioid'];
                $usuarionombre = $data['usuarionombre'];
                $ubigeoid = $data['ubigeoid'];
                $estadotramite = "EN PROCESO";
                $documento_id = 1;
                $ubigeoid = 200115;
                $documento_numexpediente=$data['documento_codexpediente'];
                $fechaingreso = $data['fecharegistro'];
                $epoca = $data['epocatramite'];
    
                // Llamar al controlador con los parámetros adecuados para insertar un nuevo registro
                echo $LicenciaController->insertar_nuevalicencia(
                    $tipotramite_tupa,
                    $negocio_ruc,
                    $negocio_razonsocial,
                    $negocio_direccionfiscal,
                    $negocio_nombrecomercial,
                    $negocio_actividadcomercial,
                    $negocio_condicionlocal,
                    $representantelegal_dni,
                    $representantelegal_nombre,
                    $negocio_area,
                    $negocio_aforo,
                    $negocio_horario,
                    $pago_monto,
                    $pago_codoperacion,
                    $pago_pagovoucher_url,
                    $dir_direccioncomercial,
                    $dir_numero,
                    $dir_letra,
                    $dir_inter,
                    $dir_mz,
                    $dir_lote,
                    $dir_dpto,
                    $dir_referencia,
                    $itse_tipoinspeccion,
                    $itse_resultado,
                    $itse_riesgo,
                    $itse_observacion,
                    $procedencia_solicitud,
                    $usuarioid,
                    $usuarionombre,
                    $ubigeoid,
                    $estadotramite,
                    $documento_numexpediente,
                    $documento_id,
                    $fechaingreso,
                    $epoca
                );
                break;


            // PENDIENTE:
        case 'get_licencia':
            $id = $data['idlic'];
            echo $LicenciaController->getLicencia($id);
            break;

        case 'get_correlativo':

            echo $LicenciaController->obtener_correlativo();
           
            break;

            // PENDIENTE:
        case 'get_lista_licencias':
            echo $LicenciaController->getLicencias();
            break;

            // PENDIENTE:

        case 'get_tabla_licencias':

            $estado      = isset($data['f_estado']) ? $data['f_estado'] : null;
            $mes         = isset($data['f_mes_registro']) ? $data['f_mes_registro'] : null;
            $anio        = isset($data['f_anio_registro']) ? $data['f_anio_registro'] : null;
            $fechainicio = isset($data['f_fecha_inicio']) ? $data['f_fecha_inicio'] : null;
            $fechafin    = isset($data['f_fecha_fin']) ? $data['f_fecha_fin'] : null;

            echo $LicenciaController->getLicencias_filtros_validado_full($estado, $mes, $anio, $fechainicio, $fechafin);

            break;

        case 'get_tabla_licencias_full':

            $estado      = isset($data['f_estado']) ? $data['f_estado'] : null;
            $mes         = isset($data['f_mes_registro']) ? $data['f_mes_registro'] : null;
            $anio        = isset($data['f_anio_registro']) ? $data['f_anio_registro'] : null;
            $fechainicio = isset($data['f_fecha_inicio']) ? $data['f_fecha_inicio'] : null;
            $fechafin    = isset($data['f_fecha_fin']) ? $data['f_fecha_fin'] : null;

            echo $LicenciaController->getLicencias_filtros_validado_full($estado, $mes, $anio, $fechainicio, $fechafin);

            break;
        
        case 'get_tabla_licencias_emtidas':

            $categoria   = isset($data['f_categoria']) ? $data['f_categoria'] : null;
            $estado      = isset($data['f_estado']) ? $data['f_estado'] : null;
            $mes         = isset($data['f_mes_registro']) ? $data['f_mes_registro'] : null;
            $anio        = isset($data['f_anio_registro']) ? $data['f_anio_registro'] : null;
            $fechainicio = isset($data['f_fecha_inicio']) ? $data['f_fecha_inicio'] : null;
            $fechafin    = isset($data['f_fecha_fin']) ? $data['f_fecha_fin'] : null;
            $codigo           = isset($data['f_codigo']) ? $data['f_codigo'] : null;
            $numero_documento = isset($data['f_numero_documento']) ? $data['f_numero_documento'] : null;
            $direccion        = isset($data['f_direccion']) ? $data['f_direccion'] : null;

            echo $LicenciaController->getLicencias_filtros_licenciasEmitidas($estado, $mes, $anio, $fechainicio, $fechafin, $categoria, $codigo, $numero_documento, $direccion);

            break;
            
        case 'get_tabla_bajas':

            $mes         = isset($data['f_mes_registro']) ? $data['f_mes_registro'] : null;
            $anio        = isset($data['f_anio_registro']) ? $data['f_anio_registro'] : null;
            $fechainicio = isset($data['f_fecha_inicio']) ? $data['f_fecha_inicio'] : null;
            $fechafin    = isset($data['f_fecha_fin']) ? $data['f_fecha_fin'] : null;

            echo $LicenciaController->getLicencias_filtros_bajanula($mes, $anio, $fechainicio, $fechafin);

            break;
            // PENDIENTE:
        case 'get_lista_licenciasanio':
            $fechainicio = $data['fechainicio'];
            $fechafin = $data['fechainicio'];
            // echo $LicenciaController->getLicencias(null, null, '2023-01-01', '2023-12-31');
            break;

            // PENDIENTE:
        case 'upd_licencia':
            $idlic = $data['idlic'];
            $tipotramite_tupa = $data['tipotramite_tupa'];
            $negocio_ruc = $data['negocio_ruc'];
            $negocio_razonsocial = $data['negocio_razonsocial'];
            $negocio_direccionfiscal = $data['negocio_direccionfiscal'];
            $negocio_nombrecomercial = $data['negocio_nombrecomercial'];
            $negocio_actividadcomercial = $data['negocio_actividadcomercial'];
            $negocio_condicionlocal = $data['negocio_condicionlocal'];   // Respetando la key con "C" mayúscula
            $representantelegal_dni = $data['representantelegal_dni'];
            $representantelegal_nombre = $data['representantelegal_nombre'];
            $negocio_area = $data['negocio_area'];
            $negocio_aforo = $data['negocio_aforo'];
            $negocio_horario = $data['negocio_horario'];
            $pago_monto = $data['pago_monto'];
            $pago_codoperacion = $data['pago_codoperacion'];   // Corrigiendo la key (debería ser pago_coperacion)
            $dir_direccioncomercial = $data['dir_direccioncomercial'];
            $dir_numero = $data['dir_numero'];
            $dir_letra = $data['dir_letra'];
            $dir_inter = $data['dir_inter'];
            $dir_mz = $data['dir_mz'];
            $dir_lote = $data['dir_lote'];
            $dir_dpto = $data['dir_dpto'];
            $dir_referencia = $data['dir_referencia'];
            $itse_tipoinspeccion = $data['itse_tipoinspeccion'];
            $itse_resultado = $data['itse_resultado'];   // Corrigiendo la key (debería ser resultado_itse)
            $itse_riesgo = $data['itse_riesgo'];          // Corrigiendo la key (debería ser riesgo_itse)
            $itse_observacion = $data['itse_observacion'];
            $procedencia_solicitud = "Interno";
            $usuarioid = $data['usuarioid'];
            $usuarionombre = $data['usuarionombre'];
            $ubigeoid = $data['ubigeoid'];
            $documento_codexpediente = $data['documento_codexpediente'];

            echo $LicenciaController->updateLicencia(
                $idlic,
                $tipotramite_tupa,
                $negocio_ruc,
                $negocio_razonsocial,
                $negocio_direccionfiscal,
                $negocio_nombrecomercial,
                $negocio_actividadcomercial,
                $negocio_condicionlocal,
                $representantelegal_dni,
                $representantelegal_nombre,
                $negocio_area,
                $negocio_aforo,
                $negocio_horario,
                $pago_monto,
                $pago_codoperacion,
                $dir_direccioncomercial,
                $dir_numero,
                $dir_letra,
                $dir_inter,
                $dir_mz,
                $dir_lote,
                $dir_dpto,
                $dir_referencia,
                $itse_tipoinspeccion,
                $itse_resultado,
                $itse_riesgo,
                $itse_observacion,
                $documento_codexpediente
            );
            break;
            
        case 'aceptar_solicitud_licencia':
            $idlic = $data['idlic'];
            echo $LicenciaController->aceptarLicenciaSolicitud($idlic);
            break;

        case 'rechazar_solicitud_licencia':
            $idlic = $data['idlic'];
            $vigencia_observacion = $data['vigencia_observacion'];
            echo $LicenciaController->rechazarLicenciaSolicitud($idlic, $vigencia_observacion);
            break;
        
        case 'bajanulidad_licencia':
            $idlic = $data['idlic'];
            $estado = $data['operacion'];
            $vigencia_observacion = $data['vigencia_observacion'];
            echo $LicenciaController->DarBajaLicencia($idlic, $estado, $vigencia_observacion);
            break;
    
        case 'cambiar_estado_licencia':
            $idlic = $data['idlic'];
            $vigencia_estado = $data['vigencia_estado'];
            $vigencia_observacion = $data['vigencia_observacion'];
            echo $LicenciaController->cambiarEstadoLicencia($idlic, $vigencia_estado, $vigencia_observacion);
            break;

        case 'guardar_zonificacion':
            $idlic = $data['idlic'];
            $zonificacion = $data['zonificacion'];
            echo $LicenciaController->guardar_zonificacion($idlic, $zonificacion);
            break;

        case 'guardar_Vigencia':
            $idlic = $data['idlic'];
            $vigencia = $data['vigencia'];
            echo $LicenciaController->guardar_Vigencia($idlic, $vigencia);
            break;

        case 'guardar_itsefechavencimiento':
            $idlic = $data['idlic'];
            $fechavencimiento = $data['fechavencimiento'];
            echo $LicenciaController->guardar_fechavencimiento($idlic, $fechavencimiento);
            break; 

            // PENDIENTE:            
        case 'delete_licencia':
            $idlic = $data['idlic'];
            
            echo $LicenciaController->deleteLicencia($idlic);
            break;
            

            // PENDIENTE:            
        case 'save_LicenciaGenerada':
            $idlic          = $data['idlic'];
            $sequiencia     = $data['sequiencia'];
            $codlicencia    = $data['codlicencia'];  
            $numresolucion  = $data['numresolucion'];
            $codresolucion  = $data['codresolucion'];
            $numautorizacion  = $data['numautorizacion'];
            $codautorizacion  = $data['codautorizacion'];

            $categoriatramite  = $data['categoriatramite'];
            

            $qrverificacion = $data['qrverificacion'];
            $tipo_duracion  = $data['tipo_duracion'];

            //------------------------------------------------------
            if (isset($_FILES['file_documentoLicencia'])) {
                $file_pdf = $_FILES['file_documentoLicencia'];
            } else {
                $file_pdf = "";
            }
            if($categoriatramite=="Autorización"){
                echo $LicenciaController->SubirAutorizacionGenerada($idlic, $file_pdf, $numautorizacion, $codautorizacion, $numresolucion, $codresolucion, $qrverificacion, $tipo_duracion);

            }else{
                echo $LicenciaController->SubirLicenciaGenerada($idlic, $file_pdf, $sequiencia, $codlicencia, $numresolucion, $codresolucion, $qrverificacion, $tipo_duracion);

            }

            break;

        case 'save_licenciaAdjunta':
            $idlic           = $data['idlic'];
            $sequiencia      = $data['sequiencia'];
            $codlicencia     = $data['codlicencia'];
            $fecha           = $data['fechaemision'];
            $qrverificacion  = "";
            //------------------------------------------------------
            if (isset($_FILES['file_documentoLicencia'])) {
                $file_pdf = $_FILES['file_documentoLicencia'];
            } else {
                $file_pdf = "";
            }
            echo $LicenciaController->SubirLicenciaAdjunta($idlic, $file_pdf, $sequiencia, $codlicencia, $qrverificacion, $fecha);
            break;
            
        case 'save_documentoResolucionAjunta':
            $idlic = $data['idlic'];
            $numero_resolucion  = $data['numero_resolucion'];

            if (isset($_FILES['file_resolucionPDF'])) {
                $file_pdf = $_FILES['file_resolucionPDF'];
            } else {
                $file_pdf = "";
            }
            echo $LicenciaController->SubirResolucionAdjunta($idlic, $file_pdf,$numero_resolucion);
            break;


            // PENDIENTE:            
        case 'generarResolucion':
            $idlic = $data['idlic'];
            echo $LicenciaController->generarResolucion($idlic);
            break;

            // ----------------------
            // Tipos de DOCUMENTOS 
            // ----------------------
        case 'get_lista_tiposdocumento':
            echo $TipoDocumentoController->get_documentos();
            break;

            // ----------------------
            // Tipos de trámite
            // ----------------------

            // PENDIENTE:        
        case 'add_tramite':
            $nombreTramite = $data['nombretramite'];
            $descripcion = $data['descripcion'];
            $codigo = $data['codigo'];
            $tipomonto = $data['tipomonto'];  // Nuevo campo tipomonto
            $monto = $data['monto'];
            $plazo = $data['plazo'];
            $duracion = $data['duracion'];
            $comentario = $data['comentario'];  // Nuevo campo comentario
            $requisito = $data['requisito'];    // Nuevo campo requisito
            $oficina_id = $data['oficina_id'];  // Nuevo campo oficina_id
            $categoria = $data['categoria'];  // Nuevo campo categoria
            echo $TramiteController->insertTramite(
                $nombreTramite,
                $descripcion,
                $codigo,
                $tipomonto,
                $monto,
                $plazo,
                $duracion,
                $comentario,
                $requisito,
                2,
                $categoria
            );
            break;

            // PENDIENTE:
        case 'get_tramite':
            $tram_id = $data['tram_id']; // Cambio para reflejar el campo 'tram_id' en lugar de 'idtra'
            echo $TramiteController->getTramite($tram_id);
            break;

            // PENDIENTE:            
        case 'get_lista_tramites':
            echo $TramiteController->getTramites();
            break;
        case 'get_lista_tramites_oficina':
            $idoficina = $data['idoficina'];
            echo $TramiteController->getTramites($idoficina);
            break;

            // PENDIENTE:            
        case 'upd_tramite':
            $tram_id = $data['id']; // Cambio para reflejar el campo 'tram_id'
            $nombreTramite = $data['nombretramite'];
            $descripcion = $data['descripcion'];
            $codigo = $data['codigo'];
            $tipomonto = $data['tipomonto'];  // Nuevo campo tipomonto
            $monto = $data['monto'];
            $plazo = $data['plazo'];
            $duracion = $data['duracion'];
            $comentario = $data['comentario'];  // Nuevo campo comentario
            $requisito = $data['requisito'];    // Nuevo campo requisito
            $oficina_id = $data['oficina_id'];  // Nuevo campo oficina_id
            $categoria = $data['categoria']; 
            echo $TramiteController->updateTramite(
                $tram_id,
                $nombreTramite,
                $descripcion,
                $codigo,
                $tipomonto,
                $monto,
                $plazo,
                $duracion,
                $comentario,
                $requisito,
                $oficina_id,
                $categoria
            );
            break;

            // PENDIENTE:
        case 'del_tramite':
            $tram_id = $data['id']; // Cambio para reflejar el campo 'tram_id'
            echo $TramiteController->deleteTramite($tram_id);
            break;

            // ----------------------
            // Requisitos
            // ----------------------
            // PENDIENTE:        
        case 'add_requisito':
            $reqnombre = $data['reqnombre'];
            $esobligatorio = $data['esobligatorio'];
            $esformato = $data['esformato'];

            if (isset($_FILES['archivopdf'])) {
                $archivopdf_file = $_FILES['archivopdf'];
                $formatoPDF = $RequisitoController->subir_archivo_pdf($archivopdf_file);
            } else {
                $formatoPDF = "";
            }
            echo $RequisitoController->insertRequisito($reqnombre, $esobligatorio, $esformato, $formatoPDF);
            break;

            // PENDIENTE:            
        case 'get_requisito':
            $idreq = $data['idreq'];
            echo $RequisitoController->getRequisito($idreq);
            break;

            // PENDIENTE:            
        case 'get_lista_requisitos':
            echo $RequisitoController->getRequisitos();
            break;

            // PENDIENTE:            
        case 'upd_requisito':
            $idreq = $data['idreq'];
            $reqnombre = $data['reqnombre'];
            $esobligatorio = $data['esobligatorio'];
            $esformato = $data['esformato'];
            $formatoPDF = $data['formatopdf'];
            echo $RequisitoController->updateRequisito($idreq, $reqnombre, $esobligatorio, $esformato, $formatoPDF);
            break;

            // PENDIENTE:            
        case 'del_requisito':
            $idreq = $data['idreq'];
            echo $RequisitoController->deleteRequisito($idreq);
            break;

            // ----------------------
            // ASIGNAR REQUISITOS
            // ----------------------

            // PENDIENTE:        
        case 'add_RequisitosAsignados':
            $idrequisito = $data['idrequisito'];
            $idtramite = $data['idtramite'];

            echo $RequisitoController->add_Requisito_tramite($idrequisito, $idtramite);
            break;

            // PENDIENTE:            
        case 'del_RequisitosAsignados':
            $idasignacion = $data['idreq'];
            echo $RequisitoController->delete_Requisito_tramite($idasignacion);
            break;

            // PENDIENTE:            
        case 'get_lista_RequisitosAsignados':
            $idtramite = $data['idtramite'];
            echo $RequisitoController->get_lista_RequisitosAsignados($idtramite);
            break;

        
            // ----------------------
            // FOTOS
            // ----------------------
        case 'add_foto':
            if (isset($_FILES['file'])) {
                $foto_file = $_FILES['file'];
            } else {
                $foto_file = "";
            }
            echo $EmpresaController->insertFoto($foto_file);
            break;

        case 'del_foto':
            $id = $data['id'];
            echo $EmpresaController->deleteFoto($id);
            break;

        case 'get_fotos':
            echo $EmpresaController->listarFotos();
            break;

        // ----------------------------------------------------
        // ARCHIVOS
        // ----------------------------------------------------

        case 'subir_adjunto':
            if (isset($_FILES['archivopdf'])) {
                $archivopdf_file = $_FILES['archivopdf'];
                echo $ArchivosController->subir_archivo_pdf($archivopdf_file);
            } else {
                Response::error('Archivo no adjunto');
                $formatoPDF = "";
            }
          
            break;

        case 'subir_adjunto_especialista':
            if (isset($_FILES['archivo_especialista'])) {
                $archivopdf_file = $_FILES['archivo_especialista'];
                echo $ArchivosController->subir_archivo_ofimatica($archivopdf_file);
            } else {
                Response::error('Archivo no adjunto');
                $formatoPDF = "";
            }
          
            break;

        // case 'get_archivos':
        //     $archivo_id = $data['id'];
        //     echo $ArchivoController->getArchivo($archivo_id);
        //     break;

        // case 'get_lista_archivos':
        //     echo $ArchivoController->getArchivos();
        //     break;

        // case 'add_archivos':
        //     $archivo_tipo = $data['tipo'];
        //     $archivo_nombre = $data['nombre'];
        //     $archivo_descripcion = $data['descripcion'];
        //     $archivo_url = $data['url'];
        //     $archivo_fechasub = $data['fechasubida'];
        //     echo $ArchivoController->insertArchivo(
        //         $archivo_tipo,
        //         $archivo_nombre,
        //         $archivo_descripcion,
        //         $archivo_url,
        //         $archivo_fechasub
        //     );
        //     break;

        // case 'upd_archivos':
        //     $archivo_id = $data['id'];
        //     $archivo_tipo = $data['tipo'];
        //     $archivo_nombre = $data['nombre'];
        //     $archivo_descripcion = $data['descripcion'];
        //     $archivo_url = $data['url'];
        //     $archivo_fechasub = $data['fechasubida'];
        //     echo $ArchivoController->updateArchivo(
        //         $archivo_id,
        //         $archivo_tipo,
        //         $archivo_nombre,
        //         $archivo_descripcion,
        //         $archivo_url,
        //         $archivo_fechasub
        //     );
        //     break;

        // case 'delete_archivo':
        //     $archivo_id = $data['id'];
        //     echo $ArchivoController->deleteArchivo($archivo_id);
        //     break;

        // BUZON
        case 'add_buzon':
            $nombre      = $data['nombre'];
            $sigla       = $data['sigla'];
            $estado      = $data['estado'];
            $tipobuzon   = $data['tipobuzon'];
            $correonotificaion = isset($data['correonotificaion']) ? $data['correonotificaion'] : null;
            echo $BuzonController->insertBuzon($nombre, $sigla, $estado, $tipobuzon, $correonotificaion);
            break;

        case 'add_Buzon_AsignarUsuario':
            $nombre      = $data['nombre'];
            $sigla       = $data['sigla'];
            $estado      = $data['estado'];
            $correonotificaion = isset($data['correonotificaion']) ? $data['correonotificaion'] : null;
            $tipobuzon   = $data['tipobuzon'];
            $iduser      = $data['iduser'];
            echo $BuzonController->insertBuzon_AsignarUsuario($nombre, $sigla, $estado, $tipobuzon, $correonotificaion,$iduser);
            break;

        case 'get_buzon':
            $id = $data['id'];
            echo $BuzonController->getBuzon($id);
            break;

        case 'get_buzones':
            echo $BuzonController->getBuzones();
            break;
            
        case 'get_buzones_x_Usuario':
            $id = $data['id'];
            echo $UsuarioController->get_buzones_x_Usuario($id);
            break;

        case 'cambiar_buzon_usuariologueado':
            $id = $data['id'];
            $idbuzon = $data['idbuzon'];
            echo $UsuarioController->get_buzones_x_Usuario_idbuzon($id,$idbuzon);
            break;

        case 'update_buzon':
            $id          = $data['id'];
            $tipo        = $data['tipo'];
            $nombre      = $data['nombre'];
            $sigla       = $data['sigla'];
            $responsable = $data['responsable'];
            $correonotificacion = isset($data['correonotificaion']) ? $data['correonotificaion'] : null;
            echo $BuzonController->updateBuzon($id, $tipo, $nombre, $sigla,  $responsable, $correonotificacion);
            break;

        case 'delete_buzon':
            $id = $data['id'];
            echo $BuzonController->deleteBuzon($id);
            break;

        // eliminar buzon con consultas
        case 'eliminar_buzon':
            $id = $data['id'];
            echo $BuzonController->eliminarBuzon($id);
            break;

        // ASIGNACIÓN USUARIO-BUZÓN
        case 'assign_user_to_buzon':
            $userId = $data['userId'];
            $buzonId = $data['buzonId'];
            echo $BuzonController->assignUserToBuzon($userId, $buzonId);
            break;

        case 'asignarBuzonUsuario':
            $userId = $data['userId'];
            $buzonId = $data['buzonId'];
            echo $UsuarioController->asignarBuzonUsuario($userId, $buzonId);
            break;
            
        case 'remove_user_from_buzon':
            $userId = $data['userId'];
            $buzonId = $data['buzonId'];
            echo $BuzonController->removeUserFromBuzon($userId, $buzonId);
            break;
            
        case 'get_users_for_buzon':
            $buzonId = $data['buzonId'];
            echo $BuzonController->getUsersForBuzon($buzonId);
            break;
            
        case 'get_buzones_for_user':
            $userId = $data['userId'];
            echo $BuzonController->getBuzonesForUser($userId);
            break;


        case 'enviar_correo_html':
            $nombre      = $data['nombre'];
            $correo      = $data['correo'];
            $asunto      = $data['asunto'];
            $cuerpohtml  = $data['cuerpohtml'];

            echo $emailServer->sendmail_html($nombre,$correo,$asunto,$cuerpohtml);
            break;

        default:
            Response::error('Petición no autorizada');
            break;
    }
} else {
    Response::error('Envio de datos invalido');
}


function cleanField($value)
{
    if (is_array($value)) {
        return array_map('cleanField', $value);
    } else {
        // Elimina espacios en blanco al inicio y al final
        $value = trim($value);
        // Elimina caracteres especiales que puedan ser utilizados en ataques XSS
        // $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
        return $value;
    }
}
?>
