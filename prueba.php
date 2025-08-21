<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;

// Cargar la plantilla existente
$templateProcessor = new TemplateProcessor('resolucion.docx');

// Reemplazar los campos de la plantilla
$templateProcessor->setValue('numresolucion', 'PROBANDO');
$templateProcessor->setValue('fechahoylarga', 'PROBANDO');
$templateProcessor->setValue('representantelegal', 'PROBANDO');
$templateProcessor->setValue('direccioncomercial', 'PROBANDO');
$templateProcessor->setValue('actividadcomercial', 'PROBANDO');
$templateProcessor->setValue('ruc', 'PROBANDO');
$templateProcessor->setValue('dni', 'PROBANDO');
$templateProcessor->setValue('recibopagonumero', 'PROBANDO');
$templateProcessor->setValue('zonificacion', 'PROBANDO');
$templateProcessor->setValue('horarioatencion', 'PROBANDO');
$templateProcessor->setValue('nrolicencia', 'PROBANDO');
$templateProcessor->setValue('razonsocial', 'PROBANDO');
$templateProcessor->setValue('nombrecomercial', 'PROBANDO');
$templateProcessor->setValue('area', 'PROBANDO');



// Guardar el archivo Word generado temporalmente
$tempFile = 'carta_generada.docx';
$templateProcessor->saveAs($tempFile);

// Convertir el archivo Word a HTML
$phpWord = IOFactory::load($tempFile);
$xmlWriter = IOFactory::createWriter($phpWord, 'HTML');
ob_start();
$xmlWriter->save('php://output');
$htmlContent = ob_get_clean();

// Forzar la codificación UTF-8 para todo el contenido
$htmlContent = mb_convert_encoding($htmlContent, 'UTF-8', 'auto');

// Configuración de Dompdf con opciones personalizadas
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Permite cargar imágenes externas o rutas absolutas
$options->set('defaultFont', 'helvetica'); // Usar la fuente Helvetica

$dompdf = new Dompdf($options);
$dompdf->loadHtml($htmlContent);

// Ajustar los márgenes en cm (Dompdf utiliza milímetros)
$dompdf->setPaper('A4', 'portrait'); // Formato A4 vertical

// Crear estilos personalizados para los márgenes
$customCss = '
    <style>
        @page {
            margin-top: 2.5cm;
            margin-bottom: 2.5cm;
            margin-left: 2.8cm;
            margin-right: 2.8cm;
        }
        body {
            font-family: FreeSerif, sans-serif;
            font-weight: normal;
        }
        header {
            position: fixed;
            top: -2.1cm;
            left: 0;
            right: 0;
            height: 2cm;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            color: #000;
        }
        header .nombreanio{
            font-size: 10px;
            padding-right: 15px;
            padding-left: 15px;
            font-style: italic;
        }
        p {
            text-indent: 1.5cm;   
            text-align: justify; 
            margin-bottom: 10px; 
        }
        /* Estilos para eliminar los bordes de la tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
         /* Aplicar anchos específicos a las dos columnas */
        table tr th:first-child, 
        table tr td:first-child {
            width: 40%; /* Primera columna 40% */
        }

        table tr th:last-child, 
        table tr td:last-child {
            width: 60%; /* Segunda columna 60% */
        }
        table p{
            text-indent: 0cm; /* Eliminar la sangría */
        }

        table, th, td {
            border: none !important; /* Fuerza la eliminación de los bordes */
        }

        th, td {
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }
        /* Ajustar el tamaño de las celdas para que no se distorsionen */
        table {
            table-layout: auto;
        }
    </style>
';

// Contenido dinámico con caracteres especiales
$nombreanio = "Año del bicentenario, de la consolidación de nuestra independencia y de la conmemoración de las heroicas batallas de Junín y Ayacucho";

// Usar htmlspecialchars() para manejar correctamente caracteres especiales en HTML
$nombreanio = htmlspecialchars($nombreanio, ENT_QUOTES, 'UTF-8');

// Crear el contenido del encabezado, incluyendo la imagen en el logo
$headerHtml = "
    <header>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
        <img src='escudo.png' width='50' style='float: left; margin-right: 10px;' />
        <div> 
            MUNICIPALIDAD DISTRITAL VEINTISÉIS DE OCTUBRE</br>
            GERENCIA DE DESARROLLO ECONÓMICO</br>
            <div class='nombreanio'>".$nombreanio."</div>
        </div>
    </header>
";

// Aplicar los márgenes y la fuente FreeSerif en el HTML
$htmlContentWithMargins = $headerHtml . $customCss . $htmlContent;

$dompdf->loadHtml($htmlContentWithMargins);
$dompdf->render();

// Guardar el PDF en un archivo
$pdfOutput = $dompdf->output();
file_put_contents('documento_generado.pdf', $pdfOutput);

echo "El archivo PDF ha sido generado exitosamente como documento_generado.pdf.";

?>
