<?php

namespace Pfoc\GoogleAuth\Rewrite\User\Model\ResourceModel;

class User extends \Magento\User\Model\ResourceModel\User
{

    public function loadByEmail($email)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable())->where('email=:email');
        $binds = ['email' => $email];
        return $connection->fetchRow($select, $binds);
    }
}