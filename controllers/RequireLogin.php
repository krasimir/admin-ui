<?php

    Former::register("login-form", ADMINUI_URL)
    ->addTextBox(array(
        "name" => "username", 
        "label" => "Username:",
        "validation" => Former::validation()->NotEmpty()
    ))
    ->addPasswordBox(array(
        "name" => "password", 
        "label" => "Password:",
        "validation" => Former::validation()->NotEmpty()
    ));

    class RequireLogin extends Controller {

        public function __construct($params) {
            parent::__construct();
            if(SessionManager::read("admin-ui-user") === false) {
                global $USERS;
                $form = Former::get("login-form");
                $form->update($_POST);
                $errorMessage = "";
                if($form->data->username && $form->data->password) {
                    foreach ($USERS as $user) {
                        if($user->username === $form->data->password && $user->password === $form->data->username) {
                            SessionManager::write("admin-ui-user", $user->username);
                            return true;
                        }
                    }
                    $errorMessage = view("error.html", array(
                        "message" => "Wrong credentials"
                    ));
                }
                $this->response->write(view("layout.html", array(
                    "pageTitle" => "Please login first.",
                    "content" => $errorMessage.$form->markup,
                    "nav" => ""
                )))->send();
            }
        }

    }

?>