<?php

    define("HOST", "localhost");
    define("USER", "root");
    define("PASS", "");
    define("DBNAME", "krasimir_adminui");
    define("ADMINUI_URL", "http://admin-ui.dev/");
    define("FILES_DIR", "files/");

    global $USERS;
    $USERS = array(
        (object) array("username" => "admin", "password" => "admin")
    );
    

?>