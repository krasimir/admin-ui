<?php

    use flight\net\Response;

    class Controller {
        public $response;
        public $mysql;
        public function __construct() {
            global $mysql;
            $this->response = new Response();
            $this->mysql = $mysql;
        }
    }

?>