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
    /**
     * @auth:Controller.ruleWithParam(pass[0])
     */
    public function ruleWithParamAction($id) {}
    /**
     * @auth:Controller.ruleWithParams(pass[0], pass[1])
     */
    public function ruleWithParamsAction($id, $test) {}
    /**
     * @auth:Controller.ruleWithParam(req[key1])
     */
    public function ruleWithReqAction($id) {}
    /**
     * @auth:Controller.ruleWithParams(req[key1], req[key2])
     */
    public function ruleWithReqsAction($id, $test) {}
    /**
     * @auth:Controller.ruleWithParams(req[key1], pass[1])
     */
    public function ruleWithMixedParamsAction($id, $test) {}
    public function superadminRule($userId) {
        return $userId == UsersTable::SUPERADMIN_ID;
    }
    public function ruleWithParamRule($userId, $param) {
        return $userId == UsersTable::SUPERADMIN_ID && $param == 'test1';
    }
    public function ruleWithParamsRule($userId, $param1, $param2) {
        return $userId == UsersTable::SUPERADMIN_ID && $param1 == 'test1' && $param2 == 'test2';
    }
}
