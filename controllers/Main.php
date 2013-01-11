<?php

    class Main extends Controller {
        public function __construct() {
            parent::__construct();
            $this->response->write(view("layout.html", array(
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
                    "link" => ADMINUI_URL."resources/".$r->content->url,
                    "label" => $r->content->title
                ));
            }
            return $markup;
        }
    }

?>