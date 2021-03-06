<?php
namespace AnnAuthorize\Lib;

use Cake\Controller\Controller;
use Cake\Core\App;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

/**
 * Implements all the logic required around the authorization scheme based on action annotations.
 */
class AnnAuthorization {

    /**
     * The name of the rule that grants any user access to the annotated controller action.
     * @var string
     */
    const RULE_ALLOWED = 'allowed';

    /**
     * The name of the rule that grants the logged in user acces to the annotated method without any further authorization.
     * @var string
     */
    const RULE_LOGGEDIN = 'loggedIn';

    /**
     * The auth rule name prefix for rules handled by the User entity.
     * @var string
     */
    const PREFIX_USER = 'User';

    /**
     * The auth rule name prefix for rules handled by the same controller.
     * @var string
     */
    const PREFIX_CONTROLLER = 'Controller';

    /**
     * The auth rule name prefix for rules handled by a table object.
     * @var string
     */
    const PREFIX_TABLE = 'Table';

    /**
     * The param name for providing 'pass' array elements to a rule method.
     * @var string
     */
    const PARAM_TYPE_PASS = 'pass';

    /**
     * The param name for providing request params to a rule method.
     * @var string
     */
    const PARAM_TYPE_REQ = 'req';

    /**
     * Holds the singleton instance of this class once getInstace() has been called for the first time.
     * @var AnnAuthorization
     */
    private static $instance;

    /**
     * Returns the singleton instance of the AnnAuthorization class.
     */
    public static function getInstance() {
        if (static::$instance == null) {
            static::$instance = new AnnAuthorization();
        }
        return static::$instance;
    }

