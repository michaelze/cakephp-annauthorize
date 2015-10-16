<?php
namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

use TestApp\Model\Table\UsersTable;

class User extends Entity {

    public function superadminRule($userId) {
        return $userId == UsersTable::SUPERADMIN_ID;
    }

}