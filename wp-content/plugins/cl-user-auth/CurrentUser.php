<?php

class CurrentUser
{
    private static $instance;
    private $user = null;
    private $userData = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function setUser($user)
    {
        if (!$user || !($user instanceof WP_User) || !$user->ID) {
            return null;
        }
        $this->user = $user;
        $this->setUserData();

        return $user;
    }

    private function setUserData(){
        $user = new stdClass();
        $user->user_email = $this->user->user_email;
        $user->user_first_name = $this->user->user_firstname;
        $user->user_last_name = $this->user->user_lastname;
        $user->user_display_name = $this->user->display_name;
        $user->user_id = $this->user->ID;
        $user->user_user_name = $this->user->user_login;
        $this->userData = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getUserData()
    {
        return $this->userData;
    }
}
