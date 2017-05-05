<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace AccountApi\Controller;

use AccountApi\Service\SecurityQuestionService;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Exception\InvalidFieldValueException;
use DvsaCommonApi\Service\Exception\MethodNotAllowedException;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;

/**
 * Class SecurityQuestionController
 * @package AccountApi\Controller
 */
class SecurityQuestionController extends AbstractDvsaRestfulController
{
    /** @var SecurityQuestionService */
    protected $securityQuestionService;

    public function __construct(SecurityQuestionService $securityQuestionService)
    {
        $this->securityQuestionService = $securityQuestionService;
    }

    /**
     * This endpoint is used to retrieve all the question
     *
     * @return JsonModel
     */
    public function getList()
    {
        return ApiResponse::jsonOk($this->securityQuestionService->getAll());
    }

    /**
     * @param int $userId
     * @param array $data
     * @return JsonModel
     */
    public function update($userId, $data)
    {
        $this->securityQuestionService->updateAnswersForUser($userId, $data);

        return ApiResponse::jsonOk($data);
    }

    /**
     * This endpoint is used to verify the correctness of an answer to a users
     * security question.
     *
     * It returns 200 if the answer is correct or 401 otherwise.
     *
     * @return JsonModel
     */
    public function verifyAnswerAction()
    {
        $questionId = (int)$this->params()->fromRoute('qid', 0);
        $userId = (int)$this->params()->fromRoute('uid', 0);
        $answer = $this->getRequest()->getQuery('answer', '');

        return ApiResponse::jsonOk($this->securityQuestionService->isAnswerCorrect($questionId, $userId, $answer));
    }


    /**
     * This endpoint is used to obtain a question for a specific user
     * and question index.
     *
     * @return JsonModel
     */
    public function getQuestionForPersonAction()
    {
        $questionId = (int)$this->params()->fromRoute('qid', 0);
        $userId = (int)$this->params()->fromRoute('uid', 0);

        return ApiResponse::jsonOk($this->securityQuestionService->findQuestionByQuestionNumber($questionId, $userId));
    }

    /**
     * @return JsonModel
     * @throws MethodNotAllowedException
     */
    public function getQuestionsForPersonAction()
    {
        if (Request::METHOD_GET != $this->getRequest()->getMethod()) {
            throw new MethodNotAllowedException();
        }

        $personId = $this->params()->fromRoute('personId');

        return ApiResponse::jsonOk($this->securityQuestionService->getQuestionsForPerson($personId));
    }

    /**
     * @return JsonModel
     * @throws InvalidFieldValueException
     * @throws MethodNotAllowedException
     * @throws RequiredFieldException
     */
    public function verifyAnswersAction()
    {
        if (Request::METHOD_POST != $this->getRequest()->getMethod()) {
            throw new MethodNotAllowedException();
        }

        $personId = $this->params()->fromRoute('personId');
        $questionsAndAnswers = Json::decode($this->request->getContent(), Json::TYPE_ARRAY)['questionsAndAnswers'];

        if (empty($questionsAndAnswers)) {
            throw new RequiredFieldException(['questionsAndAnswers']);
        }

        try {
            return ApiResponse::jsonOk(
                $this->securityQuestionService->verifySecurityAnswersForPerson($personId, $questionsAndAnswers)
            );
        } catch (\InvalidArgumentException $e) {
            throw new InvalidFieldValueException($e->getMessage());
        }
    }
}
