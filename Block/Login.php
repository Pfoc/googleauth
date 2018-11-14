<?php

namespace Pfoc\GoogleAuth\Block;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Pfoc\GoogleAuth\Helper\Config;

class Login extends Template
{

    /** @var Config */
    protected $config;
    public function __construct(Context $context,
        Config $config)
    {
        parent::__construct($context);
        $this->config = $config;
    }

    public function isEnable()
    {
        return $this->config->isEnable();
    }

    public function getGoogleAuthUrl()
    {
        return "/googleauth/token/verify/"; //TODO:: use magento function to build url
    }

    public function getGoogleClientId()
    {
        return $this->config->getClientId();
    }
}