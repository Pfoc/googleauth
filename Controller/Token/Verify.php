<?php

namespace Pfoc\GoogleAuth\Controller\Token;

use Pfoc\GoogleAuth\Model\GoogleAuth;
use Pfoc\GoogleAuth\Model\Auth;

class Verify extends \Magento\Framework\App\Action\Action
{

    const HTTP_200 = "200";
    const HTTP_500 = "500";

    /** @var \Magento\Framework\App\Request\Http */
    protected $request;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    protected $resultJsonFactory;

    /** @var GoogleAuth */
    protected $googleAuth;

    /** @var Auth */
    protected $auth;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        GoogleAuth $googleAuth,
        Auth $auth)
    {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->googleAuth = $googleAuth;
        $this->auth = $auth;

        return parent::__construct($context);
    }

    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $token = $this->request->getPost('token');

            if (empty($token)) {
                $httpResponse = self::HTTP_500;
                $message = __("The token argument is missing");
            } else {
                try {
                    $this->googleAuth->verify($token);
                    $email = $this->googleAuth->getEmail();
                    $this->auth->loginByGoogle($email);
                    $httpResponse = self::HTTP_200;
                    $message = __("Authentication correct");
                } catch (\Exception $ex) {
                    $httpResponse = self::HTTP_500;
                    $message = __($ex->getMessage());
                }
            }

            $result = $this->resultJsonFactory->create();
            $data = [
                "message" => $message
            ];

            return $result
                ->setData($data)
                ->setHttpResponseCode($httpResponse);
        }
    }
}