<?php

/*
* cla_cargarimagen.php
* Clase que representa la carga de una imágen en el servidor
*
* Created 05/05/2016
* @author: kamarena
*/

$SERVIDOR = filter_input_array(INPUT_SERVER);
$PATH = $SERVIDOR['DOCUMENT_ROOT']. '/proyectos/php-upload-n-compress-images';
require_once "$PATH/cla_imagen.php";

class CargarImagen {

  public $img;          // Array Imagen: representación de las imagenes cargadas
  public $load_path;    // string: url destino de la carga de las imagenes procesadas
  public $error;        // Array int: errores que se pueden haber producido al cargar

  /* Constructor */
  public function __construct($files_values, $load_path) {
    $this->load_path = $load_path;
    $this->cargarImagenes($files_values);
  }

  /*
  * Carga las imágenes en el array de imágenes
  * @params array $files_values: elementos cargados desde el html
  */
  private function cargarImagenes($files_values) {
    $this->img = array();
    $this->error = array();
    for ($i = 0; $i < count($files_values['name']); $i++) {
      $this->img[$i] = new Imagen(
        $files_values['name'][$i],
        $files_values['tmp_name'][$i]
      );
      $this->error[$i] = $files_values['error'][$i];
    }
  }

  /*
  * Guarda las imágenes cargadas en la carpeta que se indica
  * @return boolean: boleano que muestra si se ha realizado la acción
  */
  public function guardarImgOriginal() {
    $ok = true;

    foreach ($this->img as $imagen) {
      if ($ok) $ok = $imagen->moverImagen($this->load_path);
    }

    return $ok;
  }

  /*
  * Redimensiona las imagenes según anchura y altura máxima que la contiene
  * @params int $hmax: anchura máxima de las imágenes resultantes
  * @params int $vmax: altura máxima de la imágenes resultantes
  * @params int $calidad: porcentaje de calidad (0 - 100)
  * @return boolean: boleano que muestra si se ha realizado la acción
  */
  public function resizeToMax($hmax, $vmax, $calidad) {
    $ok = true;

    foreach ($this->img as $imagen) {
      if ($ok) {
        if ($imagen->vsize != 0) $ratio_ini = $imagen->hsize / $imagen->vsize;
        if ($vmax != 0) $ratio_max = $hmax / $vmax;

        if ($ratio_max > $ratio_ini) {
          $nuevo_ancho = $vmax * $ratio_ini;
          $nuevo_alto = $vmax;
        } else {
          $nuevo_ancho = $hmax;
          if ($ratio_ini != 0) $nuevo_alto = $hmax / $ratio_ini;
        }

        $ok = $this->resize($imagen, $nuevo_ancho, $nuevo_alto, $calidad);
      }
    }

    return $ok;
  }

  /*
  * Redimensiona las imagenes según porcentaje de reducción
  * @params float $porcentaje: porcentaje de reducción (si es menor que 1)
  * @params int $calidad: porcentaje de calidad (0 - 100)
  * @return boolean: boleano que muestra si se ha realizado la acción
  */
  public function resizeToPercent($porcentaje, $calidad) {
    $ok = true;

    foreach ($this->img as $imagen) {
      if ($ok) {
        $nuevo_ancho = $imagen->hsize * $porcentaje;
        $nuevo_alto = $imagen->vsize * $porcentaje;

        $ok = $this->resize($imagen, $nuevo_ancho, $nuevo_alto, $calidad);
      }
    }

    return $ok;
  }

  /*
  * Redimensiona las imagenes según los parámetros
  * @params Imagen $imagen: objeto imágen que se está procesando
  * @params float $porcentaje: proporción sobre las imágenes originales
  * @params int $calidad: porcentaje de calidad (0 - 100)
  * @return boolean: boleano que muestra si se ha realizado la acción
  */
  private function resize($imagen, $h, $v, $calidad) {
    $ok = true;

    $img_p = imagecreatetruecolor($h, $v);
    $img = $this->getImgResized($imagen);

    if ($img) {
      imagecopyresampled(
        $img_p, $img, 0, 0, 0, 0, $h, $v, $imagen->hsize, $imagen->vsize
      );
      $ok = $this->copyImgResized($imagen, $img_p, $imagen->nombre, $calidad);

      imagedestroy($img);
    } else {
      $ok = false;
    }

    imagedestroy($img_p);

    return $ok;
  }

