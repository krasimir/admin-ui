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
            $resources = new Resources();
            $resources = $resources->get();
            $markup = '';
            foreach($resources as $r) {
                $markup .= view("home/resources-link.html", array(
                    "link" => ADMINUI_URL."resources/".$r->content->name,
                    "label" => $r->content->title
                ));
            }
            return $markup;
        }
    }

?>