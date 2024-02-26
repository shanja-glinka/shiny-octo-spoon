<?php

namespace app\views;

use app\lib\Response;
use app\lib\Views;
use app\utils\TemplateData;

class Main extends Views
{

    public function __construct()
    {
        $response = new Response('html');
        parent::__construct($response);
    }

    public function hiMark($args = [])
    {
        $this->response->setContentType('text');
        return $this->response->withHtml(new TemplateData('himark.html', $args));
    }

    public function login($args = [])
    {
        $this->response->setContentType('text');
        return $this->response->withHtml(new TemplateData('login.html', $args));
    }

    public function signup($args = [])
    {
        $this->response->setContentType('text');
        return $this->response->withHtml(new TemplateData('signup.html', $args));
    }

    public function profile($args = [])
    {
        $this->response->setContentType('text');
        return $this->response->withHtml(new TemplateData('profile.html', $args));
    }
}
