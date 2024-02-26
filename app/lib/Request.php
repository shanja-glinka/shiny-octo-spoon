<?php

namespace app\lib;

use app\utils\StringUtils;
use Exception;

class Request
{

    /** @var string */
    protected string $requestUrl;

    /** @var string */
    protected string $requestMethod;

    /** @var mixed */
    protected mixed $get;

    /** @var mixed */
    protected mixed $post;

    /** @var array */
    protected array $acceptMethods;

    /** @var mixed */
    protected mixed $phpInput;

    /** @var string */
    protected string $defaltContentType;


    public function __construct()
    {
        $this->requestUrl = $this->getRequestUrl();
        $this->requestMethod = $this->getRequestMethod();
        $this->get = $this->requestVluesPrepare($_GET);
        $this->post = $this->requestVluesPrepare($_POST);
        $this->acceptMethods = ['GET', 'POST', 'PUT', 'DELETE'];

        $this->defaltContentType = 'json';

        $this->phpInput = null;

        $this->parseRequests();
    }

    /**
     * @param mixed $var
     * @return void
     */
    public function getBody($var = null)
    {
        return ($var == null ?
            $this->post
            : (isset($this->post[$var]) ? $this->post[$var] : null));
    }

    /**
     * @param mixed $var
     * @return void
     */
    public function getQuery($var = null)
    {
        return ($var == null
            ? $this->get
            : (isset($this->get[$var]) ? $this->get[$var] : null));
    }

    /**
     * @return string|null
     */
    public function getContentType(): ?string
    {
        if (!isset($_SERVER['CONTENT_TYPE'])) {
            return $this->defaltContentType;
        }

        switch ($_SERVER['CONTENT_TYPE']) {
            case 'application/json':
                return 'json';
            case 'form-data':
            case 'x-www-form-urlencoded':
            case 'text/html':
                return 'html';
            case 'application/xml':
                return 'xml';
            default:
                return 'html';
        }
    }

    /**
     *
     * @return boolean
     */
    public function isGet(): bool
    {
        if ($this->requestMethod === 'GET') {
            return true;
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function isPost(): bool
    {
        if ($this->requestMethod === 'POST') {
            return true;
        }

        return false;
    }

    /**
     * @param string $method
     * @return boolean
     */
    public function isMethodAcepted(string $method): bool
    {
        return in_array($method, $this->acceptMethods);
    }

    /**
     * @return string
     */
    public function getRequestMethod(): string
    {
        if (isset($this->requestMethod)) {
            return $this->requestMethod;
        }

        return filter_var(getenv('REQUEST_METHOD'));
    }

    /**
     * @return string
     */
    public function getRequestUrl(): string
    {
        if (isset($this->requestUrl)) {
            return $this->requestUrl;
        }

        $requestUrl = $_SERVER['REQUEST_URI'];
        if (strpos($requestUrl, '?')) {
            $requestUrl = substr($requestUrl, 0, strpos($requestUrl, '?'));
        }

        if ($requestUrl[strlen($requestUrl) - 1] == '/') {
            $requestUrl = substr($requestUrl, 0,  strlen($requestUrl) - 1);
        }

        return $requestUrl;
    }

    /**
     * @return string
     */
    public function getRequestUri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * @return string
     */
    public function getDomainName(): string
    {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * @return string
     */
    public function getDomainProtocol(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
        return $protocol;
    }

    /**
     * Request Value
     * 
     * @param string $valueName or NULL for get request datas
     * @param string $requestMethod or NULL for call $this->getRequestMethod()
     * 
     * @return any
     */
    public function val(?string $valueName = null, ?string $requestMethod = null)
    {
        if ($requestMethod === null) {
            $requestMethod = $this->getRequestMethod();
        }

        if (!isset($GLOBALS['__REQUEST_VARS'])) {
            $GLOBALS['__REQUEST_VARS'] = [];
            $GLOBALS['__REQUEST_VARS'] = json_decode(file_get_contents('php://input'), true);
        }

        $request = null;

        if ($this->getContentType() === 'json') {
            $request = &$GLOBALS['__REQUEST_VARS'];
        } else {
            switch ($requestMethod) {
                case 'GET':
                    $request = &$_GET;
                    break;
                case 'POST':
                    $request = &$_POST;
                    break;
                case 'DELETE':
                case 'PUT':
                case 'OPTIONS':
                case 'HEAD':
                    $request = &$_REQUEST;
                    break;
                default:
                    $request = null;
                    break;
            }
        }

        return ($request == null ? null : ($valueName == null ? $request : $request[$valueName]));
    }

    /**
     * @param null|string $valueName
     * @return boolean
     */
    public function issetGet(?string $valueName): bool
    {
        return isset($_GET[$valueName]);
    }

    /**
     * @param string $valueName
     */
    public function throwIfValueNotExist($valueName, $requestMethod = null): bool
    {
        if (is_array($valueName)) {
            return $this->throwIfValuesNotExist($valueName, $requestMethod);
        }

        if ($requestMethod === null) {
            $requestMethod = $this->getRequestMethod();
        }

        if ($this->val($valueName, $requestMethod) === null) {
            throw new \InvalidArgumentException("Value '$valueName' must be not empty", 400);
        }

        return true;
    }


    /**
     * @param array $valueName
     */
    public function throwIfValuesNotExist($valuesName, $requestMethod = null): bool
    {
        if (!is_array($valuesName)) {
            throw new Exception('Value is not exists', 500);
        }

        if ($requestMethod === null) {
            $requestMethod = $this->getRequestMethod();
        }

        foreach ($valuesName as $value) {
            if ($this->val($value, $requestMethod) === null) {
                throw new \InvalidArgumentException("Value '$value'  must be not empty", 400);
            }
        }

        return true;
    }


    /**
     * @param string $valueName
     */
    public function requireValue($valueName, $requestMethod = null): bool
    {
        return $this->throwIfValueNotExist($valueName, $requestMethod);
    }



    /**
     * @param [type] $s
     * @return string|array|null
     */
    private function requestVluesPrepare($s)
    {
        if (!is_null($s)) {
            $s = StringUtils::htmlChars($s);
            StringUtils::strTrim($s);
        }

        return $s;
    }

    /**
     * @return void
     */
    private function parseRequests()
    {
        parse_str(file_get_contents('php://input'), $request);

        foreach ($request as $key => $value) {
            unset($request[$key]);

            $request[str_replace('amp;', '', $key)] = $value;
        }

        $_REQUEST = array_merge($_REQUEST, $this->requestVluesPrepare($request));
    }
}
