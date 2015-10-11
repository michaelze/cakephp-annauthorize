<?php
namespace AnnAuthorize\Test\Auth;

use Cake\TestSuite\TestCase;
use AnnAuthorize\Auth\AnnAuthorize;
use TestApp\Controller\AnnAuthorizeTestController;
use AnnAuthorize\Test\Fixture\UsersFixture;

class AnnAuthorizeTest extends TestCase {

    private $annAuthorize;

    public function setUp() {
        parent::setUp();
        $this->annAuthorize = new AnnAuthorize($this->mockComponentRegistry());
    }

    public function testAllowedActionIsAllowed() {
        $authorized = $this->annAuthorize->authorize(['id' => UsersFixture::DEFAULT_USER_ID, 'username' => 'asdf'], $this->mockRequest('loggedInAction', []));
        $this->assertTrue($authorized);
    }

    private function mockComponentRegistry() {
        $componentRegistryMock = $this->getMockBuilder('Cake\Controller\ComponentRegistry')->setMethods(['getController'])->getMock();
        $componentRegistryMock->expects($this->any())->method('getController')->will($this->returnValue(new AnnAuthorizeTestController()));
        return $componentRegistryMock;
    }

    private function mockRequest($action, $pass) {
        $requestMock = $this->getMockBuilder('Cake\Network\Request')->setMethods(['param'])->getMock();
        $requestMock->expects($this->any())->method('param')->withConsecutive(['action'], ['pass'])->willReturnOnConsecutiveCalls($action, $pass);
        return $requestMock;
    }
}