  /*
  * Devuelve el tipo de error que se ha producido al cargar la imágen
  * @return string $message: mensaje que muestra el tipo de error que se ha producido
  */
  public function readError($index) {
    $message = 'Error uploading file';

    switch($this->error[$index]) {
      case UPLOAD_ERR_OK:
        $message = 'Sin errores';;
        break;
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        $message .= ' - file too large.';
        break;
      case UPLOAD_ERR_PARTIAL:
        $message .= ' - file upload was not completed.';
        break;
      case UPLOAD_ERR_NO_FILE:
        $message .= ' - zero-length file uploaded.';
        break;
      default:
        $message .= ' - internal error # ' . $this->error[$index];
        break;
    }

    return $message;
  }

  /*
  * Muestra los datos de la imágen cargada
  * @return string $data: cadena que muestra los datos
  */
  public function getFilesData() {
    $cadena = '';
    for ($i = 0; $i < count($this->img); $i++) {
      $cadena = $cadena . '---------------------------------------<br>';
      $cadena = $cadena . '<b>Nombre:</b> ' . $this->img[$i]->nombre . '<br>';
      $cadena = $cadena . '<b>Url:</b> ' . $this->img[$i]->url . '<br>';
      $cadena = $cadena . '<b>Horizontal:</b> ' . $this->img[$i]->hsize . ' px<br>';
      $cadena = $cadena . '<b>Vertical:</b> ' . $this->img[$i]->vsize . ' px<br>';
      $cadena = $cadena . '<b>Peso:</b> ' . $this->img[$i]->peso . ' bytes<br>';
      $cadena = $cadena . '<b>Tipo:</b> ' . $this->img[$i]->tipo . '<br>';
      $cadena = $cadena . '<b>Error:</b> ' . $this->readError($i) . '<br>';
      $cadena = $cadena . '---------------------------------------<br>';
    }
    return $cadena;
  }

  /*
  * Obtiene la imágen base para redimensionar
  * @params Imagen $imagen: objeto imágen que se está procesando
  * @return binary raw image data $img: fichero imágen que se quiere redimensionar
  */
  private function getImgResized($imagen) {
    switch ($imagen->tipo) {
      case IMAGETYPE_GIF:
        $img = imagecreatefromgif($imagen->url);
        break;
      case IMAGETYPE_JPEG:
        $img = imagecreatefromjpeg($imagen->url);
        break;
      case IMAGETYPE_PNG:
        $img = imagecreatefrompng($imagen->url);
        break;
      default:
        $img = false;
        break;
    }

    return $img;
  }

  /*
  * Copia la imágen procesada a la carpeta destino
  * @params Imagen $imagen: objeto imágen que se está procesando
  * @params resource $img_p: imágen que se va a copiar
  * @params string $nombre: nombre que recibirá la imágen copiada
  * @params float $calidad: calidad final que se le quiere dar a la imágen
  * @return boolean $ok: booleano que muestra si se ha realizado la acción
  */
  private function copyImgResized($imagen, $img_p, $nombre, $calidad) {
    $ok = true;

    switch ($imagen->tipo) {
      case IMAGETYPE_GIF:
        imagejpeg($img_p, $this->load_path . '/'
          . basename($nombre, '.gif') . '.jpg', $calidad);
        break;
      case IMAGETYPE_JPEG:
        imagejpeg($img_p, $this->load_path . '/' . $nombre, $calidad);
        break;
      case IMAGETYPE_PNG:
        imagejpeg($img_p, $this->load_path . '/'
          . basename($nombre, '.png') . '.jpg', $calidad);
        break;
      default:
        $ok = false;
        break;
    }

    return $ok;
  }

}
