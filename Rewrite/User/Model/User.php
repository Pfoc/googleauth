<?php

namespace Pfoc\GoogleAuth\Rewrite\User\Model;

class User extends \Magento\User\Model\User
{

    public function loginByGoogle($email)
    {
        if ($this->authenticateByGoogle($email)) {
            $this->getResource()->recordLogin($this);
        }
        return $this;
    }

    public function authenticateByGoogle($email)
    {
        $result = false;
        try {
            $user = $this->loadByEmail($email);

            $this->_eventManager->dispatch(
                'admin_user_authenticate_before',
                ['username' => $user['username'], 'user' => $this]
            );

            $result = true;
            $this->_eventManager->dispatch(
                'admin_user_authenticate_after',
                ['username' => $user['username'], 'password' => null, 'user' => $this, 'result' => true]
            );

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->unsetData();
            throw $e;
        }

        if (!$result) {
            $this->unsetData();
        }

        return $result;
    }

    public function loadByEmail($email)
    {
        $data = $this->getResource()->loadByEmail($email);
        if ($data !== false) {
            $this->setData($data);
            $this->setOrigData();
        }
        return $data;
    }
}