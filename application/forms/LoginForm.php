<?php
class Form_LoginForm extends Zend_Form
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

        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password: *')
                ->setAttrib('class', 'inputr')
                ->addValidator('NotEmpty', true,
                        array('messages' => 'Password is required.'))
                 ->setRequired(true);

        $keep_me = new Zend_Form_Element_Checkbox('keep_me');
        $keep_me->setLabel('Keep me logged on this computer')
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(false);

        $submit = new Zend_Form_Element_Submit('login');
        $submit->setLabel('Login')
                ->setAttrib('class', 'login_button');


        
        $loginForm = new Zend_Form();
        $this->setName('login')
                ->setAttrib('id', 'login')
                ->setAttrib('action', 'login')
                ->addElements(array($email, $password, $keep_me, $submit));
    }
}
