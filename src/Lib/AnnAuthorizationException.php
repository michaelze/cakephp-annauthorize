<?php
namespace AnnAuthorize\Lib;

use Cake\Core\Exception\Exception;

/**
 * Exception class for errors in the AnnAuthorization system.
 */
class AnnAuthorizationException extends Exception {
    public function __construct($message = null, $previous = null) {
        if($message == null) {
            $message = 'AnnAuthorization error';
        }
        parent::__construct($message, 500, $previous);
    }
}