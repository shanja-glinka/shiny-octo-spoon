<?php

namespace app\lib;

abstract class Views
{

    protected $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

}