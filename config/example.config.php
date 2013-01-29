<?php

    define("HOST", "localhost");
    define("USER", "root");
    define("PASS", "");
    define("DBNAME", "krasimir_adminui");
    define("ADMINUI_URL", "http://admin-ui.dev/");
    define("FILES_DIR", "files/");
    define("RESOURCE_DIR", dirname(__FILE__)."/../resources/");

    global $USERS;
    $USERS = array(
        (object) array("username" => "admin", "password" => "admin")
    );

    global $IMAGE_SIZES;
    $IMAGE_SIZES = array(
        (object) array("prefix" => "small_", "height" => 100),
        (object) array("prefix" => "small2_", "width" => 100),
        (object) array("prefix" => "exact_", "width" => 100, "height" => 100),
        (object) array("prefix" => "scale_", "scale" => 30)
    );
    

?>
