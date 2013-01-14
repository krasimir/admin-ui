<?php

    class ActionPosition extends Action {

        public function __construct($resource, $id, $direction) {
            parent::__construct($resource);
            $this->id = $id;
            $this->defineContext();
            $this->defineForm();
            $this->run($direction);
        }
        public function run($direction) {
            $records = $this->mysql->{$this->resource->name}->order("position")->asc()->get();
            $numOfRecords = count($records);
            for($i=0; $i<$numOfRecords; $i++) {
                $record = $records[$i];
                if($record->id == $this->id) {
                    if($direction == "up") {
                        if($i > 0) {
                            $tmp = $record->position;
                            $record->position = $records[$i-1]->position;
                            $records[$i-1]->position = $tmp;
                            $this->mysql->{$this->resource->name}->save($records[$i-1]);
                            $this->mysql->{$this->resource->name}->save($record);
                            header("Location: ".ADMINUI_URL."resources/".$this->resource->name);
                            die();
                        }
                    } else {
                        if($i < $numOfRecords-1) {
                            $tmp = $record->position;
                            $record->position = $records[$i+1]->position;
                            $records[$i+1]->position = $tmp;
                            $this->mysql->{$this->resource->name}->save($records[$i+1]);
                            $this->mysql->{$this->resource->name}->save($record);
                            header("Location: ".ADMINUI_URL."resources/".$this->resource->name);
                            die();
                        }
                    }
                }
            }
            header("Location: ".ADMINUI_URL."resources/".$this->resource->name);
            die();
        }

    }

?>