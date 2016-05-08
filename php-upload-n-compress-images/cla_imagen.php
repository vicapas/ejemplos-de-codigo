<?php

/*
* cla_imagen.php
* Clase que representa un archivo de tipo imagen
*
* Created 06/05/2016
* @author: kamarena
*/

class Imagen {

  public $nombre;         // string: nombre de la imagen que se ha cargado
  public $url;          // string: url de la imagen
  public $hsize;        // int: anchura de la imagen
  public $vsize;        // int: altura de la imagen
  public $peso;         // int: peso en kb de la imagen
  public $tipo;         // int: tipo de imagen (1: gif, 2: jpg, 3: png)

  public function __construct($nombre, $url) {
    $this->nombre = $nombre;
    $this->url = $url;
    list($this->hsize, $this->vsize, $this->tipo) = getimagesize($this->url);
    $this->peso = filesize($this->url);
  }

  /*
  * Mueve la imagen a una nueva ubicación
  * @params string $url: url que apunta al fichero destino
  * @params string $nombre: nombre que recibirá la imágen
  * @return boolean: boleano que muestra si se ha realizado la acción
  */
  public function moverImagen($url, $nombre = '') {
    if ($nombre == '') $nombre = $this->nombre;
    return move_uploaded_file($this->url, $url . '/' . $nombre);

  }

}
