<?php

namespace app\utils;

class TemplateData
{

    protected $dir = 'assets/html/';
    protected $template;
    protected $variables = array();

    public function __construct($template = null, $values = [], $dir = null)
    {
        $this->setTemplate($template)->setVariables($values)->setDir($dir);
    }

    public function setTemplate($template = null)
    {

        if ($template === null) {
            return $this;
        }

        $this->template = $template;

        return $this;
    }

    public function setVariables($array = [])
    {
        if ($array) {
            foreach ($array as $key => $value) {
                $this->setVariable($key, $value);
            }
        }

        return $this;
    }

    public function setVariable($key, $value)
    {
        return $this->variables[$key] = $value;
    }

    public function setDir($dir = null)
    {
        if ($dir !== null) {
            $this->dir = $dir;
        }

        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function getTemplatePath()
    {
        return $this->getDir() . $this->getTemplate();
    }

    public function getVariables()
    {
        return $this->variables;
    }

    public function getDir()
    {
        return $this->dir;
    }

    public function __get($key)
    {
        return $this->variables[$key];
    }

    public function __set($key, $value)
    {
        $this->variables[$key] = $value;
    }
}