    /**
     * Performs authorization for user with the provided id against the specified controller action using the @auth annotations on the respective
     * method.
     * @param string $userId
     *         The id of the user to perform authorization for.
     * @param Controller|string $controller
     *         The controller object or the lower case and underscored name of the controller containing the action that should be accessed.
     * @param string $action
     *         The accessed action's name.
     * @param array $pass
     *         All the parameters passed to the action.
     * @param ServerRequest $request
     *         The request object representing the current request.
     * @return boolean
     *         Returns true, if authorization was successful, false otherwise.
     * @throws AnnAuthorizationException
     *         Throws this exception, when a table class required by a rule does not exist, when a rule method does not exists or when an unknown rule
     *         prefix is encountered.
     */
    public function authorizeRequest($userId, $controller, $action, array $pass, ServerRequest $request) {
        $authRules = $this->parseAuthAnnotation($controller, $action);
        foreach ($authRules as $authRule) {
            $ruleComponents = explode('.', $authRule['name']);
            $rulePrefix = $ruleComponents[0];
            $callback = [];
            if ($rulePrefix == self::RULE_ALLOWED) {
                // authorize the request as soon as we encounter the 'allowed' rule.
                return true;
            }
            if($userId == null) {
                // any further rule checking (after allowed) requires a user so if we don't have a userId skip this rule.
                continue;
            }
            if ($rulePrefix == self::RULE_LOGGEDIN) {
                // as we are sure at this point that we have a user id, we can authorize any request with the 'loggedIn' rule.
                return true;
            } elseif($rulePrefix == self::PREFIX_USER) {
                $ruleName = $ruleComponents[1];
                $user = TableRegistry::get('Users')->get($userId);
                $callback = [$user, $this->getRuleMethodName($ruleName)];
            }
            elseif ($rulePrefix == self::PREFIX_CONTROLLER) {
                $ruleName = $ruleComponents[1];
                if (is_object($controller)) {
                    $controllerObject = $controller;
                } else {
                    $controllerName = $this->getControllerName($controller);
                    $controllerClassName = App::className($controllerName, 'Controller', 'Controller');
                    try {
                        $controllerClass = new \ReflectionClass($controllerClassName);
                    } catch(\ReflectionException $e) {
                        throw new AnnAuthorizationException(sprintf('Class "%s" not found.', $controllerClassName), $e);
                    }
                    $controllerObject = $controllerClass->newInstance();
                }
                $callback = [$controllerObject, $this->getRuleMethodName($ruleName)];
            } elseif ($rulePrefix == self::PREFIX_TABLE) {
                $tableName = $ruleComponents[1];
                $ruleName = $ruleComponents[2];
                $table = TableRegistry::get($tableName);
                if ($table == null) {
                    throw new AnnAuthorizationException(sprintf('Table "%s" not found while trying to apply auth rule "%s" for %s::%s.', $tableName,
                            $authRule['name'], $this->getControllerName($controller, true), $action));
                }
                $callback = [$table, $this->getRuleMethodName($ruleName)];
            } else {
                throw new AnnAuthorizationException(sprintf('Invalid rule prefix "%s" encountered while trying to apply auth rule "%s" for %s::%s.',
                        $rulePrefix, $authRule['name'], $this->getControllerName($controller, true), $action));
            }
            if (!method_exists($callback[0], $callback[1])) {
                throw new AnnAuthorizationException(sprintf('Method "%s" not found on "%s" while trying to apply auth rule "%s" for %s::%s.',
                        $callback[1], get_class($callback[0]), $authRule['name'], $controller->name, $action));
            }
            $callbackParams = [$userId];
            foreach ($authRule['args'] as $ruleParam) {
                $callbackParams[] = $this->getParam($ruleParam, $pass, $request);
            }
            if (call_user_func_array($callback, $callbackParams)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns all actions from the supplied controller that have an @auth annotation that marks them as open to the public.
     * @param Controller $controller
     *         The controller to check for allowed actions.
     * @return array
     *         Returns an array containing all the action's names from the supplied controller that are marked as publicly available.
     */
    public function getAllowedActions(Controller $controller) {
        $controllerClass = new \ReflectionClass($controller);
        $actionMethods = $controllerClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        $allowedActions = [];
        foreach ($actionMethods as $actionMethod) {
            $actionMethodComment = $actionMethod->getDocComment();
            if (preg_match('/@auth:' . self::RULE_ALLOWED . '\(\)/', $actionMethodComment)) {
                $allowedActions[] = $actionMethod->getName();
            }
        }
        return $allowedActions;
    }

    /**
     * Parses the @auth annotations within the method comment of the provided controller action.
     * @param Controller|string $controller
     *         The controller object or the lower case and underscored name of the controller that contains the action.
     * @param string $action
     *         The respective action's name.
     * @return array
     *         Returns the parsed authorization rules. The returned value is an associative array whose keys are the names of the authorization rules
     *         and whose values are numerically indexed arrays containing the arguments for the authorization rule.
     *         e.g.: [
     *             'allowed' => [],
     *             'User.role' => [superadmin],
     *             'Table.Routes.builder' => [#arg-0]
     *         ]
     */
    protected function parseAuthAnnotation($controller, $action) {
        if (is_object($controller)) {
            $actionMethod = new \ReflectionMethod($controller, $action);
        } else {
            $controllerName = Inflector::camelize($controller);
            $controllerClassName = App::className($controllerName, 'Controller', 'Controller');
            try {
                $actionMethod = new \ReflectionMethod($controllerClassName, $action);
            } catch(\ReflectionException $e) {
                $controllerName = sprintf('%sController', $controllerName);
                throw new AnnAuthorizationException(sprintf('Unable to parse auth annotations for %s::%s.', $controllerName, $action), $e);
            }
        }
        $methodComment = $actionMethod->getDocComment();
        $ruleCount = preg_match_all('/@auth:([a-z\.]+)\(([^\)]*)\)/i', $methodComment, $matches);
        $authRules = [];
        for ($i = 0; $i < $ruleCount; $i++) {
            $ruleName = $matches[1][$i];
            $authRules[] = [
                'name' => $ruleName,
                'args' => preg_split('/\s*,\s*/', $matches[2][$i])
            ];
        }
        return $authRules;
    }

    /**
     * This method returns the camel cased name of the provided controller.
     * @param Controller|string $controller
     *         The controller object or the lower case and underscored name of the controller.
     * @param string $addSuffix
     *         If true, the suffix 'Controller' will be appended to the returned value.
     * @return string
     *         Returns the controller's camel cased name of the provided controller.
     */
    protected function getControllerName($controller, $addSuffix = false) {
        if (is_object($controller)) {
            $controllerName = $controller->name;
        } else {
            $controllerName = Inflector::camelize($controller);
        }
        if ($addSuffix) {
            $controllerName = sprintf('%sController', $controllerName);
        }
        return $controllerName;
    }

    /**
     * Returns the name of the rule method for the provided rule name.
     * @param string $ruleName
     *         The rule name you want to retrieve the respective rule method name for.
     * @return string
     *         Returns the constructred rule method name.
     */
    protected function getRuleMethodName($ruleName) {
        return sprintf('%sRule', $ruleName);
    }

    /**
     * This method parses the provided rule param string and returns the appropriate value to be passed to the rule method.
     * @param string $ruleParam
     *          The rule param as parsed by the parseAuthAnnotation method.
     * @param array $pass
     *          The pass array from the parsed url.
     * @param ServerRequest $request
     *          The request object representing the current request.
     * @return mixed
     *          Returns the value corresponding to the provided $ruleParam.
     * @throws AnnAuthorizationException
     *          Throws this exception if the $pass or $request parameter designated by the $ruleParam does not exist.
     */
    protected function getParam($ruleParam, array $pass, ServerRequest $request) {
        $ruleMatched = preg_match('/(' . self::PARAM_TYPE_PASS . '|' . self::PARAM_TYPE_REQ . ')\[([^\]]+)\]/', $ruleParam, $matches);
        if (!$ruleMatched) {
            return $ruleParam;
        }
        $type = $matches[1];
        $index = $matches[2];
        switch ($type) {
            case self::PARAM_TYPE_PASS:
                if (!array_key_exists($index, $pass)) {
                    throw new AnnAuthorizationException();
                }
                return $pass[$index];
            case self::PARAM_TYPE_REQ:
                if (!array_key_exists($index, $request->params)) {
                    throw new AnnAuthorizationException();
                }
                return $request->param($index);
        }
    }

    /**
     * Private constructor to prevent additional instances being created.
     */
    private function __construct() {
    }
}