
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Hello, world!</title>
  </head>
  <body>
  <form action="" enctype="multipart/form-data" method="post">
    <div class="form-group" style="margin-top:30px">
        <label for="exampleFormControlFile1">Upload config.json file</label>
        <input id="fileToUpload" accept=".json" name="fileToUpload" type="file" class="form-control-file">
    </div>
    <button type="submit" class="btn btn-primary" name='but_submit'>Upload</button>
    </form>
    <p style="font-size: 16px; color: #1B9D0D; margin-top:10px">
    <?php
    if (isset($_POST['but_submit'])) {     
      wp_upload_bits($_FILES['fileToUpload']['name'], null, file_get_contents($_FILES['fileToUpload']['tmp_name'])); 
      $uploads_dir = dirname( __FILE__ );
      $source      = $_FILES['fileToUpload']['tmp_name'];
      $destination = trailingslashit( $uploads_dir ) . $_FILES['fileToUpload']['name'];
      move_uploaded_file($source, $destination);
      echo "The config file has been uploaded.";
    }else{
      if (file_exists($uploads_dir = dirname( __FILE__ )."/config.json")) {
        echo "The config file has been uploaded.";
      }
    }
    ?>
    </p>
    
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>