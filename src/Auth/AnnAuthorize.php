<?php
namespace AnnAuthorize\Auth;

use Cake\Auth\BaseAuthorize;
use Cake\Log\Log;
use Cake\Network\Request;

use AnnAuthorize\Lib\AnnAuthorization;

/**
 * Authorize implementation that handles authorization based on special comments on each controller method.
 */
class AnnAuthorize extends BaseAuthorize {

    /**
     * Hands authorization over to the AnnAuthorize class.
     * @param array $user
     *         An array containing information about the user to authorize.
     * @param Request $request
     *         Describes the request to authorize.
     */
    public function authorize($user, Request $request) {
        $controller = $this->_registry->getController();
        $action = $request->param('action');
        $pass = $request->param('pass');
        Log::debug(sprintf('Trying to authorize user %s for request %s/%s and parameters %s.',
                $user['username'], $controller->name, $action, json_encode($pass)));
        $annAuthorization = AnnAuthorization::getInstance();
        $authorized = $annAuthorization->authorizeRequest($user['id'], $controller, $action, $pass, $request);
        Log::debug(sprintf('Authorization %s', $authorized ? 'was successful.': 'failed.'));
        return $authorized;
    }
}