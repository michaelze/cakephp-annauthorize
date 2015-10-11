<?php
namespace AnnAuthorize\Test\View\Helper;

use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;

use AnnAuthorize\Test\Fixture\UsersFixture;
use AnnAuthorize\View\Helper\AnnAuthorizeHelper;

/**
 * Test class for the AnnAuthorizeHelper.
 */
class AnnAuthorizeHelperTest extends TestCase {

    private $annAuthorizeHelper;

    public function setUp() {
        parent::setUp();
        Router::connect('/ann-authorize-test/allowedAction', ['controller' => 'AnnAuthorizeTest', 'action' => 'allowedAction']);
        Router::connect('/ann-authorize-test/loggedInAction', ['controller' => 'AnnAuthorizeTest', 'action' => 'loggedInAction']);
        $this->annAuthorizeHelper = new AnnAuthorizeHelper(new View());
    }

    public function testAllowedActionLinkIsCreated() {
        $link = $this->annAuthorizeHelper->link('some title', ['controller' => 'AnnAuthorizeTest', 'action' => 'allowedAction']);
        $this->assertNotFalse($link);
        $this->assertRegExp('/href="\/ann-authorize-test\/allowedAction"/', $link);
    }

    public function testLoggedInActionIsAllowedWhenLoggedIn() {
        $this->annAuthorizeHelper->request = $this->getRequestMock(UsersFixture::DEFAULT_USER_ID);
        $link = $this->annAuthorizeHelper->link('some title', ['controller' => 'AnnAuthorizeTest', 'action' => 'loggedInAction']);
        $this->assertNotFalse($link);
        $this->assertRegExp('/href="\/ann-authorize-test\/loggedInAction"/', $link);
    }

    public function testLoggedInActionIsNotAllowedWhenNotLoggedIn() {
        $this->annAuthorizeHelper->request = $this->getRequestMock(null);
        $link = $this->annAuthorizeHelper->link('some title', ['controller' => 'AnnAuthorizeTest', 'action' => 'loggedInAction']);
        $this->assertFalse($link);
    }

    /**
     * Creates a request mock whose session will simulate the user with the provided id being logged in.
     * @param string $userId
     *          Provide the id of the user that is currently logged in, or null if no user is logged in at the moment.
     * @return \Cake\Network\Request Returns the mocked request.
     */
    private function getRequestMock($userId) {
        $sessionMock = $this->getMockBuilder('Cake\Network\Session')->setMethods(['read'])->getMock();
        $requestMock = $this->getMockBuilder('Cake\Network\Request')->setMethods(['session'])->getMock();
        $sessionMock->expects($this->any())->method('read')->with($this->equalTo('Auth.User.id'))->will($this->returnValue($userId));
        $requestMock->expects($this->any())->method('session')->will($this->returnValue($sessionMock));
        return $requestMock;
    }
}