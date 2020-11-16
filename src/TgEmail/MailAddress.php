<?php

namespace TgEmail;

/**
 * Defines a mail address.
 *
 * @author ralph
 *        
 */
class MailAddress {

    /**
     * The actual email address
     */
    public $email;

    /**
     * The name of the address
     */
    public $name;

    /**
     * Constructor.
     *
     * @param string $email
     *            - an e-mail address
     * @param string $name
     *            - the name of the address (optional)
     */
    public function __construct($email, $name = NULL) {
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * Returns the mail-compliant address string.
     *
     * @return string the address string.
     */
    public function __toString() {
        if ($this->name != NULL) {
            return $this->name . ' <' . $this->email . '>';
        }
        return $this->email;
    }

    /**
     * Returns a MailAddress object from a compliant string.
     *
     * @param string $s
     *            - a mail compliant string
     * @return MailAddress - The MailAddress object
     */
    public static function from($s, $name = NULL) {
        if (is_string($s)) {
            if ($name == NULL) {
                $pos = strpos($s, '<');
                if ($pos !== FALSE) {
                    $name = $pos > 0 ? trim(substr($s, 0, $pos)) : NULL;
                    if ($name == '') $name = NULL;

                    $email = substr($s, $pos + 1);
                    $pos = strpos($email, '>');
                    if ($pos !== FALSE) {
                        $email = trim(substr($email, 0, $pos));
                    }

                    return new MailAddress($email, $name);
                }
                return new MailAddress($s);
            } else {
                return new MailAddress($s, $name);
            }
        } else if (is_a($s, 'TgEmail\\MailAddress')) {
            return $s;
        } else if (is_object($s)) {
            return new MailAddress($s-email, $s->name);
        }
    }
}

