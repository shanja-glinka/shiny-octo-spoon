<?php

namespace app\utils;

use \Exception;

abstract class DataCollector implements DataCollectorinterface
{
    /** @var array */
    protected $originRequestData = [];

    /** @var array */
    protected $extractedRequestData = [];


    /**
     * @param array $requestData
     */
    public function __construct($requestData)
    {
        $this->originRequestData = $requestData;

        foreach ($this->originRequestData as $var => &$val) {
            if (gettype($val) === 'string') {
                StringUtils::strTrim($val);
            }
        }
    }

    public function __destruct()
    {
        $this->removeUploadFiles();
    }


    /**
     * Data extraction start function
     *
     * @return array
     */
    public function extract()
    {
        $this->addExtractedDatas($this->originRequestData);
        $this->addExtractedDatas(['files' => FilesUpload::upload('files', 'uploads/files/')]);

        $res = $this->collectRequestDatas();
        return $res ?? $this->extractedRequestData;
    }






    /**
     * Collects data from the query array
     *
     * @return array
     */
    protected abstract function collectRequestDatas();


    /**
     * Adds the processed data to the data array
     *
     * @param array $data
     * @return self
     */
    protected function addExtractedDatas(array $data): self
    {
        $this->extractedRequestData = array_merge($this->extractedRequestData, $data);

        return $this;
    }

    /**
     * Trying to convert a string to a number
     *
     * @param mixed $value
     * @return void
     */
    protected function tryParseToInt($value)
    {
        return ctype_digit($value) ? intval($value) : $value;
    }

    /**
     * Mail validation
     *
     * @param string $email
     * @return void
     */
    protected function emailValidate($email)
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Incorrect e-mail format', 400);
        }
    }

    /**
     * Phone number validation
     *
     * @param string $phone
     * @param string $phoneCode
     * @return string
     */
    protected function phoneValidate($phone, $phoneCode = '7'): ?string
    {
        if (empty($phone)) {
            return '';
        }

        $formattedPhone = preg_replace("@[^\d]+@", '', $phone);

        if (strlen($formattedPhone) == 10) {
            $formattedPhone = $phoneCode . $formattedPhone;
        }

        if (strlen($formattedPhone) < 10) {
            throw new Exception('Invalid Phone Format', 400);
        }

        $formattedPhone = preg_replace("@^8@", $phoneCode, $formattedPhone);

        if ($formattedPhone && strlen($formattedPhone) === 11) {
            $formattedPhone = '+' . mb_substr($formattedPhone, 0, 1) . ' (' . mb_substr($formattedPhone, 1, 3) . ') ' . mb_substr($formattedPhone, 4, 3) . ' ' . mb_substr($formattedPhone, 7, 4);
        } else {
            throw new Exception('Invalid Phone Format', 400);
        }

        return $formattedPhone;
    }


    /**
     * File system cleaner
     *
     * @return void
     */
    private function removeUploadFiles()
    {
        if (!isset($this->extractedRequestData['files'])) {
            return;
        }

        $filesAttach = $this->extractedRequestData['files'];

        if (!is_array($filesAttach)) {
            return;
        }

        foreach ($filesAttach as $filename) {
            unlink($filename);
        }
    }
}
