<?php

namespace app\lib;

use app\utils\Methods;

final class Router
{

    /** @var array */
    protected $routes = [];

    /** @var string */
    protected string $requestUrl;

    /** @var string */
    protected string $requestMethod;

    /** @var string|array */
    protected $requestHandler;

    /** @var array */
    protected $params = [];

    /** @var array */
    protected $placeholders = [
        ':seg' => '([^\/]+)',
        ':num' => '([0-9]+)',
        ':any' => '(.+)',
        ':rest' => '(\bget\b|\bpost\b|\bdelete\b|\bput\b)',
        ':token' => '([0-9]{4,30}[-]{1}[-0-9a-z]{4,32}[-]{1}[-0-9a-z]{3,20})'
    ];

    /** @var Request */
    private $request;


    public function __construct()
    {
        $this->request = new Request();

        $this->requestMethod = $this->request->getRequestMethod();
        $this->requestUrl = $this->request->getRequestUrl();
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @return string|array
     */
    public function getRequestHandler()
    {
        return $this->requestHandler;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array|string $route
     * @param mixed $handler
     * @return self
     */
    public function add($route, $handler = null): self
    {
        if ($handler !== null and !is_array($route)) {
            if (is_array($handler)) {
                foreach ($handler as $requestMethod => $methodCall) {
                    if (!$this->request->isMethodAcepted($requestMethod)) {
                        throw new \InvalidArgumentException("Method '$requestMethod' not accepted", 405);
                    }

                    $this->isHandlerExist($methodCall);
                }
            } else
                $this->isHandlerExist($handler);


            if (isset($this->routes[$route]) and (!is_array($this->routes[$route]) or (is_array($this->routes[$route]) and is_string($handler)))) {
                throw new \Exception("Route '$route' already exist", 400);
            }

            if (is_array($this->routes[$route]) and is_array($handler))
                foreach ($handler as $requestMethod => $methodCall) {
                    if (isset($this->routes[$route][$requestMethod])) {
                        throw new \Exception("Route '$route' with '$handler' method already exist", 400);
                    }
                }

            $route = [$route => $handler];
        }

        $this->routes = array_merge($this->routes, $route);
        return $this;
    }

    /**
     * @param array|string $route
     * @param mixed $handler
     * @return self
     */
    public function del($route, $handler = null): self
    {
        if (!$this->routes[$route]) {
            return $this;
        }

        if ($handler === null or !is_array($handler)) {
            unset($this->routes[$route]);
            return $this;
        }

        foreach ($handler as $requestMethod) {
            if (isset($this->routes[$route][$requestMethod])) {
                unset($this->routes[$route][$requestMethod]);
            }
        }

        if (isset($this->routes[$route]) and is_array($this->routes[$route]) and !count($this->routes[$route])) {
            unset($this->routes[$route]);
        }
        return $this;
    }

    /**
     * @param string|null $requestUrl
     * @return boolean
     */
    public function isFound(?string $requestUrl = null): bool
    {
        if (!$requestUrl) {
            $requestUrl = $this->request->getRequestUrl();
        }

        if (isset($this->routes[$requestUrl])) {
            $this->requestHandler = $this->routes[$requestUrl];
            return true;
        }

        $find = array_keys($this->placeholders);
        $replace = array_values($this->placeholders);

        foreach ($this->routes as $route => $handler) {
            if (strpos($route, ':') !== false) {
                $route = str_replace($find, $replace, $route);
            }

            if (preg_match('#^' . $route . '$#', $requestUrl, $matches)) {
                $this->requestHandler = $handler;
                $this->params = array_slice($matches, 1);
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $handler
     * @param string|null $method
     * @return string|null
     */
    public function handlerToLink($handler, $method = null)
    {
        foreach ($this->getRoutes() as $link => $handlers) {
            if (is_array($handlers)) {
                foreach ($handlers as $req => $handlr) {
                    if (($method === null or $req === $method) and $handlr === $handler) {
                        return $link;
                    }
                }
            } else {
                if ($handlers === $handler) {
                    return $link;
                }
            }
        }

        return null;
    }

    /**
     * @param mixed $handler
     * @param mixed $params
     * @return mixed
     */
    public function executeHandler($handler = null, $params = null)
    {
        if ($handler === null) {
            throw new \InvalidArgumentException(
                'Request handler not setted out. Please check ' . __CLASS__ . '::isFound() first'
            );
        }

        if (is_array($handler)) {
            $handler = $this->restMethodCheck($handler);
        }

        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }

        if (strpos($handler, '@')) {
            $restAction = $this->explodeHandler($handler);
            $method = $restAction[0];
            $action = $restAction[1];

            $method = Methods::installMethod($method);
            return Methods::callMethod($method, $action, $params);
        }
    }

    /**
     * @param mixed $requestHandler
     * @return mixed
     */
    private function restMethodCheck($requestHandler)
    {
        $request = new Request();
        $request->getRequestMethod();

        $requestHandler = @$requestHandler[$request->getRequestMethod()];

        if (!$requestHandler) {
            throw new \InvalidArgumentException('Method "' . $request->getRequestMethod() . '" not accepted', 405);
        }

        return $requestHandler;
    }

    /**
     * @param string $handler
     * @return boolean
     */
    private function isHandlerExist($handler)
    {
        $restAction = $this->explodeHandler($handler);
        $method = $restAction[0];
        $action = $restAction[1];

        if (!class_exists($method)) {
            throw new \RuntimeException("Class '{$method}' not found", 500);
        }
        if (!method_exists($method, $action)) {
            throw new \RuntimeException("Method '{$method}::{$action}' not found", 500);
        }
    }

    /**
     * @param string $handler
     * @return array
     */
    private function explodeHandler($handler)
    {
        $restAction = @explode('@', $handler);

        if (!is_array($restAction) or count($restAction) < 2) {
            throw new \RuntimeException("Route handler cannot be processed", 500);
        }

        return $restAction;
    }
}
