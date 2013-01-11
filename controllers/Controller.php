<?php

    use flight\net\Response;

    class Controller {
        protected $response;
        protected $mysql;
        public function __construct() {
            global $mysql;
            $this->response = new Response();
            $this->mysql = $mysql;
        }
    }

?>