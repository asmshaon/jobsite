<?php

class Form_ResetPasswordForm extends Zend_Form
{
    public function init($options = null)
    {
        $password1 = new Zend_Form_Element_Password('password1');
        $password1->setLabel('Password:')
                ->setRequired(true)
                ->setAttrib('class', 'inputr')
                ->addFilter('StringTrim')
                ->addValidator('stringLength', false, array(6, 20))
                ->addValidator('NotEmpty', true,
                        array('messages' => 'Password is required.'));

        $password2 = new Zend_Form_Element_Password('password2');
        $validatorPassword = new Zend_Validate_ConfirmPassword('password1');
        $validatorPassword->setMessage('Password mismatch.');
        $password2->setLabel('Retype Password:')
                ->addValidator('stringLength', false, array(6, 20))
                ->setRequired(true)
                ->setAttrib('class', 'inputr')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true,
                         array('messages' => 'Password is required.'))
                ->addValidator($validatorPassword);

        $submit = new Zend_Form_Element_Submit('login');
        $submit->setLabel('Reset Password')
                ->setAttrib('class', 'submit_button');

        $loginForm = new Zend_Form();
        $this->setName('login')
                ->setAttrib('id', 'login')
                ->setAttrib('action', '')
                ->addElements(array($password1, $password2, $submit));
    }
}