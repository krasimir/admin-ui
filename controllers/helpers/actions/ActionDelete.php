<?php

    class ActionDelete extends Action {

        public function __construct($resource, $id) {
            parent::__construct($resource);
            $this->id = $id;
            $this->defineContext();
            $this->defineForm();
            $this->run();
        }
        public function run() {
            $record = $this->mysql->{$this->resource->name}->where("id=".$this->id)->get();
            $record = $record[0];
            $this->mysql->{$this->resource->name}->trash($record);
            $this->response->write(view("layout.html", array(
                "pageTitle" => $this->resource->title,
                "content" => view("resource/add.html", array(
                    "title" => $this->resource->title,
                    "name" => $this->resource->name,
                    "form" => view("resource/success.html", array(
                        "message" => "The record is deleted successfully. <a href='".ADMINUI_URL."resources/".$this->resource->name."'>Go back</a>."
                    ))
                )),
                "nav" => view("nav.html")
            )))->send();
        }

    }

?>