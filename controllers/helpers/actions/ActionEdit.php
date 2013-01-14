<?php

    class ActionEdit extends Action {

        public function __construct($resource, $id) {
            parent::__construct($resource);
            $this->id = $id;
            $this->defineContext();
            $this->defineForm();
            $this->run($id);
        }
        public function run() {
            $record = $this->mysql->{$this->resource->name}->where("id=".$this->id)->get();
            $record = $record[0];
            foreach($this->resource->data as $item) {
                if($item->presenter == "File") {
                   $record->{$item->name."_hidden"} = $record->{$item->name};
                }
                if($item->presenter == "Check") {
                    $record->{$item->name} = explode(",", $record->{$item->name});
                }
            }
            $this->form->update(array_merge($_POST, $_FILES), $record);
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
                    "content" => view("resource/edit.html", array(
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