<?php

    class Main extends Controller {
        public function __construct() {
            parent::__construct();
            $this->response->write(view("layout.html", array(
                "pageTitle" => "Administration",
                "content" => view("home/index.html", array(
                    "resources" => $this->getResourcesMarkup()
                )),
                "nav" => view("nav.html")
            )))->send();
        }
        private function getResourcesMarkup() {
            $resources = Resources::get();
            $markup = '';
            foreach($resources as $r) {
                if(!isset($r->content->parent)) {
                    $markup .= view("home/resources-link.html", array(
                        "link" => ADMINUI_URL."resources/".$r->content->name,
                        "label" => $r->content->title
                    ));
                }
            }
            return $markup;
        }
    }

?>