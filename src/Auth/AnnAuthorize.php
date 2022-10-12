<?php
namespace AnnAuthorize\Auth;

use AnnAuthorize\Lib\AnnAuthorization;
use Cake\Auth\BaseAuthorize;
use Cake\Http\ServerRequest;
use Cake\Log\Log;

/**
 * Authorize implementation that handles authorization based on special comments on each controller method.
 */
class AnnAuthorize extends BaseAuthorize {

    /**
     * Hands authorization over to the AnnAuthorize class.
     * @param array $user
     *         An array containing information about the user to authorize.
     * @param ServerRequest $request
     *         Describes the request to authorize.
     */
    public function authorize($user, ServerRequest $request): bool {
        $controller = $this->_registry->getController();
        $action = $request->getParam('action');
        $pass = $request->getParam('pass');
        Log::debug(sprintf('Trying to authorize user %s for request %s/%s and parameters %s.',
                $user['username'], $controller->getName(), $action, json_encode($pass)));
        $annAuthorization = AnnAuthorization::getInstance();
        $authorized = $annAuthorization->authorizeRequest($user['id'], $controller, $action, $pass, $request);
        Log::debug(sprintf('Authorization %s', $authorized ? 'was successful.': 'failed.'));
        return $authorized;
    }
}