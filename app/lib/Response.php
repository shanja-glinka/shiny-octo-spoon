<?php

namespace app\lib;

use app\utils\Template;
use Exception;

class Response
{

    /** @var int */
    protected $codeResponse;

    /** @var string */
    protected $contentTypeResponse;

    /** @var bool */
    private $useFormatResponse;

    /** @var mixed */
    private $dataResponse;

    private $contentType = [
        'json' => 'Content-type: application/json; charset=UTF-8',
        'html' => 'Content-Type: text/html; charset=UTF-8',
        'text' => 'Content-Type: text/html; charset=UTF-8',
    ];


    /**
     * @param string $headerType
     * @param boolean $useFormatResponse
     */
    public function __construct(string $headerType = 'json', bool $useFormatResponse = true)
    {
        $this->setHtmlCode(200);
        $this->setContentType($headerType);
        $this->useFormatResponse($useFormatResponse);
    }

    /**
     * @param string $extension
     * @return self
     */
    public function setContentType(string $extension): self
    {
        if (array_key_exists($extension, $this->contentType)) {
            $this->contentTypeResponse = $extension;
        } else {
            throw new Exception('Unsupported Content-type: ' . $extension);
        }
        return $this;
    }

    /**
     * @param integer $htmlCode
     * @return self
     */
    public function setHtmlCode(int $htmlCode): self
    {
        $this->codeResponse = $htmlCode;
        return $this;
    }

    /**
     * @param integer $htmlCode
     * @return self
     */
    public function setCode(int $htmlCode): self
    {
        return $this->setHtmlCode($htmlCode);
    }

    /**
     * @param string|null $header
     * @return self
     */
    public function sendHeader(?string $header = null): self
    {
        if ($header !== null) {
            header($header);
        }

        return $this;
    }

    /**
     * @param int|null $code
     * @return self
     */
    public function sendCode(int $code = null): self
    {
        if ($code !== null) {
            http_response_code($code);
        }

        return $this;
    }

    /**
     * @param boolean $useit
     * @return void
     */
    public function useFormatResponse(bool $useit = true)
    {
        $this->useFormatResponse = $useit;
    }

    /**
     * @param mixed $data
     * @return self
     */
    public function send($data): self
    {
        $this->setDataResponse($data);

        if ($this->contentTypeResponse == 'json') {
            $this->withJson($data);
        } else {
            $this->withText($data);
        }

        $this->setDataResponse(null);

        return $this;
    }

    /**
     * @param mixed $data
     * @return void
     */
    public function setDataResponse($data)
    {
        $this->dataResponse = $data;
    }

    /**
     * @param mixed $data
     * @return void
     */
    public function withText($data = null)
    {
        $this->sendHeader($this->contentType[$this->contentTypeResponse]);
        $this->sendCode($this->codeResponse);
        $this->withData($data);
    }

    /**
     * @param mixed $data
     * @return void
     */
    public function withJson($data = null)
    {
        $this->sendCode($this->codeResponse);
        $this->sendHeader($this->contentType['json']);

        if (is_string($data)) {
            $data = ['message' => $data];
        }

        echo json_encode($data === null ? $this->dataResponse : $data);
    }

    /**
     * @param mixed $data
     * @return void
     */
    public function withData($data = null)
    {
        echo print_r($data === null ? $this->dataResponse : $data, true);
    }

    /**
     * @param \app\utils\TemplateData $templateData
     * @return void
     */
    public function withHtml($templateData)
    {
        if (!is_object($templateData)) {
            throw new Exception('Unsupported file Template');
        }

        $template = new Template();
        $this->setContentType('html')->withData($template->render($templateData));
    }

    /**
     * @param string $location
     * @return void
     */
    public function redirectTo(string $location)
    {
        $this->sendCode(301)->sendHeader('Location: ' . $location);
        exit;
    }
}
