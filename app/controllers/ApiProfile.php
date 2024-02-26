<?php

namespace app\controllers;

use app\lib\Controllers;
use app\utils\DataCollectorinterface;
use app\utils\ProfileMap;

class ApiProfile extends Controllers
{

    public function __construct()
    {
        parent::__construct();
        $this->response->setContentType('json');
    }



    public function formReport()
    {
        return $this->response->withText(IssueReport::getReportForm());
    }

    public function jiraReport()
    {
        /** @var DataCollectorinterface */
        $reportData = new ProfileMap::$profile->dataCollector($this->request->val());

        $requestData = $reportData->extract();

        $result = IssueReport::jiraIssueReport($requestData);

        return $this->response->send(
            [
                'status' => true,
                'message' => null,
                'result' => $result
            ]
        );
    }
}
