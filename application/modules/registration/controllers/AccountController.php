<?php

class Registration_AccountController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
        $this->_helper->layout->setLayout('w-layout');
        $this->_redirector = $this->_helper->getHelper('Redirector');
        
        $messageSession = new Zend_Session_Namespace('messageSession');
    }

    public function indexAction() {
        // action body
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

    /**
     * Urban interns register page for users who are now logged in
     */
    public function joinNowAction() {

        $siteMessage = new Zend_Session_Namespace('siteMessage');
        
        if ($this->isLoggedIn() == false) {

            $userObj = new Registration_Model_User();

            if ($this->_request->isPost()) {

                $userEmail      = $this->_request->getParam('user_email');

                if($userObj->isExistingUser($userEmail))
                        $siteMessage->errorMessage = "This email address is already registered, please try again.";
                else
                {
                    $membership_type = $this->_request->getParam('membership_type');

                    if ($membership_type == 1 || $membership_type == 3){

                        if($userObj->saveUser($this->_request->getParams())){
                            $siteMessage->message = "Some action you initiated that relates to this page has been completed.
                                Thank you for register with us. We have sent a confirmation mail for activate your account.";
                            $this->_redirect('/registration/account/thank-you');
                        }
                        else
                            $siteMessage->errorMessage = "Error! Invalid information is provided.";
                    }
                    else {
                        $myNamespace = new Zend_Session_Namespace('myNamespace');
                        $myNamespace->email = $this->_request->getParam('user_email');
                        $myNamespace->password = $this->_request->getParam('password1');
                        $myNamespace->membershipType = $this->_request->getParam('membership_type');
                        $myNamespace->userType = $this->_request->getParam('user_type');

                        $this->_redirect('/registration/account/billing');
                    }
                }
                $this->view->errorMessage = $siteMessage->errorMessage;
                $this->view->message      = $siteMessage->message;
                Zend_Session:: namespaceUnset('siteMessage');
            }
            
        }
        else
            $this->_redirect('/w/home');
    }

    /**
     *
     */
    public function activeNewUserAction() {

        $siteMessage = new Zend_Session_Namespace('siteMessage');
        
        $loginForm = new Form_LoginForm();
        $loginForm->setAction('/registration/account/login');
        $this->view->title = "The urbaninterns : User Activation  Process";

        $values = $this->_request->getParams();
        $user = new Registration_Model_User();
        $isActive = $user->activeNewUser($values['activation-key']);

        if ($isActive) {
            $siteMessage->message = "Your account is now active.";
        } else {
            $siteMessage->errorMessage = "Invalid activation key or already used.";
        }

        $this->view->loginForm = $loginForm;

        $this->view->errorMessage = $siteMessage->errorMessage;
        $this->view->message      = $siteMessage->message;
        Zend_Session:: namespaceUnset('siteMessage');
    }

    /**
     * Login Action for user login....
     */
    public function loginAction()
    {
        $siteMessage = new Zend_Session_Namespace('siteMessage');

        $this->view->title = "The ubaninterns : Log In";
        # If we're already logged in, just redirect
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('/w/home');
        }

        $request = $this->getRequest();
        $loginForm = new Form_LoginForm();
        $errorMessage = "";

        if ($request->isPost()) {
            if ($loginForm->isValid($request->getPost())) {
                $authAdapter = $this->getAuthAdapter();

                # get the username and password from the form
                $email    = $loginForm->getValue('email');
                $password = md5($loginForm->getValue('password'));

                $user = new Registration_Model_User();

                if ($user->isActive($email, $password) === false) {
                    $username = md5(microtime());
                    $password = md5(microtime());
                }

                # pass to the adapter the submitted username and password
                $authAdapter->setIdentity($email)
                        ->setCredential($password);

                $auth = Zend_Auth::getInstance();
                $result = $auth->authenticate($authAdapter);

                # is the user a valid one?
                if ($result->isValid()) {
                    # all info about this user from the login table
                    # ommit only the password, we don't need that
                    $userInfo = $authAdapter->getResultRowObject(null, 'user_password');

                    # the default storage is a session with namespace Zend_Auth
                    $authStorage = $auth->getStorage();
                    $authStorage->write($userInfo);

                    if ($this->_request->getParam('keep_me')) {
                        $seconds = 60 * 60 * 24 * 365; // 7 days
                        Zend_Session::rememberMe($seconds);
                    }                   
                    $redirectUrl = new Zend_Session_Namespace('redirectUrl');
                    
                    if(isset ($redirectUrl->url)){                       
                        $this->_redirect ($redirectUrl->url);
                    }
                    else{                        
                        $this->_redirect('/w/home');
                    }
                } else {
                    $siteMessage->errorMessage = "Wrong username or password provided. Please try again.";
                    $this->view->message       = $siteMessage->message;
                }
            }
        }
        $this->view->loginForm    = $loginForm;

        $this->view->errorMessage = $siteMessage->errorMessage;
        $this->view->message      = $siteMessage->message;
        Zend_Session:: namespaceUnset('siteMessage');
    }

    /**
     * Gets the adapter for authentication against a database table
     *
     * @return object
     */
    protected function getAuthAdapter() {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

        $authAdapter->setTableName('users')
                ->setIdentityColumn('user_email')
                ->setCredentialColumn('user_password');

        return $authAdapter;
    }

    /**
     *
     */
    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect('/w/home');
    }

    /**
     *
     */
    public function forgotPasswordAction() 
    {
        $siteMessage = new Zend_Session_Namespace('siteMessage');
        
        $forgotPasswordForm = new Form_ForgotPasswordForm();

        if ($this->_request->isPost()) {
            if ($forgotPasswordForm->isValid($this->_request->getPost())) {
                $newUser = new Registration_Model_User();
                if($newUser->sendResetPasswordLink($this->_request->getParams()))
                    $siteMessage->message = "Your Password Reset Link have been sent. Please reset your password.";
                else
                    $siteMessage->errorMessage = "Email address does not match in our database";                
            }
        }

        $this->view->forgotPasswordForm = $forgotPasswordForm;

        $this->view->message      = $siteMessage->message;
        $this->view->errorMessage = $siteMessage->errorMessage;
        Zend_Session:: namespaceUnset('siteMessage');
    }

    /**
     *
     */
    public function resetPasswordAction()
    {
        $siteMessage = new Zend_Session_Namespace('siteMessage');

        $resetPasswordForm = new Form_ResetPasswordForm();

        $user = new Registration_Model_User();

        $resetKey = $this->_request->getParam('reset-key');

        $requestInfo = $user->getPasswordRequestInfo($resetKey);

        if (count($requestInfo)) {
            if ($this->_request->isPost()) {
                if ($resetPasswordForm->isValid($this->_request->getPost())) {
                    $newUser = new Registration_Model_User();
                    if($newUser->resetPassword($this->_request->getParams(), $requestInfo[0]->user_email))
                        $siteMessage->message = "Your password have been reset successfully.";
                    else
                        $siteMessage->errorMessage = "Error! password cant reset. Please try again.";
                    
                }
            }
            $this->view->resetPasswordForm = $resetPasswordForm;
        }
        else
            $siteMessage->errorMessage = "Invalid activation key or already used.";

        $this->view->resetPasswordForm = $resetPasswordForm;

        $this->view->message      = $siteMessage->message;
        $this->view->errorMessage = $siteMessage->errorMessage;
        Zend_Session:: namespaceUnset('siteMessage');
    }

    /**
     *
     */
    public function thankYouAction()
    {
        $siteMessage = new Zend_Session_Namespace('siteMessage');

        $this->view->message      = $siteMessage->message;
        $this->view->errorMessage = $siteMessage->errorMessage;

        Zend_Session:: namespaceUnset('siteMessage');
    }

    public function billingAction()
    {
        $siteMessage = new Zend_Session_Namespace('siteMessage');

        $userObj = new  Registration_Model_User();
        
        $typeInfo = array(
            4 => array('amount' => 49.95, 'desc' => 'Single Membership', 'planId'=> 'single-employer' ),
            5 => array('amount' => 99.95, 'desc' => 'Premium Membership', 'planId' => 'premium-employer'),
            6 => array('amount' => 149.95, 'desc' => 'Pro Membership', 'planId' => 'pro-employer' ),
            7 => array('amount' => 249.95, 'desc' => 'Platinum Membership', 'planId' => 'platinum-employer' ),
            2 => array('amount' => 12.95, 'desc' => 'Premium Membership $12.95 ', 'planId' => 'premium-intern' )
        );

        $billingForm = new Form_BillingForm();       

        $myNamespace = new Zend_Session_Namespace('myNamespace');

        $this->view->membershipType = $typeInfo[$myNamespace->membershipType]['desc'];
        $this->view->typeAmount =     $typeInfo[$myNamespace->membershipType]['amount'];

        if ($this->_request->isPost()) {
            if ($billingForm->isValid($this->_request->getPost())) {

                    $billObj = new Registration_Model_Billing();
                    $planId = $typeInfo[$myNamespace->membershipType]['planId'];

                    $tranaction = $billObj->payment($planId, $this->_request->getParams());                   

                    if(isset ($tranaction) && count($tranaction) == 2)
                    {
                        $data = array(
                            'user_email' => $myNamespace->email,
                            'password1'  => $myNamespace->password,
                            'user_type'  => $myNamespace->userType,
                            'membership_type' => $myNamespace->membershipType,
                            'subscription_id' => $tranaction['subscription_id'],
                            'customer_id'     => $tranaction['customer_id'],
                            'status'          => 'active'
                        );
                        if($userObj->saveUser($data)){                            
                            if($this->autoLoggedin($myNamespace->email, $myNamespace->password)){
                                $redirectUrl = new Zend_Session_Namespace('redirectUrl');
                                if(isset ($redirectUrl->url))
                                    $this->_redirect ($redirectUrl->url);
                                else
                                    $this->_redirect('/w/home');
                            }
                        }
                        else
                            $siteMessage->errorMessage = "Error! invaild information is provided.";
                    }
                    else
                        $siteMessage->errorMessage = "Error! invaild information is provided.";
            }
            $this->view->message      = $siteMessage->message;
            $this->view->errorMessage = $siteMessage->errorMessage;
            Zend_Session:: namespaceUnset('siteMessage');
        }

        $this->view->billingForm = $billingForm;

    }
    

    /**
     * 
     */
    public function updateBillingAction() {

        if($this->isLoggedIn())
        {
            $siteMessage = new Zend_Session_Namespace('siteMessage');
            
            $auth = Zend_Auth::getInstance();            
            $userId  = $auth->getIdentity()->user_id;

            $userObj = new  Registration_Model_User();
            $typeInfo = array(
                4 => array('amount' => 49.95, 'desc' => 'Single Membership', 'planId'=> 'single-employer' ),
                5 => array('amount' => 99.95, 'desc' => 'Premium Membership', 'planId' => 'premium-employer'),
                6 => array('amount' => 149.95, 'desc' => 'Pro Membership', 'planId' => 'pro-employer' ),
                7 => array('amount' => 249.95, 'desc' => 'Platinum Membership', 'planId' => 'platinum-employer' ),
                2 => array('amount' => 12.95, 'desc' => 'Premium Membership $12.95 ', 'planId' => 'premium-intern' )
            );

            $billingForm = new Form_BillingForm();
            $billingForm->setAction('update-billing');

            $myNamespace = new Zend_Session_Namespace('myNamespace');

            $this->view->membershipType = $typeInfo[$myNamespace->membershipType]['desc'];
            $this->view->typeAmount =     $typeInfo[$myNamespace->membershipType]['amount'];

            $customerId = $myNamespace->customerId;
            $subscriptionId = $myNamespace->subscriptionId;

            if ($this->_request->isPost()) {
                if ($billingForm->isValid($this->_request->getPost())) {

                        $billObj = new Registration_Model_Billing();
                        $planId = $typeInfo[$myNamespace->membershipType]['planId'];

                        $price = $typeInfo[$myNamespace->membershipType]['amount'];

                        $isCancled = $userObj->getSubscriptionId($userId);

                        if($isCancled == 'canceled')
                        {
                            $tranaction = $billObj->canceledCustomerSubscription( $customerId, $planId, $this->_request->getParams());
                            if(isset ($tranaction) && count($tranaction) == 2)
                            {
                                $subscriptionId = $tranaction['subscription_id'];
                                $newMembershipType = $myNamespace->membershipType;
                                
                                if($userObj->changeMembershipType($userId, $newMembershipType, $subscriptionId)){
                                     $siteMessage->message = "Your membership is now changed successfully.";
                                     $this->_redirect('/registration/account/thank-you');
                                }
                                else
                                    $siteMessage->errorMessage = "Payment request is invalid.";
                            }
                        }
                        else
                        {
                            $tranaction = $billObj->updateSubscription($customerId, $subscriptionId ,$planId, $price, $this->_request->getParams());

                            if($tranaction === true)
                            {
                                $newMembershipType = $myNamespace->membershipType;
                                if($userObj->changeMembershipType($userId, $newMembershipType, $subscriptionId)){
                                     $siteMessage->message = "Your membership is now changed successfully.";
                                     $this->_redirect('/registration/account/thank-you');
                                }
                                else
                                    $siteMessage->errorMessage = "Error! invaild information is provided.";
                            }
                            else
                                $siteMessage->errorMessage = "Error! invaild information is provided.";
                        }

                }

                $this->view->message      = $siteMessage->message;
                $this->view->errorMessage = $siteMessage->errorMessage;
                Zend_Session:: namespaceUnset('siteMessage');
            }

            $this->view->billingForm = $billingForm;
        }
        else
            $this->_redirect('/registration/account/login');

    }




     /**
     * Urban interns register page for users who are now logged in
     */
    public function applyJobAction() 
    {
        $siteMessage = new Zend_Session_Namespace('siteMessage');

        //Check user is already loggedIn or not
        if($this->isLoggedIn() == false)
        {
            $redirectUrl = new Zend_Session_Namespace('redirectUrl');
           
            $userObj = new Registration_Model_User();
            
            if ($this->_request->isPost()) {

                $userEmail      = $this->_request->getParam('user_email');

                if($userObj->isExistingUser($userEmail))
                        $siteMessage->errorMessage = "This email address is already registered, please try again.";

                else
                {
                    $membership_type = $this->_request->getParam('membership_type');

                    if ($membership_type == 1){                        
                     
                        if($userObj->saveUser($this->_request->getParams())){
                            $siteMessage->message = "Some action you initiated that relates to this page has been completed.
                                Thank you for register with us. We have sent a confirmation mail for activate your account.";
                            $this->_redirect('/registration/account/thank-you');
                        }
                        else
                            $siteMessage->errorMessage = "Error! Invalid information is provided.";
                    }
                    else {
                        $myNamespace = new Zend_Session_Namespace('myNamespace');
                        $myNamespace->email = $this->_request->getParam('user_email');
                        $myNamespace->password = $this->_request->getParam('password1');
                        $myNamespace->membershipType = $this->_request->getParam('membership_type');
                        $myNamespace->userType = $this->_request->getParam('user_type');

                        $this->_redirect('/registration/account/billing');
                    }
                }

                $this->view->message      = $siteMessage->message;
                $this->view->errorMessage = $siteMessage->errorMessage;
                Zend_Session:: namespaceUnset('siteMessage');
            }
        }
        else{
            $auth = Zend_Auth::getInstance();
            $userType   = $auth->getIdentity()->user_type;
            if($userType == 'employer')
                $this->_redirect($_SERVER['HTTP_REFERER']);
            else if($userType == 'intern')
                $this->_redirect('/w/home');
        }
    }

    /**
     * Urban interns register page for users who are now logged in
     */
    public function postJobAction() 
    {
        $siteMessage = new Zend_Session_Namespace('siteMessage');

        //Check user is already loggedIn or not
        if($this->isLoggedIn() == false)
        { 
            $userObj = new Registration_Model_User();

             if ($this->_request->isPost()) {
                 
                 $userEmail      = $this->_request->getParam('user_email');
                 
                if($userObj->isExistingUser($userEmail))
                        $siteMessage->errorMessage = "This email address is already registered, please try again.";
                else
                {                
                        $membership_type = $this->_request->getParam('membership_type');

                        if ($membership_type == 3){
                            $userObj = new Registration_Model_User();
                          
                            if($userObj->saveUser($this->_request->getParams())){
                                $siteMessage->message = "Some action you initiated that relates to this page has been completed.
                                    Thank you for register with us. We have sent a confirmation mail for activate your account.";
                                $this->_redirect('/registration/account/thank-you');
                            }
                            else
                                $siteMessage->errorMessage = "Error! Invalid information is provided.";
                        }
                        else {
                            $myNamespace = new Zend_Session_Namespace('myNamespace');
                            $myNamespace->email = $this->_request->getParam('user_email');
                            $myNamespace->password = $this->_request->getParam('password1');
                            $myNamespace->membershipType = $this->_request->getParam('membership_type');
                            $myNamespace->userType = $this->_request->getParam('user_type');

                            $this->_redirect('/registration/account/billing');
                        }
                }
                
                $this->view->message      = $siteMessage->message;
                $this->view->errorMessage = $siteMessage->errorMessage;
                Zend_Session:: namespaceUnset('siteMessage');
            }            
        }
        else
        {
            $auth = Zend_Auth::getInstance();
            $userType              = $auth->getIdentity()->user_type;
            if($userType == 'intern')
                $this->_redirect($_SERVER['HTTP_REFERER']);
            else if($userType == 'employer')
                $this->_redirect('/w/home');
        }
    }

    /**
     *
     */
    public function changeMembershipAction()
    {
        //Check user is already loggedIn or not
        if($this->isLoggedIn() == true )
        {
            $user = new Registration_Model_User();
            $billObj = new Registration_Model_Billing();

            $action = base64_decode($this->_request->getParam('action-id'));
            $this->view->action = $action;
         
            $auth = Zend_Auth::getInstance();
            $userType              = $auth->getIdentity()->user_type;
            $userId                = $auth->getIdentity()->user_id;
            
            $currentMembershipType = $user->getCurrentMembershipType($userId);

            $this->view->currentMembershipType = $currentMembershipType;
            $this->view->for = '';

            if( !strcmp($userType,"intern") )
            {
                $availableMembershipTypes = $user->getAvailableMembershipTypes($userType, $currentMembershipType, $action);
                
                if($this->_request->getParam('type') == 'promote')
                {
                    $this->view->for =  'promote';
                    unset ($availableMembershipTypes[1]);
                }

                if ($this->_request->getPost()) {
                    //3=free membership
                    $newMembershipType = $this->_request->getParam('membership_type');
                    $subscriptionId    = $user->getSubscriptionId($userId);

                    if($currentMembershipType == (int)$newMembershipType);

                    else if((int)$newMembershipType == 1)
                    {
                        //DELETE CUSTOMER

                        if($billObj->cancelSubscription($subscriptionId)){                            
                            if($user->changeMembershipType($userId, $newMembershipType))
                                $this->view->msg = "Your membership changed successfully";
                            else
                                $this->view->msg = "Your membership change problem.";
                        }
                        else
                            $this->view->msg = "ERROR";
                    }
                    else
                    {
                        $myNamespace = new Zend_Session_Namespace('myNamespace');
                        $myNamespace->membershipType = $newMembershipType;
                        $myNamespace->customerId     = $auth->getIdentity()->customer_id;
                        $myNamespace->subscriptionId     = $auth->getIdentity()->subscription_id;
                        $this->_redirect('/registration/account/update-billing');
                    }
                }
            }
            else if(!strcmp($userType,"employer"))
            {
                $availableMembershipTypes = $user->getAvailableMembershipTypes($userType, $currentMembershipType, $action);

                if ($this->_request->getPost()) {
                    //3=free membership
                    $newMembershipType = $this->_request->getParam('membership_type');
                    $subscriptionId    = $user->getSubscriptionId($userId);

                    if($currentMembershipType == (int)$newMembershipType);                    
                    
                    else if((int)$newMembershipType == 3)
                    {
                        //DELETE CUSTOMER
                        
                        if($billObj->cancelSubscription($subscriptionId)){                            
                            if($user->changeMembershipType($userId, $newMembershipType))
                                $this->view->msg = "Your membership changed successfully";
                            else
                                $this->view->msg = "Your membership change problem.";
                        }
                        else
                            $this->view->msg = "ERROR";
                    }
                    else
                    {
                        $myNamespace = new Zend_Session_Namespace('myNamespace');
                        $myNamespace->membershipType = $newMembershipType;
                        $myNamespace->customerId     = $auth->getIdentity()->customer_id;
                        $myNamespace->subscriptionId     = $auth->getIdentity()->subscription_id;
                        $this->_redirect('/registration/account/update-billing');
                    }
                }
            }

            $this->view->availableMembershipTypes = $availableMembershipTypes;

        }
        else
            $this->_redirect('/registration/account/login');
    }

    public function autoLoggedin($email, $password)
    {
        $user = new Registration_Model_User();
        $authAdapter = $this->getAuthAdapter();

        # pass to the adapter the submitted username and password
        $authAdapter->setIdentity($email)
                ->setCredential(md5($password));

        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($authAdapter);

        # is the user a valid one?
        if ($result->isValid()) {
            # all info about this user from the login table
            # ommit only the password, we don't need that
            $userInfo = $authAdapter->getResultRowObject(null, 'user_password');

            # the default storage is a session with namespace Zend_Auth
            $authStorage = $auth->getStorage();
            $authStorage->write($userInfo);

            return true;
        }
        return false;
    }

}