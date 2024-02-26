<?php

namespace app\lib;

use app\utils\Methods;

abstract class Controllers
{

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    protected $view = null;


    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    /**
     * @param string $viewMethodName
     * @param array $args
     * @return self
     */
    protected function setView(string $viewMethodName, $args = []): self
    {
        $this->installMethodNamespace($viewMethodName, AppNamespace . 'views\\');
        $this->view = Methods::installMethod($viewMethodName, $args);

        return $this;
    }

    /**
     * @param string $action
     * @param array $params
     * @return mixed
     */
    protected function renderView($action, $params = [])
    {
        if (!$this->view)
            throw new \RuntimeException(
                'Controller View was not installed'
            );

        return Methods::callMethod($this->view, $action, array($params));
    }


    /**
     * @param string $methodName
     * @param string $namespace
     * @return void
     */
    private function installMethodNamespace(&$methodName, $namespace)
    {
        if (strpos($methodName, $namespace) === false) {
            $methodName = $namespace . $methodName;
        }
    }
}
