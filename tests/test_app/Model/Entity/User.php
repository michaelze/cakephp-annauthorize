<?php
namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

use TestApp\Model\Table\UsersTable;

class User extends Entity {

    public function superadminRule(string $userId) {
        return $userId == UsersTable::SUPERADMIN_ID;
    }

}