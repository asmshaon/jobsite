<?php

class Form_ForgotPasswordForm extends Zend_Form
{
    public function init($options = null)
    {
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email: *')                 
                ->setRequired(true)
                ->setAttrib('class', 'inputr')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true,
                        array('messages' => 'Email address is required.'))
                ->addValidator('EmailAddress', true,
                        array('messages' =>  'Valid email address is required.'));

        $submit = new Zend_Form_Element_Submit('login');
        $submit->setLabel('Reset Password')
                ->setAttrib('class', 'submit_button');

        $loginForm = new Zend_Form();
        $this->setName('login')
                ->setAttrib('id', 'login')
                ->setAttrib('action', 'forgot-password')
                ->addElements(array($email,$submit));
    }
}