<?php

    class Resource extends Controller {

        public $matchedRouterRule;
        public $resources;
        public $id;
        public $resource;

        public function __construct($params) {

            parent::__construct();
            $this->matchedRouterRule = $params["ROUTER_RULE_MATCH"];
            $this->resource = Resources::getByName(isset($params["name"]) ? $params["name"] : false)->content;

            switch($this->matchedRouterRule->pattern) {
                case "/resources/@name":
                    new ActionList($this->resource);
                break;
                case "/resources/@name/add":
                    new ActionAdd($this->resource);
                break;
                case "/resources/@name/edit/@id":
                    new ActionEdit($this->resource, isset($params["id"]) ? $params["id"] : false);
                break;
                case "/resources/@name/delete/@id":
                    new ActionDelete($this->resource, isset($params["id"]) ? $params["id"] : false);
                break;
                case "/resources/@name/up/@id":
                    new ActionPosition($this->resource, isset($params["id"]) ? $params["id"] : false, "up");
                break;
                case "/resources/@name/down/@id":
                    new ActionPosition($this->resource, isset($params["id"]) ? $params["id"] : false, "down");
                break;
            }

        }
    }

?>