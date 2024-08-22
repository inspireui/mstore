<?php include_once(plugin_dir_path(dirname(__FILE__)) . 'functions/index.php'); ?>

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <style type="text/tailwindcss">
        .mstore-input-class { 
            @apply border border-gray-300 text-gray-900 text-sm rounded focus:border-blue-500 w-full sm:max-w-md px-2 py-3
        }
        .mstore-button-class {
            @apply mt-5 px-5 py-2 text-base font-medium text-center text-white bg-green-700 rounded hover:bg-green-800 
        }
        .mstore-file-input-class {
            @apply block w-full text-sm text-slate-500
      file:mr-4 file:py-2 file:px-4
      file:rounded-full file:border-0
      file:text-sm file:font-semibold
      file:bg-violet-50 file:text-violet-700
      hover:file:bg-violet-100
        }
    </style>
</head>
<body>
<?php
	wp_enqueue_script('my_script', plugins_url('assets/js/mstore-inspireui.js', MSTORE_PLUGIN_FILE), array('jquery'), '1.0.0', true);
            wp_localize_script('my_script', 'MyAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
	?>
<div class="container mx-auto p-5 bg-white">
    <h4 class="text-xl text-semibold">MStore API Settings</h4> <br/>
    <?php echo load_template(dirname(__FILE__) . '/admin/mstore-api-admin-dashboard.php'); ?>
</div>

</body>
</html>