<?php

class Admin_HomeController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->setLayout('site-layout');
    }

    public function indexAction()
    {
        // action body
        $this->_helper->layout()->disableLayout();
    }


}

