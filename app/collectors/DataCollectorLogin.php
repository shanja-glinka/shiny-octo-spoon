<?php

namespace app\collectors;

use app\utils\DataCollector;

class DataCollectorLogin extends DataCollector
{
    protected function collectRequestDatas()
    {
        $this->addExtractedDatas(['appVersion' => $this->getAppVersion()]);

        if (isset($this->extractedRequestData['data']) and is_string($this->extractedRequestData['data'])) {
            $this->extractedRequestData['data'] = @json_decode($this->extractedRequestData['data'], true);
        }

        $this->tryToCorrectTypes();

        return $this->extractedRequestData;
    }


    private function getAppVersion()
    {
        $httpAppVersion = 'HTTP_' . strtoupper('appVersion');

        return isset($_SERVER[$httpAppVersion]) ? $_SERVER[$httpAppVersion] : null;
    }

    private function tryToCorrectTypes()
    {
        $dataVal = $this->extractedRequestData['data'];

        if (isset($this->extractedRequestData['email'])) {
            $this->emailValidate($this->extractedRequestData['email']);
        }

        if (isset($this->extractedRequestData['name'])) {
            $this->extractedRequestData['name'] = preg_replace('/\s\s+/', ' ', $this->extractedRequestData['name']);
        }

        if (isset($dataVal['userId'])) {
            $this->extractedRequestData['data']['userId'] = $this->tryParseToInt($dataVal['userId']);
        }

        if (isset($dataVal['screenHeight'])) {
            $this->extractedRequestData['data']['screenHeight'] = $this->tryParseToInt($dataVal['screenHeight']);
        }

        if (isset($dataVal['screenWIdth'])) {
            $this->extractedRequestData['data']['screenWIdth'] = $this->tryParseToInt($dataVal['screenWIdth']);
        }
    }
}
