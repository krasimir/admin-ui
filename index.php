<?php

    session_start();

    require(dirname(__FILE__)."/modules/Autoloader/Autoloader.php");

    // modules + resources
    global $F;
    $F->loadModule(
        "ErrorHandler",
        "View",
        "Router", 
        "DBAdapters/MySQL",
        "SessionManager",
        "Former"
    );
    $F->loadResource(
        "config/config.php",
        "controllers/*",
        "modules/FlightNet/Response.php"
    );

    // configuration of the template engine and Former
    View::$root = dirname(__FILE__)."/tpl/";
    View::$forEachView = array(
        "siteURL" => ADMINUI_URL
    );

    // database
    $mysql = new MySQLAdapter((object) array(
        "host" => HOST,
        "user" => USER,
        "pass" => PASS,
        "dbname" => DBNAME
    ));

    // routing
    $router = new Router();
    $router
    ->register("/logout", "Logout")
    ->register("/resources/@name/up/@id", array("RequireLogin", "Resource"))
    ->register("/resources/@name/down/@id", array("RequireLogin", "Resource"))
    ->register("/resources/@name/delete/@id", array("RequireLogin", "Resource"))
    ->register("/resources/@name/edit/@id", array("RequireLogin", "Resource"))
    ->register("/resources/@name/add", array("RequireLogin", "Resource"))
    ->register("/resources/@name", array("RequireLogin", "Resource"))
    ->register("", array("RequireLogin", "Main"))
    ->run();

?>