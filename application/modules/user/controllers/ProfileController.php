<?php

class User_ProfileController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->setLayout('w-layout');
        $this->_redirector = $this->_helper->getHelper('Redirector');
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

    public function indexAction()
    {
       
    }

    /**
     *
     */
    public function viewProfileAction()
    {
        
    }

    /**
     * 
     */
    public function createInternProfileAction()
    {
        if($this->isLoggedIn() == false)
        {
             $this->_redirect('/registration/account/login');
        }
        else
        {
            $siteMessage = new Zend_Session_Namespace('siteMessage');

            $auth     = Zend_Auth::getInstance();
            $userType = $auth->getIdentity()->user_type;
            $userId   = $auth->getIdentity()->user_id;
            $userObj  = new Registration_Model_User();            

            if(!strcmp($userType, 'intern'))
            {
                if($userObj->hasProfileById($userType, $userId))
                    if($userType == 'intern')
                        $this->_redirect('/user/profile/my-profile');

                $utilObj   = new User_Model_Utilities();
                $profile   = new User_Model_Profile();
              
                //Get Availablity options for inters employers
                $this->view->availabilites  = $utilObj->getAvailabilites();

                //Get Schedule options for inters employers
                $this->view->schedules      = $utilObj->getSchedules();

                //Get Hours options for inters employers
                $this->view->hours          = $utilObj->getHours();

                //Get industries options for inters employers
                $this->view->industries     = $utilObj->getIndustries();

                //Get skills options for inters employers
                $this->view->skills         = $utilObj->getSkills();

                //Get educations options for inters employers
                $this->view->educations     = $utilObj->getEducations();

                if ($this->_request->isPost()) {
                    if($profile->saveInternProfile($this->_request->getParams())){
                        $siteMessage->message = "I should be taken to my new profile.";
                        $this->_redirect("/user/profile/my-profile");
                    }
                    else
                        $siteMessage->errorMessage = "Error, profile cant create, Please try again.";
                }
                else
                    $siteMessage->message = "All fields are required unless otherwise indicated.";
            }
            else
            {
                $this->_redirect('/user/profile/create-employer-profile');
            }

            $this->view->message      = $siteMessage->message;
            $this->view->errorMessage = $siteMessage->errorMessage;

            Zend_Session:: namespaceUnset('siteMessage');
        }
    }

    public function createEmployerProfileAction()
    {
        if($this->isLoggedIn() == false)
        {
             $this->_redirect('/registration/account/login');
        }
        else
        {            
            $siteMessage = new Zend_Session_Namespace('siteMessage');
            
            $auth     = Zend_Auth::getInstance();
            $userType = $auth->getIdentity()->user_type;
            $userId   = $auth->getIdentity()->user_id;

            if(!strcmp($userType, 'employer'))
            {
                $utilObj   = new User_Model_Utilities();
                $profile   = new User_Model_Profile();                
              
                //Get industries options for inters employers
                $this->view->industries     = $utilObj->getIndustries();

                $stateList = array(
                ''          => '-State-',
                'AL'        => 'Alabama',
                'AK'        => 'Alaska',
                'AZ'        => 'Arizona',
                'AR'        => 'Arkansas',
                'CA'        => 'California',
                'CO'        => 'Colorado',
                'CT'        => 'Connecticut',
                'DE'        => 'Delaware',
                'DC'        => 'District of Columbia',
                'FL'        => 'Florida',
                'GA'        => 'Georgia',
                'HI'        => 'Hawaii',
                'ID'        => 'Idaho',
                'IL'        => 'Illinois',
                'IN'        => 'Indiana',
                'IA'        => 'Iowa',
                'KS'        => 'Kansas',
                'KY'        => 'Kentucky',
                'LA'        => 'Louisiana',
                'ME'        => 'Maine',
                'MD'        => 'Maryland',
                'MA'        => 'Massachusetts',
                'MI'        => 'Michigan',
                'MN'        => 'Minnesota',
                'MS'        => 'Mississippi',
                'MO'        => 'Missouri',
                'MT'        => 'Montana',
                'NE'        => 'Nebraska',
                'NV'        => 'Nevada',
                'NH'        => 'New Hampshire',
                'NJ'        => 'New Jersey',
                'NM'        => 'New Mexico',
                'NY'        => 'New York',
                'NC'        => 'North Carolina',
                'ND'        => 'North Dakota',
                'OH'        => 'Ohio',
                'OK'        => 'Oklahoma',
                'OR'        => 'Oregon',
                'PA'        => 'Pennsylvania',
                'RI'        => 'Rhode Island',
                'SC'        => 'South Carolina',
                'SD'        => 'South Dakota',
                'TN'        => 'Tennessee',
                'TX'        => 'Texas',
                'UT'        => 'Utah',
                'VT'        => 'Vermont',
                'VA'        => 'Virginia',
                'WA'        => 'Washington',
                'WV'        => 'West Virginia',
                'WI'        => 'Wisconsin',
                'WY'        => 'Wyoming');

                $this->view->stateList = $stateList;
                
                if ($this->_request->isPost()) {    
                    if($profile->saveEmployerProfile($this->_request->getParams())){
                        $siteMessage->message = "Thanks for creating your employer profile. This is the profile that job seekers will view when they click on your emails or jobs.";
                        $this->_redirect('/user/profile/employer-profile/profile-id/'.$userId);
                    }
                    else
                        $siteMessage->errorMessage = "Employer profile cant update, Please try again.";                
                }
                else
                    $siteMessage->message = "All fields are required unless otherwise indicated.";

                $this->view->message      = $siteMessage->message;
                $this->view->errorMessage = $siteMessage->errorMessage;
                Zend_Session:: namespaceUnset('siteMessage');
            }
            else
            {
                $this->_redirect('/user/profile/create-employer-profile');
            }
        }
    }

    /**
     * Urban interns own profile
      * 
      *
     */
    public function myProfileAction()
    {
        if($this->isLoggedIn() == false)
        {
             //Do something..........
             $this->_redirect('/registration/account/login');
        }
        else
        {
            $profileObj = new User_Model_Profile();
            $msgObj     = new User_Model_Message();
            $userObj    = new Registration_Model_User();
            $utilObj    = new User_Model_Utilities();
 
            $auth     = Zend_Auth::getInstance();
            $userId   = $auth->getIdentity()->user_id;
            $userType = $auth->getIdentity()->user_type;

            if(!$userObj->hasProfileById($userType, $userId)) $this->_redirect ('/w/home');

            if(!strcasecmp($userType, 'intern'))
            {
                $siteMessage = new Zend_Session_Namespace('siteMessage');

                $internProfileInfo              = $profileObj->getInternProfileInfo($userId);
                $this->view->total_message      = $msgObj->countParentMessageByUserId($userId);
                $this->view->internProfileInfo  = $internProfileInfo;
                $this->view->schedules          =  $utilObj->getSchedules();
                
                $this->view->message = $siteMessage->message;
                Zend_Session:: namespaceUnset('siteMessage');
            }
            else
            {
                $this->_redirect ('/w/home');
            }
        }
    }

    public function employerProfileAction()
    {
        //Profile Id is user just for display purpose , but we user user id for show employer profile

        $siteMessage = new Zend_Session_Namespace('siteMessage');
        
        $userId             = $this->_request->getParam('profile-id');
        $this->view->msg    = $this->_request->getParam('thank-you', null);

        $profileObj = new User_Model_Profile();
        $jobObj     = new Employer_Model_Job();
        $userObj    = new Registration_Model_User();

        if($userObj->hasProfileById('employer', $userId))
        {
            $activeJobs = $jobObj->getPostedJobsByEmployer($userId);
            $this->view->activeJobs = $activeJobs;
            $employerProfileInfo              = $profileObj->getEmployerProfileInfo($userId);
            $this->view->employerProfileInfo  = $employerProfileInfo;
          
            $this->view->postedJobs = $jobObj->getPostedJobsByEmployer($userId);
           
            $this->view->editLink = false;

            if($this->isLoggedIn() == true)
            {
                $auth     = Zend_Auth::getInstance();
                $luserId   = $auth->getIdentity()->user_id;

                if($userId === $luserId)
                    $this->view->editLink = true;
            }
            $this->view->hasProfile = true;
        }
        else
            $this->view->hasProfile = false;

        $this->view->message = $siteMessage->message;

        Zend_Session:: namespaceUnset('siteMessage');  
    }

    /**
     *
     */
    public function internProfileAction()
    {
        $userObj    = new Registration_Model_User();
        $profileObj = new User_Model_Profile();
        $jobObj     = new Employer_Model_Job();
        $utilObj    = new User_Model_Utilities();

        if($this->isLoggedIn() == false)
        {
            $redirectUrl = new Zend_Session_Namespace('redirectUrl');
            $redirectUrl->url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
            
             //LOGOUT INTERN VIEW HERE

            $accessLevel = 'guest';

            $profileId =  (int) $this->_request->getParam('profile-id');
            $profileObj->increaseProfileClicked($profileId);

            $internProfileInfo    = $profileObj->getInternProfileInfo($profileId);
            $this->view->internProfileInfo  = $internProfileInfo;
            
        }
        else
        {
            Zend_Session:: namespaceUnset('redirectUrl');
            
            $auth     = Zend_Auth::getInstance();
            $userType = $auth->getIdentity()->user_type;
            $userId = $auth->getIdentity()->user_id;

            $this->view->hasProfile = $userObj->hasProfileById($userType, $userId);

            $profileId =  (int) $this->_request->getParam('profile-id');

            $profileOwner = $profileObj->getProfileOwnerByJobId($profileId);

            $accessLevel = '';

            if($userId == $profileOwner)
            {
                 $accessLevel = 'owner';

                 $this->_redirect('/user/profile/my-profile');                
            }
            else if(!strcmp($userType, 'employer'))
            {
                $accessLevel = 'employer';
                $profileId =  (int) $this->_request->getParam('profile-id');
                $profileObj->increaseProfileClicked($profileId);

                $internProfileInfo    = $profileObj->getInternProfileInfo($profileId);
                $this->view->internProfileInfo  = $internProfileInfo;

                $empoyerInfo          = $profileObj->getEmployerProfileInfo($userId);
                $this->view->empployerInfo      = $empoyerInfo;

                $employerActiveJobs   = $jobObj->getPostedJobsByEmployer($userId);
                $this->view->employerActiveJobs = $employerActiveJobs;              

            }
            elseif($userType == 'intern')
            {
                //just profile
                $accessLevel = 'intern';
                
                $profileId =  (int) $this->_request->getParam('profile-id');
                $profileObj->increaseProfileClicked($profileId);

                $internProfileInfo    = $profileObj->getInternProfileInfo($profileId);
                $this->view->internProfileInfo  = $internProfileInfo;
            }            
        }

        $this->view->schedules  =  $utilObj->getSchedules();
        $this->view->accessLevel = $accessLevel;
    }

     /**
     *
     */
    public function settingsAction() {

        try{
 //Check user is already loggedIn or not
        if($this->isLoggedIn() == true)
        {
            $siteMessage = new Zend_Session_Namespace('siteMessage');
            
            $auth      = Zend_Auth::getInstance();
            $userType  = $auth->getIdentity()->user_type;
            $userId    = $auth->getIdentity()->user_id;           

            $user = new Registration_Model_User();            

            if ($this->_request->isPost()) {
                if($user->saveSettings($userId , $this->_request->getParams())){
                    $userInfo     = $user->getUserInfoById($userId);
                    $settingsInfo = $user->getUserSettings($userId);
                    $membershipInfo = $user->getMembershipInfo($userType, $userInfo[0]['membership_type']);
                    $siteMessage->message = "Your new settings are saved succesfully.";
                }
                else
                    $siteMessage->errorMessage = "Error, settings cant saved.";
            }
            else
            {
                $userInfo     = $user->getUserInfoById($userId);
                $settingsInfo = $user->getUserSettings($userId);
                $membershipInfo = $user->getMembershipInfo($userType, $userInfo[0]['membership_type']);
            }
            $this->view->userSettings   = $settingsInfo;
            $this->view->userInfo       = $userInfo;
            $this->view->membershipInfo = $membershipInfo;

            $this->view->message      = $siteMessage->message;
            $this->view->errorMessage = $siteMessage->errorMessage;
            Zend_Session:: namespaceUnset('siteMessage');
        }
        else
            $this->_redirect('/registration/account/login');
        }
        catch(Exception $ex)
        {
            echo $ex->getMessage();
        }
    }

    /**
     * 
     */
    public function editMyProfileAction()
    {
         //Check user is already loggedIn or not
        if($this->isLoggedIn() == true)
        {            
            $siteMessage = new Zend_Session_Namespace('siteMessage');

            $userObj   = new Registration_Model_User();
            $auth      = Zend_Auth::getInstance();
            $userId    = $auth->getIdentity()->user_id;
            $userType  = $auth->getIdentity()->user_type;

            if(!strcmp($userType, 'intern') && $userObj->hasProfileById($userType, $userId))
            {
                $utilObj    = new User_Model_Utilities();
                $proObj     = new User_Model_Profile();
                $internInfo = $proObj->getInternProfileInfo($userId);

                $this->view->location_name     = $utilObj->getLocationName($internInfo['internInfo'][0]->location_id);
                $this->view->internProfileInfo = $internInfo;

                //Get Availablity options for inters employers
                $this->view->availabilites  = $utilObj->getAvailabilites();

                //Get Schedule options for inters employers
                $this->view->schedules      = $utilObj->getSchedules();

                //Get Hours options for inters employers
                $this->view->hours          = $utilObj->getHours();

                //Get industries options for inters employers
                $this->view->industries     = $utilObj->getIndustries();

                //Get skills options for inters employers
                $this->view->skills         = $utilObj->getSkills();

                //Get educations options for inters employers
                $this->view->educations     = $utilObj->getEducations();

                 if ($this->_request->isPost()) {
                    if($proObj->updateInternProfile($this->_request->getParams())){
                        $siteMessage->message = "I should be taken to my new profile.";
                        $this->_redirect("/user/profile/my-profile");
                    }
                    else
                        $siteMessage->message = "Error, profile cant update.";
                }
                else
                    $siteMessage->message = "All fields are required unless otherwise indicated.";

                $this->view->message      = $siteMessage->message;
                $this->view->errorMessage = $siteMessage->errorMessage;
                Zend_Session:: namespaceUnset('siteMessage');
            }
            else
                $this->_redirect('/w/home');
        }
        else
            $this->_redirect('/registration/account/login');
    }

    public function updateProfileIndexAction()
    {
        $proObj = new User_Model_Profile();
        $proObj->updateProfileIndex();
    }

    public function addEducationAction()
    {
        $this->_helper->layout->disableLayout();
        $utilObj = new User_Model_Utilities();

        $this->view->educations = $utilObj->getEducations();
        
        $id = $this->_request->getParam('id');
        $this->view->id = $id;
    }


    public function editEmployerProfileAction()
    {
        if($this->isLoggedIn() == false)
        {
             $this->_redirect('/registration/account/login');
        }
        else
        {
            $siteMessage = new Zend_Session_Namespace('siteMessage');
            
            $auth     = Zend_Auth::getInstance();
            $userType = $auth->getIdentity()->user_type;
            $userId   = $auth->getIdentity()->user_id;

            if(!strcmp($userType, 'employer'))
            {
                $utilObj   = new User_Model_Utilities();
                $profile   = new User_Model_Profile();

                $employerProfileInfo              = $profile->getEmployerProfileInfo($userId);
                $this->view->employerProfileInfo  = $employerProfileInfo;

                //Get industries options for inters employers
                $this->view->industries     = $utilObj->getIndustries();

                $stateList = array(
                ''          => '-State-',
                'AL'        => 'Alabama',
                'AK'        => 'Alaska',
                'AZ'        => 'Arizona',
                'AR'        => 'Arkansas',
                'CA'        => 'California',
                'CO'        => 'Colorado',
                'CT'        => 'Connecticut',
                'DE'        => 'Delaware',
                'DC'        => 'District of Columbia',
                'FL'        => 'Florida',
                'GA'        => 'Georgia',
                'HI'        => 'Hawaii',
                'ID'        => 'Idaho',
                'IL'        => 'Illinois',
                'IN'        => 'Indiana',
                'IA'        => 'Iowa',
                'KS'        => 'Kansas',
                'KY'        => 'Kentucky',
                'LA'        => 'Louisiana',
                'ME'        => 'Maine',
                'MD'        => 'Maryland',
                'MA'        => 'Massachusetts',
                'MI'        => 'Michigan',
                'MN'        => 'Minnesota',
                'MS'        => 'Mississippi',
                'MO'        => 'Missouri',
                'MT'        => 'Montana',
                'NE'        => 'Nebraska',
                'NV'        => 'Nevada',
                'NH'        => 'New Hampshire',
                'NJ'        => 'New Jersey',
                'NM'        => 'New Mexico',
                'NY'        => 'New York',
                'NC'        => 'North Carolina',
                'ND'        => 'North Dakota',
                'OH'        => 'Ohio',
                'OK'        => 'Oklahoma',
                'OR'        => 'Oregon',
                'PA'        => 'Pennsylvania',
                'RI'        => 'Rhode Island',
                'SC'        => 'South Carolina',
                'SD'        => 'South Dakota',
                'TN'        => 'Tennessee',
                'TX'        => 'Texas',
                'UT'        => 'Utah',
                'VT'        => 'Vermont',
                'VA'        => 'Virginia',
                'WA'        => 'Washington',
                'WV'        => 'West Virginia',
                'WI'        => 'Wisconsin',
                'WY'        => 'Wyoming');

                $this->view->stateList = $stateList;

                if ($this->_request->isPost()) {
                    if($profile->updateEmployerProfile($this->_request->getParams())){
                        $siteMessage->message = "Thanks for creating your employer profile. This is the profile that job seekers will view when they click on your emails or jobs.";
                        $this->_redirect('/user/profile/employer-profile/profile-id/'.$userId);
                    }
                    else
                        $siteMessage->errorMessage = "Employer profile cant update, Please try again.";
                }
                else
                    $siteMessage->message = "All fields are required unless otherwise indicated.";

                $this->view->message      = $siteMessage->message;
                $this->view->errorMessage = $siteMessage->errorMessage;
                Zend_Session:: namespaceUnset('siteMessage');
            }
            else
            {
                $this->_redirect('/user/profile/create-employer-profile');
            }
        }
    }
}