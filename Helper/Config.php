<?php

namespace Pfoc\GoogleAuth\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Config extends AbstractHelper
{
    protected $scopeConfig;

    const GOOGLE_AUTH_ENABLE = 'google_auth/settings/enable';
    const GOOGLE_AUTH_CLIENT_ID = 'google_auth/settings/client_id';
    const GOOGLE_AUTH_SECRET_ID = 'google_auth/settings/secret_id';

    public function __construct(
        Context $context
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    public function isEnable()
    {
        return $this->scopeConfig->getValue(self::GOOGLE_AUTH_ENABLE);
    }

    public function getClientId()
    {
        return $this->scopeConfig->getValue(self::GOOGLE_AUTH_CLIENT_ID);
    }

    public function getSecretId()
    {
        return $this->scopeConfig->getValue(self::GOOGLE_AUTH_SECRET_ID);
    }
}