<?php
namespace AnnAuthorize\Test\Lib;

use AnnAuthorize\Lib\AnnAuthorization;
use AnnAuthorize\Test\Fixture\UsersFixture;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use TestApp\Controller\AnnAuthorizeTestController;
use TestApp\Model\Table\UsersTable;

/**
 * Test class for AnnAuthorization library.
 * @author Michael
 * @since v0.1
 */
class AnnAuthorizationTest extends TestCase
{
    public $fixtures = ['plugin.ann_authorize.users'];

    /**
     * @var AnnAuthorizeTestController
     */
    private $controller;

    /**
     * @var AnnAuthorization
     */
    private $AnnAuthorization;

    public function setUp() {
        parent::setUp();
        $this->controller = new AnnAuthorizeTestController();
        $this->AnnAuthorization = AnnAuthorization::getInstance();
    }

    public function testAuthorizeRequestAllowed() {
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersFixture::DEFAULT_USER_ID, $this->controller, 'allowedAction', [], new ServerRequest()));
    }

    public function testAuthorizeRequestLoggedIn() {
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersFixture::DEFAULT_USER_ID, $this->controller, 'loggedInAction', [], new ServerRequest()));
    }

    public function testAuthorizeRequestDeniedLoggedInIfNotLoggedIn() {
        $this->assertFalse($this->AnnAuthorization->authorizeRequest(null, $this->controller, 'loggedInAction', [], new ServerRequest()));
    }

    public function testAuthorizeRequestDeniedUserRule() {
        $this->assertFalse($this->AnnAuthorization->authorizeRequest(UsersFixture::DEFAULT_USER_ID, $this->controller, 'userRuleAction', [], new ServerRequest()));
    }

    public function testAuthorizeRequestDeniedUserRuleNotLoggedIn() {
        $this->assertFalse($this->AnnAuthorization->authorizeRequest(null, $this->controller, 'userRuleAction', [], new ServerRequest()));
    }

    public function testAuthorizeRequestAllowedUserRule() {
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'userRuleAction', [], new ServerRequest()));
    }

    public function testAuthorizeRequestDeniedControllerRule() {
        $this->assertFalse($this->AnnAuthorization->authorizeRequest(UsersFixture::DEFAULT_USER_ID, $this->controller, 'controllerRuleAction', [], new ServerRequest()));
    }

    public function testAuthorizeRequestAllowedControllerRule() {
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'controllerRuleAction', [], new ServerRequest()));
    }

    public function testAuthorizeRequestDeniedTableRule() {
        $this->assertFalse($this->AnnAuthorization->authorizeRequest(UsersFixture::DEFAULT_USER_ID, $this->controller, 'tableRuleAction', [], new ServerRequest()));
    }

    public function testAuthorizeRequestDeniedTableRuleNotLoggedIn() {
        $this->assertFalse($this->AnnAuthorization->authorizeRequest(null, $this->controller, 'tableRuleAction', [], new ServerRequest()));
    }

    public function testAuthorizeRequestAllowedTableRule() {
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'tableRuleAction', [], new ServerRequest()));
    }

    public function testAuthorizeRequestWithControllerName() {
        $this->assertFalse($this->AnnAuthorization->authorizeRequest(UsersFixture::DEFAULT_USER_ID, 'ann_authorize_test', 'controllerRuleAction', [], new ServerRequest()));
    }

    /**
     * @expectedException AnnAuthorize\Lib\AnnAuthorizationException
     */
    public function testAuthorizeRequestThrowsExceptionOnMissingController() {
        $this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, 'does_not_exist', 'doesNotMatter', [], new ServerRequest());
    }
    /**
     * @expectedException AnnAuthorize\Lib\AnnAuthorizationException
     */
    public function testAuthorizeRequestThrowsExceptionOnMissingTable() {
        $this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'missingTableAction', [], new ServerRequest());
    }

    /**
     * @expectedException AnnAuthorize\Lib\AnnAuthorizationException
     */
    public function testAuthorizeRequestThrowsExceptionOnInexistentRuleMethod() {
        $this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'inexistentRuleMethodAction', [], new ServerRequest());
    }

    /**
     * @expectedException AnnAuthorize\Lib\AnnAuthorizationException
     */
    public function testAuthorizeRequestThrowsExceptionOnInvalidPrefix() {
        $this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'invalidPrefixAction', [], new ServerRequest());
    }

    public function testAuthorizeRequestWithParams() {
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'ruleWithParamAction', ['test1'], new ServerRequest()));
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'ruleWithParamsAction', ['test1', 'test2'], new ServerRequest()));
        $request = new ServerRequest();
        $request->param('key1', 'test1');
        $request->param('key2', 'test2');
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'ruleWithReqAction', ['test1', 'test2'], $request));
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'ruleWithReqsAction', ['test1', 'test2'], $request));
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'ruleWithMixedParamsAction', ['test1', 'test2'], $request));
    }

    public function testSameRuleMultipleTimesAction() {
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'sameRuleMultipleTimesAction', ['test2'], new ServerRequest()));
    }

    /**
     * @expectedException AnnAuthorize\Lib\AnnAuthorizationException
     */
    public function testAuthorizeRequestThrowsExceptionOnMissingPassParam() {
        $this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'ruleWithParamAction', [], new ServerRequest());
    }

    /**
     * @expectedException AnnAuthorize\Lib\AnnAuthorizationException
     */
    public function testAuthorizeRequestThrowsExceptionOnMissingRequestParam() {
        $this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'ruleWithReqAction', [], new ServerRequest());
    }

    public function testGetAllowedActions() {
        $allowedActions = $this->AnnAuthorization->getAllowedActions($this->controller);
        $this->assertEquals(['allowedAction'], $allowedActions);
    }
}
