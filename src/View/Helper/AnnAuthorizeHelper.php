<?php
namespace AnnAuthorize\View\Helper;

use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\View\Helper;

use Laminas\Diactoros\Uri;

use AnnAuthorize\Lib\AnnAuthorization;

/**
 * Helper class that provides AnnAuthorization specific functionality.
 *
 * @property    Cake\View\Helper\HtmlHelper       $Html
 */
class AnnAuthorizeHelper extends Helper {
    public $helpers = ['Html'];

    /**
     * This method provides conditional output of the desired link only if the current user has access to the controller action referenced by the
     * link.
     * @return string Returns the constructed link if the current user has access to the controller action referenced by the link or false, if not.
     * @see \Cake\View\Helper\HtmlHelper::link() for additional information on the parameters.
     */
    public function link($title, $url = null, array $options = []) {
        $linkRequest = (new ServerRequest())->withUri(new Uri(Router::url($url ?? $title)));
        $parsedRoute = Router::parseRequest($linkRequest);

        $annAuthorization = AnnAuthorization::getInstance();

        $request = $this->getView()->getRequest();

        $userId = $request->getSession()->read('Auth.User.id');
        $controller = $parsedRoute['controller'];
        $action = $parsedRoute['action'];
        $pass = $parsedRoute['pass'];
        $requestAuthorized = $annAuthorization->authorizeRequest($userId, $controller, $action, $pass, $request);
        if ($requestAuthorized) {
            return $this->Html->link($title, $url, $options);
        }
        if (array_key_exists('fallbackToTitle', $options) && $options['fallbackToTitle'] === true) {
            return h($title);
        }
        return false;
    }
}
