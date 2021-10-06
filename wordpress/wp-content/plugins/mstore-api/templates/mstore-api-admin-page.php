<?php include_once(plugin_dir_path(dirname(__FILE__)) . 'functions/index.php'); ?>

<!doctype html>
<html <?php language_attributes(); ?> >
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <?php wp_head(); ?>
    <style>
        .mstore_input {
            margin-bottom: 10px;
            width: 400px !important;
            padding: .857em 1.214em !important;
            background-color: transparent;
            color: #818181 !important;
            line-height: 1.286em !important;
            outline: 0;
            border: 0;
            -webkit-appearance: none;
            border-radius: 1.571em !important;
            box-sizing: border-box;
            border-width: 1px;
            border-style: solid;
            border-color: #ddd;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, .07) !important;
            transition: 50ms border-color ease-in-out;
            font-family: "Open Sans", HelveticaNeue-Light, "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
            touch-action: manipulation;
        }

        .mstore_button {
            position: relative;
            border: 0 none;
            border-radius: 3px !important;
            color: #fff !important;
            display: inline-block;
            font-family: 'Poppins', 'Open Sans', Helvetica, Arial, sans-serif;
            font-size: 12px;
            letter-spacing: 1px;
            line-height: 1.5;
            text-transform: uppercase;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            margin-bottom: 21px;
            margin-right: 10px;
            line-height: 1;
            padding: 12px 30px;
            background: #39c36e !important;
            -webkit-transition: all 0.21s ease;
            -moz-transition: all 0.21s ease;
            -o-transition: all 0.21s ease;
            transition: all 0.21s ease;
        }

        .mstore_title {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: .5em;
            line-height: 1.1;
            display: block;
            margin-inline-start: 0px;
            margin-inline-end: 0px;
        }

        .mstore_list {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
            vertical-align: baseline;
            display: block;
            margin-block-start: 1em;
            margin-block-end: 1em;
            margin-inline-start: 0px;
            margin-inline-end: 0px;
            padding-inline-start: 40px;
            list-style: none;
        }

        .mstore_list li {
            list-style-type: square;
            font-size: 14px;
            font-weight: normal;
            margin-bottom: 6px;
            display: list-item;
            text-align: -webkit-match-parent;
        }

        .mstore_number_list li {
            list-style-type: decimal;
        }

        .mstore_link {
            margin-inline-start: 0px;
            margin-inline-end: 0px;
            color: #0099ff;
            text-decoration: none;
            outline: 0;
            transition-property: border, background, color;
            transition-duration: .05s;
            transition-timing-function: ease-in-out;
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
            vertical-align: baseline;
            margin-bottom: 20px;
            display: block;
        }

        .mstore_table {
            width: 100%;
            max-width: 100%;
            margin-bottom: 1.236rem;
            background-color: transparent;
            border-spacing: 0;
            border-collapse: collapse;
            display: table;
            border-color: grey;
        }

        .mstore_table a {
            color: #0099ff;
            text-decoration: none;
        }

        .mstore_table th, .mstore_table td {
            text-align: left;
        }

        .mstore_deactive_button {
            background: #C84B31 !important;
        }
    </style>
</head>
<body>
<div id="mstore-api-builder-container" hidden="true">
    <button type="button" class="mstore_button" name='btn_back'>Back</button>
    <h4>MStore API Builder</h4> <br/>
    <?= load_template(dirname(__FILE__) . '/admin/mstore-api-admin-builder.php'); ?>
</div>

<div id="mstore-api-settings-container">
    <h4>MStore API Settings</h4> <br/>
    <?= load_template(dirname(__FILE__) . '/admin/mstore-api-admin-dashboard.php'); ?>
</div>

</body>
</html>