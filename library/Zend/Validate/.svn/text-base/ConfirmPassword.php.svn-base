<?php

require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_ConfirmPassword extends Zend_Validate_Abstract {
    const NOT_MATCH = 'notMatch';
    protected $_messageTemplates = array(
            self::NOT_MATCH => 'Password confirmation does not match'
    );
    public function isValid($value, $context = null) {
        $value = (string) $value;
        $this->_setValue($value);
        if (is_array($context)) {
            if (isset($context['password1'])
                    && ($value == $context['password1'])) {
                return true;
            }
        } elseif (is_string($context) && ($value == $context)) {
            return true;
        }
        $this->_error(self::NOT_MATCH);
        return false;
    }
}