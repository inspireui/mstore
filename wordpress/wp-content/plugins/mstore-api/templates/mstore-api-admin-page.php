
<!doctype html>
<html <?php language_attributes(); ?> >
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <?php wp_head(); ?>
    </head>
  <body>
<div class="wrap">
	<h1>MStore API Settings</h1>

  <br>

  <div class="thanks">
  <p style="font-size: 16px;">Thank you for installing Mstore API plugins.</p>
  <p style="font-size: 16px;">This setting help to speed up the mobile app performance,  upload the config.json from the common folder:</p>
	</div>
</div>

  <form action="" enctype="multipart/form-data" method="post">
  
    <div class="form-group" style="margin-top:30px">
        <input id="fileToUpload" accept=".json" name="fileToUpload" type="file" class="form-control-file">
    </div>
    
    <p style="font-size: 14px; color: #1B9D0D; margin-top:10px">
    <?php
    if (isset($_POST['but_submit'])) {     
      wp_upload_bits($_FILES['fileToUpload']['name'], null, file_get_contents($_FILES['fileToUpload']['tmp_name'])); 
      $uploads_dir = str_replace('plugins/mstore-api/templates','uploads',dirname( __FILE__ ));
      $source      = $_FILES['fileToUpload']['tmp_name'];
      $destination = trailingslashit( $uploads_dir ) . '2000/01/config.json';
      if (!file_exists($uploads_dir."/2000/01")) {
        mkdir($uploads_dir."/2000/01", 0777, true);
      }
      move_uploaded_file($source, $destination);
      echo "The caching is active.";
    }else{
      if (file_exists($uploads_dir = str_replace('plugins/mstore-api/templates','uploads',dirname( __FILE__ ))."/2000/01/config.json")) {
        echo "The caching is active.";
      }
    }
    ?>
    </p>

    <button type="submit" class="btn btn-primary" name='but_submit'>Save</button>
    </form>

  </body>
</html>