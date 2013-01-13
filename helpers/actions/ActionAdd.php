<?php

    class ActionAdd extends Action {

        public function __construct($resource) {
            parent::__construct($resource);
            $this->defineContext();
            $this->defineForm();
            $this->run();
        }
        public function run() {
            $this->form->update(array_merge($_POST, $_FILES));
            if($this->form->submitted && $this->form->success) {
                // Form is submitted
                $data = $this->handleFileUploads($this->form->data);
                $this->mysql->{$this->resource->name}->save($this->prepareObjectForSave($data));
                $this->response->write(view("layout.html", array(
                    "pageTitle" => $this->resource->title,
                    "content" => view("resource/add.html", array(
                        "title" => $this->resource->title,
                        "name" => $this->resource->name,
                        "form" => view("resource/success.html", array(
                            "message" => "The record is added successfully. <a href='".ADMINUI_URL."resources/".$this->resource->name."'>Back</a>."
                        ))
                    )),
                    "nav" => view("nav.html")
                )))->send();
            } else {
                $this->response->write(view("layout.html", array(
                    "pageTitle" => $this->resource->title,
                    "content" => view("resource/add.html", array(
                        "title" => $this->resource->title,
                        "name" => $this->resource->name,
                        "form" => $this->form->markup
                    )),
                    "nav" => view("nav.html")
                )))->send();
            }
        }

    }

?>