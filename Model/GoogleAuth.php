<?php

namespace Pfoc\GoogleAuth\Model;

use Pfoc\GoogleAuth\Helper\Config;
use Google\Apiclient\Google\Google_Client;

class GoogleAuth
{

    /** @var Config */
    private $config;

    /** @var string */
    private $email;

    /** @var string */
    private $name;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function verify($token): void
    {
        $client = new \Google_Client(['client_id' => $this->config->getClientId()]);
        $payload = $client->verifyIdToken($token);
        if ($payload) {
            $this->setName($payload['name']);
            $this->setEmail($payload['email']);
        } else {
            throw new \Exception(__("Authentication not correct"));
        }

    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}