<?php

$SERVIDOR = filter_input_array(INPUT_SERVER);
$PATH = $SERVIDOR['DOCUMENT_ROOT']. '/proyectos/php-upload-n-compress-images';
$PATH_LOAD = "$PATH/load_images";
require_once "$PATH/cla_cargarimagen.php";

$input_name = 'img_loader';

$carga_img = new CargarImagen($_FILES[$input_name], $PATH_LOAD);

echo $carga_img->getFilesData() . '<br>';

if ($carga_img->resizeToMax(800, 600, 100)) {
  echo 'Realizada la carga y compresión con éxito.';
} else {
  echo 'No se ha podido cargar y comprimir las imágenes.';
}
