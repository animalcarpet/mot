<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\DocumentService;

class DocumentController extends BaseTestSupportRestfulController
{
    const FIELD_MOT_TEST_NUMBER = 'mot_test_number';

    public function get($id)
    {
        return TestDataResponseHelper::jsonOk($this->getDocumentService()->get($id));
    }

    public function delete($id)
    {
        return TestDataResponseHelper::jsonOk($this->getDocumentService()->delete($id));
    }

    public function getList()
    {
        $params = $this->params()->fromQuery();
        switch (true) {
            case array_key_exists(self::FIELD_MOT_TEST_NUMBER, $params):
                $result = $this->getDocumentService()->getByMotTestNumber($params[self::FIELD_MOT_TEST_NUMBER]);
                break;
            default:
                $result = [];
                break;
        }

        return TestDataResponseHelper::jsonOk($result);
    }

    /**
     * @return DocumentService
     */
    private function getDocumentService()
    {
        return $this->getServiceLocator()->get(DocumentService::class);
    }
}
