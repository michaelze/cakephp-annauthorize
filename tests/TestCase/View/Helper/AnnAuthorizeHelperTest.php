<?php
namespace AnnAuthorize\Test\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;

use AnnAuthorize\View\Helper\AnnAuthorizeHelper;
use Cake\Routing\Router;

/**
 * Test class for the AnnAuthorizeHelper.
 */
class AnnAuthorizeHelperTest extends TestCase {
    
    public function testAllowedActionLinkIsCreated() {
        Router::connect('/ann-authorize-test/allowedAction', ['controller' => 'AnnAuthorizeTest', 'action' => 'allowedAction']);
        $helper = new AnnAuthorizeHelper(new View());
        $link = $helper->link('some title', ['controller' => 'AnnAuthorizeTest', 'action' => 'allowedAction']);
        $this->assertNotNull($link);
        $this->assertRegExp('/href="\/ann-authorize-test\/allowedAction"/', $link);
    }
    
}