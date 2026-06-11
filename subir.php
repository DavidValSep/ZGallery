<?php
/**
 * Subir archivos (individual o múltiples)
 * - Valida permiso desde settings `allow_uploads`
 * - Guarda en la carpeta `fotos/`
 * - Inserta registro en `uploads` y `selected`
 */
session_start();
require_once 'config.php';
require_once 'includes/dbcon.php';

// Verificar permiso
$allowUploadsRow = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['allow_uploads']);
$allowUploads = $allowUploadsRow ? $allowUploadsRow['svalue'] === '1' : true;

if (!$allowUploads) {
    http_response_code(403);
    echo 'Subidas deshabilitadas';
    exit;
}

    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500">
    <link rel="stylesheet" href="includes/screen.css">
    <link rel="stylesheet" href="http://lokeshdhakar.com/projects/lightbox2/css/lightbox.css">
	<script src="http://code.jquery.com/jquery-latest.js"></script>
</head>
 
<body>
  <section>
    <h3 style="margin-left: 30px;"><?php echo $irname; ?><br>Subir fotograf&iacute;as</h3>
    <div style="display:block;">
    <?php
    # definimos la carpeta destino
    $carpetaDestino="fotos/";
 
    # si hay algun archivo que subir
    if($_FILES["archivo"]["name"][0])
    {
 
        # recorremos todos los arhivos que se han subido
        for($i=0;$i<count($_FILES["archivo"]["name"]);$i++)
        {
 
            # si es un formato de imagen
            if($_FILES["archivo"]["type"][$i]=="image/jpeg" || $_FILES["archivo"]["type"][$i]=="image/pjpeg" || $_FILES["archivo"]["type"][$i]=="image/gif" || $_FILES["archivo"]["type"][$i]=="image/png")
            {
 
                # si exsite la carpeta o se ha creado
                if(file_exists($carpetaDestino) || @mkdir($carpetaDestino))
                {
                    $origen=$_FILES["archivo"]["tmp_name"][$i];
                    $destino=$carpetaDestino.$_FILES["archivo"]["name"][$i];
 
                    # movemos el archivo
                    if(@move_uploaded_file($origen, $destino))
                    {
                        echo "<br>".$_FILES["archivo"]["name"][$i]." movido correctamente";
                    }else{
                        echo "<br>No se ha podido mover el archivo: ".$_FILES["archivo"]["name"][$i];
                    }
                }else{
                    echo "<br>No se ha podido crear la carpeta: up/".$user;
                }
            }else{
                echo "<br>".$_FILES["archivo"]["name"][$i]." - NO es imagen jpg";
            }
        }
    }else{
        echo "<br>No se ha subido ninguna imagen";
    }
    ?>
 
    <form action="<?php echo $_SERVER["PHP_SELF"]?>" method="post" enctype="multipart/form-data" name="inscripcion">
        <input type="file" name="archivo[]" multiple="multiple">
        <input type="submit" value="Enviar"  class="trig">
    </form>
	</div>
	<div style="clear:both;"></div>
  </section>
  <section>
    <div style="display:block;">
<?php
$directorio = opendir("./fotos/"); //ruta actual
while ($archivo = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
{
    if (!is_dir($archivo) && $archivo != 'index.php')//verificamos si es o no un directorio
    {
        echo '<div style="display: inline-block;margin-bottom:30px;"><a class="image-link" href="pre.php?file='.$archivo.'&m=800" data-lightbox="foto-'.$archivo.'"><img class="image" src="mini.php?file='.$archivo.'&m=200" alt="'.$archivo.'" /></a>
              <br>';
echo '</div>';
    }
}
?>
    </div>
  </section>
<p>&nbsp;</p>
<div style="clear:both;"></div></body>
</html>