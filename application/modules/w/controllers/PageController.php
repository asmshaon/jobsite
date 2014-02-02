<?php

class W_PageController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
	$this->_helper->layout->setLayout('w-layout');
    }

    /**
    *
    */
    public function  isLoggedIn()
    {
        if (Zend_Auth::getInstance()->hasIdentity())
            return true;
        else
            return false;
    }

    public function aboutAction()
    {
        
    }

    public function termsAction()
    {

    }
    
    public function contactAction()
    {

    }
    
    public function partnersAction()
    {

    }
    
    public function resourcesAction()
    {

    }

    public function tipsAction()
    {

    }

    public function howItWorksAction()
    {

    }

    public function pressAction()
    {

    }

}
