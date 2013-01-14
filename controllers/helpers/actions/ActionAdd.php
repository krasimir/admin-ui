<?php

    class ActionAdd extends Action {

        public function __construct($resource) {
            parent::__construct($resource);
            $this->defineContext();
            $this->defineForm();
            $this->run();
        }
        public function run($default = null) {
            $this->form->update(array_merge($_POST, $_FILES), $default);
            if($this->form->submitted && $this->form->success) {
                // Form is submitted
                $data = $this->handleFileUploads($this->form->data);
                $data = $this->formatData($data);
                $this->mysql->{$this->resource->name}->save($data);
                header("Location: ".ADMINUI_URL."resources/".$this->resource->name);
                die();
            } else {
                $this->response->write(view("layout.html", array(
                    "pageTitle" => $this->resource->title,
                    "content" => view("resource/add.html", array(
                        "title" => $this->resource->title,
                        "name" => $this->resource->name,
                        "form" => $this->form->markup,
                        "childs" => $this->getChildResourcesMarkup()
                    )),
                    "nav" => view("nav.html")
                )))->send();
            }
        }

    }

?>