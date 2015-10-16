<?php
namespace TestApp\Controller;

use Cake\Controller\Controller;

use TestApp\Model\Table\UsersTable;

/**
 * This controller class is used to test the AnnAuthorization library.
 */
class AnnAuthorizeTestController extends Controller {
    /**
     * @auth:allowed()
     */
    public function allowedAction() {}
    /**
     * @auth:loggedIn()
     */
    public function loggedInAction() {}
    /**
     * @auth:User.superadmin()
     */
    public function userRuleAction() {}
    /**
     * @auth:Controller.superadmin()
     */
    public function controllerRuleAction() {}
    /**
     * @auth:Table.UsersTable.test()
     */
    public function tableRuleAction() {}
    /**
     * @auth:Table.DoesNotExist.asdf()
     */
    public function missingTableAction() {}
    /**
     * @auth:Controller.doesNotExist()
     */
    public function inexistentRuleMethodAction() {}
    /**
     * @auth:DoesNotExist.something()
     */
    public function invalidPrefixAction() {}
    public function superadminRule($userId) {
        return $userId == UsersTable::SUPERADMIN_ID;
    }
}
