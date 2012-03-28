<?php

class Pwg_User extends Pwg_Autoparams implements Pwg_I_User {
	
    protected $login = false;
    
    protected $id = false;
        
    protected function setLogin($login) {
        $this->login = $login;
    }

    function getLogin() {
        return $this->login;
    }

    protected function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }
    
}