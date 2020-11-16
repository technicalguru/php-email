<?php

namespace TgEmail;

/**
 * An exception occurring in mail system.
 * @author ralph
 *        
 */
class MailException extends \Exception {

    /**
     * Constructor.
     * @param mixed $message  - the message (optional)
     * @param mixed $code     - the error code (optional)
     * @param mixed $previous - the cause of this error (optional)
     */
    public function __construct($message = null, $code = null, $previous = null) {
        parent::__construct($message = null, $code = null, $previous = null);
    }
}

