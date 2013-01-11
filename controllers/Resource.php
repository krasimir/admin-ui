<?php

    class Resource extends Controller {
        private $resource;
        public function __construct($params) {
            parent::__construct();
            $resource = new Resources();
            $this->resource = $resource->getByURL($params["name"]);
            
        }
    }

?>