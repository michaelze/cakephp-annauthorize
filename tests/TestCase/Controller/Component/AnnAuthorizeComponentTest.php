<?php
namespace AnnAuthorize\Test\Controller\Component;

use Cake\TestSuite\TestCase;
use AnnAuthorize\Controller\Component\AnnAuthorizeComponent;
use TestApp\Controller\AnnAuthorizeTestController;

class AnnAuthorizeComponentTest extends TestCase {

    public function testAllAllowedActionsAreAllowedOnAuthComponent() {
        $component = new AnnAuthorizeComponent($this->mockComponentRegistry());
        $this->assertEquals(['allowedAction'], $component->Auth->allowedActions);
    }

    private function mockComponentRegistry() {
        $componentRegistryMock = $this->getMockBuilder('Cake\Controller\ComponentRegistry')->setMethods(['getController'])->getMock();
        $componentRegistryMock->expects($this->any())->method('getController')->will($this->returnValue(new AnnAuthorizeTestController()));
        return $componentRegistryMock;
    }

}