<?php

    use flight\net\Response;

    class Controller {
        protected $response;
        public function __construct() {
            $this->response = new Response();
        }
    }

?>