<?php
namespace TestApp\Model\Table;

use Cake\ORM\Table;

class UsersTable extends Table {

    const SUPERADMIN_ID = 'superadmin';

    public function testRule(string $userId) {
        return $userId == self::SUPERADMIN_ID;
    }

}