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
            header("Location: ".ADMINUI_URL."resources/".$this->resource->name);
            die();
        }

    }

?>