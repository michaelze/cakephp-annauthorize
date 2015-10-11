<?php
namespace AnnAuthorize\View\Helper;

use Cake\View\Helper;
use Cake\Routing\Router;

use AnnAuthorize\Lib\AnnAuthorization;

/**
 * Helper class that provides AnnAuthorization specific functionality.
 * 
 * @property    Cake\View\Helper\HtmlHelper       $Html
 * @property    Cake\View\Helper\SessionHelper    $Session
 */
class AnnAuthorizeHelper extends Helper
{
    public $helpers = ['Html'];

    /**
     * This method provides conditional output of the desired link only if the current user has access to the controller action referenced by the
     * link.
     * @return Returns the constructed link if the current user has access to the controller action referenced by the link or false, if not.
     * @see \Cake\View\Helper\HtmlHelper::link() for additional information on the parameters.
     */
    public function link($title, $url = null, array $options = []) {
        $parsedRoute = Router::parse(Router::url($url !== null ? $url : $title));
        $annAuthorization = AnnAuthorization::getInstance();
        $userId = $this->request->session()->read('Auth.User.id');
        $requestAuthorized = $annAuthorization->authorizeRequest($userId, $parsedRoute['controller'], $parsedRoute['action'], $parsedRoute['pass']);
        if($requestAuthorized) {
            return $this->Html->link($title, $url, $options);
        }
        return false;
    }
}