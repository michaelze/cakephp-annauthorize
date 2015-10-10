<?php
namespace AnnAuthorize\Test\Lib;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

use AnnAuthorize\Lib\AnnAuthorization;
use AnnAuthorize\Test\Fixture\UsersFixture;

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
        $this->controller = new AnnAuthorizeTestController();
        $this->AnnAuthorization = new AnnAuthorization();
    }

    public function testAuthorizeRequestAllowed() {
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersFixture::DEFAULT_USER_ID, $this->controller, 'allowedAction', []));
    }

    public function testAuthorizeRequestLoggedIn() {
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersFixture::DEFAULT_USER_ID, $this->controller, 'loggedInAction', []));
    }

    public function testAuthorizeRequestDeniedLoggedInIfNotLoggedIn() {
        $this->assertFalse($this->AnnAuthorization->authorizeRequest(null, $this->controller, 'loggedInAction', []));
    }

    public function testAuthorizeRequestDeniedUserRule() {
        $this->assertFalse($this->AnnAuthorization->authorizeRequest(UsersFixture::DEFAULT_USER_ID, $this->controller, 'userRuleAction', []));
    }

    public function testAuthorizeRequestAllowedUserRule() {
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'userRuleAction', []));
    }

    public function testAuthorizeRequestDeniedControllerRule() {
        $this->assertFalse($this->AnnAuthorization->authorizeRequest(UsersFixture::DEFAULT_USER_ID, $this->controller, 'controllerRuleAction', []));
    }

    public function testAuthorizeRequestAllowedControllerRule() {
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'controllerRuleAction', []));
    }

    public function testAuthorizeRequestDeniedTableRule() {
        TableRegistry::set('UsersTable', new UsersTable());
        $this->assertFalse($this->AnnAuthorization->authorizeRequest(UsersFixture::DEFAULT_USER_ID, $this->controller, 'tableRuleAction', []));
    }

    public function testAuthorizeRequestAllowedTableRule() {
        TableRegistry::set('UsersTable', new UsersTable());
        $this->assertTrue($this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'tableRuleAction', []));
    }

    public function testAuthorizeRequestWithControllerName() {
        $this->assertFalse($this->AnnAuthorization->authorizeRequest(UsersFixture::DEFAULT_USER_ID, 'ann_authorize_test', 'controllerRuleAction', []));
    }

    /**
     * @expectedException AnnAuthorize\Lib\AnnAuthorizationException
     */
    public function testAuthorizeRequestThrowsExceptionOnMissingController() {
        $this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, 'does_not_exist', 'doesNotMatter', []);
    }
    /**
     * @expectedException AnnAuthorize\Lib\AnnAuthorizationException
     */
    public function testAuthorizeRequestThrowsExceptionOnMissingTable() {
        $this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'missingTableAction', []);
    }

    /**
     * @expectedException AnnAuthorize\Lib\AnnAuthorizationException
     */
    public function testAuthorizeRequestThrowsExceptionOnInexistentRuleMethod() {
        $this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'inexistentRuleMethodAction', []);
    }

    /**
     * @expectedException AnnAuthorize\Lib\AnnAuthorizationException
     */
    public function testAuthorizeRequestThrowsExceptionOnInvalidPrefix() {
        $this->AnnAuthorization->authorizeRequest(UsersTable::SUPERADMIN_ID, $this->controller, 'invalidPrefixAction', []);
    }

    public function testGetAllowedActions() {
        $allowedActions = $this->AnnAuthorization->getAllowedActions($this->controller);
        $this->assertEquals(['allowedAction'], $allowedActions);
    }
}
