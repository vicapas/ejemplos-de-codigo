<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Prototipo Compresor imágenes</title>
  </head>
  <body>

    <form class="formulario" enctype="multipart/form-data" action="image_upload.php" method="POST">
      <input type="hidden" name="MAX_FILE_SIZE" value="4000000" />
      <input type="file" multiple="multiple" name="img_loader[]" accept="image/*" />
      <input type="submit" name="btn_load" value="Carga" />
    </form>

    <div class="respuesta"></div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>

    <script>
      $(function() {

        $(".formulario").submit(function(event) {
          console.log('ok');
          event.preventDefault();

          var formData = new FormData($('.formulario')[0]);
          var ruta = 'image_upload.php';

          $.ajax({
            url: ruta,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(datos) {
              $('.respuesta').html(datos);
            }
          });

        });

      });
    </script>

  </body>
</html>
