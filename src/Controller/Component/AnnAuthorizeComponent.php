<?php
namespace AnnAuthorize\Controller\Component;

use Cake\Controller\Component;

use AnnAuthorize\Lib\AnnAuthorization;

/**
 * Component that helps integrating the annotation based authorization into the AuthComponent.
 *
 * @property \Cake\Controller\Component\AuthComponent    $Auth
 */
class AnnAuthorizeComponent extends Component {

    public $components = ['Auth'];

    /**
     * Marks all actions as allowed with the AuthComponent that have the @auth:allowed() annotation in their respective method comments.
     * @param array $config
     *         Additional configuration parameters.
     */
    public function initialize(array $config = []) {
        $annAuthorization = AnnAuthorization::getInstance();
        $allowedActions = $annAuthorization->getAllowedActions($this->_registry->getController());
        $this->Auth->allow($allowedActions);
    }
}