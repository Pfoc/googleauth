<?php

namespace Pfoc\GoogleAuth\Model;

class Auth
{

    /** @var \Magento\Security\Model\AdminSessionsManager */
    protected $adminSessionsManager;

    /** @var \Magento\Backend\Model\Auth\StorageInterface */
    protected $authStorage;

    /** @var \Magento\Backend\Model\Auth\Credential\StorageInterface */
    protected $credentialStorage;

    /** @var \Magento\Framework\Data\Collection\ModelFactory */
    protected $modelFactory;

    /** @var \Magento\Framework\Event\ManagerInterface */
    protected $eventManager;

    /** @var \Magento\Framework\Stdlib\CookieManagerInterface */
    protected $cookieManager;

    /** @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory */
    protected $cookieMetadataFactory;

    /** @var \Magento\Framework\Session\SessionManagerInterface */
    protected $sessionManager;

    /** @var \Magento\Framework\Session\Config\ConfigInterface */
    protected $sessionConfig;

    /** @var \Magento\Backend\App\ConfigInterface */
    protected $config;


    public function __construct(
        \Magento\Security\Model\AdminSessionsManager $adminSessionsManager,
        \Magento\Backend\Model\Auth\StorageInterface $authStorage,
        \Magento\Backend\Model\Auth\Credential\StorageInterface $credentialStorage,
        \Magento\Framework\Data\Collection\ModelFactory $modelFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Backend\App\ConfigInterface $config)
    {
        $this->adminSessionsManager = $adminSessionsManager;
        $this->authStorage = $authStorage;
        $this->credentialStorage = $credentialStorage;
        $this->modelFactory = $modelFactory;
        $this->eventManager = $eventManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
        $this->sessionConfig = $sessionConfig;
        $this->config = $config;
    }

    public function loginByGoogle($email): void
    {
        if (empty($email)) {
            throw new \Exception(
                __('You did not sign in correctly or your account is temporarily disabled.')
            );
        }

        try {
            $this->initCredentialStorage();
            $this->getCredentialStorage()->loginByGoogle($email);
            if ($this->getCredentialStorage()->getId()) {
                $this->getAuthStorage()->setName("admin");
                $this->getAuthStorage()->setUser($this->getCredentialStorage());
                $this->getAuthStorage()->processLogin();
                $this->eventManager->dispatch(
                    'backend_auth_user_login_success',
                    ['user' => $this->getCredentialStorage()]
                );

                $this->adminSessionsManager->processLogin();
                $this->createCookie("admin", $this->getAuthStorage()->getSessionId());
                $this->getAuthStorage()->refreshAcl();
            }
            if (!$this->getAuthStorage()->getUser()) {
                throw new \Exception(__('You did not sign in correctly or your account is temporarily disabled.'));
            }
        } catch (PluginAuthenticationException $e) {
            $this->eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => null, 'exception' => $e]
            );
            throw $e;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => null, 'exception' => $e]
            );
            throw new \Exception(
                __('You did not sign in correctly or your account is temporarily disabled.')
            );
        }
    }

    protected function initCredentialStorage(): void
    {
        $this->credentialStorage = $this->modelFactory->create(
            \Magento\Backend\Model\Auth\Credential\StorageInterface::class
        );
    }

    public function getCredentialStorage(): \Magento\Backend\Model\Auth\Credential\StorageInterface
    {
        return $this->credentialStorage;
    }

    public function getAuthStorage(): \Magento\Backend\Model\Auth\StorageInterface
    {
        return $this->authStorage;
    }

    private function createCookie($name, $value): void
    {
        $lifetime = $this->config->getValue(\Magento\Backend\Model\Auth\Session::XML_PATH_SESSION_LIFETIME);
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($lifetime)
            ->setPath("/admin")
            ->setDomain($this->sessionManager->getCookieDomain())
            ->setSecure($this->sessionConfig->getCookieSecure())
            ->setHttpOnly($this->sessionConfig->getCookieHttpOnly());

        $this->cookieManager->setPublicCookie(
            $name,
            $value,
            $metadata
        );
    }

}