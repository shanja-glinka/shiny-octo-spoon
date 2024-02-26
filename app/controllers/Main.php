<?php

namespace app\controllers;

use app\lib\Controllers;
use app\models\Auth;

class Main extends Controllers
{
    /** @var Auth */
    private Auth $auth;


    public function __construct()
    {
        parent::__construct();

        $this->response->setContentType('text');

        $this->auth = new Auth;
    }


    public function login()
    {
        if ($this->auth->getClientId() > 0) {
            $this->response->redirectTo('/profile');
        }

        return $this->setView('Main')->renderView('login');
    }

    public function logout()
    {
        $this->auth->signout();
        $this->response->redirectTo('/login');
    }

    public function signup()
    {
        return $this->setView('Main')->renderView('signup');
    }

    public function profile()
    {
        if (!$this->auth->getClientId()) {
            $this->response->redirectTo('/login');
        }

        return $this->setView('Main')->renderView('profile');
    }

    public function hiMark()
    {
        return $this->setView('Main')->renderView('hiMark');
    }
}
