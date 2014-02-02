<?php

class Employer_JobController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->setLayout('w-layout');
        $this->_redirector = $this->_helper->getHelper('Redirector');
    }

    public function indexAction()
    {
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



    public function myJobsAction()
    {
        $jobObj = new Employer_Model_Job();

        if($this->isLoggedIn() == false)
        {
             $this->_redirect('/registration/account/login');
        }
        else
        {
            $auth     = Zend_Auth::getInstance();
            $userType = $auth->getIdentity()->user_type;
            $userId   = $auth->getIdentity()->user_id;

            if($userType =='intern')            
                $this->_redirect('/w/home');
            else
            {
                $this->view->postedJobs = $jobObj->getPostedJobsByEmployer($userId, 3);
            }
            $this->view->userId = $userId;
        }
    }

    /**
     *
     */

    public function testIndexAction()
    {
        $path = str_replace('\\', '/',getcwd() . '/data/indexes/jobs');

        $index = Zend_Search_Lucene::open($path);
        $doc = new Zend_Search_Lucene_Document();

        $doc->addField(Zend_Search_Lucene_Field::unIndexed('job_id', "60"));
        $doc->addField(Zend_Search_Lucene_Field::text('job_title', "shaon"));
        $doc->addField(Zend_Search_Lucene_Field::text('company_name', "shaon"));
        $doc->addField(Zend_Search_Lucene_Field::text('responsibilites', "shaon"));
        $doc->addField(Zend_Search_Lucene_Field::text('qualifications', "shaon"));
        $doc->addField(Zend_Search_Lucene_Field::text('pay_type', "shaon"));
        $index->addDocument($doc);
        $index->commit();
        $index->optimize();
    }

    public function searchIndexAction()
    {
       $path = str_replace('\\', '/',getcwd() . '/data/indexes/jobs');

       $index = Zend_Search_Lucene::open($path);
       $results = $index->find("lucen");

       echo "Index contains ".$index->count()." documents.\n\n";

        foreach ($results as $result)
        {
            echo  $result->job_id;
        }
    }


    public function postAction()
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
                $utilObj    = new User_Model_Utilities();
                $userObj    = new Registration_Model_User();
                $profileObj = new User_Model_Profile();
                $jobObj     = new Employer_Model_Job();

                $hasProfile = $userObj->hasProfileById($userType, $userId);
            
                //Get All Locations for interns and employers
                $this->view->locations      = $utilObj->getLocations();

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

                //if company have profile get profile info to bind job post form
                if($hasProfile)
                    $this->view->employerInfo   = $profileObj->getEmployerProfileInfo($userId);

                if ($this->_request->isPost()) {
                    if($jobObj->saveJob($userId, $this->_request->getParams()))
                        $siteMessage->message = "Job posted successfully.";
                    else
                        $siteMessage->errorMessage = "Error! Job cant posted.";
                }
                else
                    $siteMessage->message = "All fields are required unless otherwise indicated.";

                $this->view->postedJobs = $jobObj->getPostedJobsByEmployer($userId, 3);

                //this is true because no need to profile to post job
                $this->view->hasProfile = true;

                $this->view->message      = $siteMessage->message;
                $this->view->errorMessage = $siteMessage->errorMessage;
                Zend_Session:: namespaceUnset('siteMessage');
            }
            else
            {
                $this->_redirect('/w/home');
            }
        }
    }

    public function jobDetailsAction()
    {
        if($this->isLoggedIn() == false)
        {
             $redirectUrl = new Zend_Session_Namespace('redirectUrl');
             $redirectUrl->url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
             $this->_redirect('/registration/account/apply-job');
        }
        else
        {
            Zend_Session:: namespaceUnset('redirectUrl');
            
            $auth     = Zend_Auth::getInstance();
            $userType = $auth->getIdentity()->user_type;
            $userId   = $auth->getIdentity()->user_id;

            $jobObj     = new Employer_Model_Job();
            $userObj    = new Registration_Model_User();
            $profileObj = new User_Model_Profile();
            $utilObj    = new User_Model_Utilities();

            $jobId  = (int)$this->_request->getParam('job-id');

            $jobOwner = $jobObj->getJobOwnerByJobId($jobId);

            $accessLevel = '';

            if($userId == $jobOwner)
            {
                //stats for this job
                //$this->view->stats = $jobObj->getJobStats($jobId);
                $accessLevel = 'owner';
            }
            elseif($userType == 'intern')
            {
                //applly form
                $accessLevel = 'intern';
                $hasProfile = $userObj->hasProfileById($userType, $userId);

                if($hasProfile)
                {
                    $internProfileInfo    = $profileObj->getInternProfileInfo($userId);
                    $this->view->internProfileInfo  = $internProfileInfo;
                }
                $this->view->hasProfile = $hasProfile;
                
            }
            elseif($userType == 'employer')
            {
                //just job
                $accessLevel = 'employer';
            }

            $jobDetails = $jobObj->getJobDetailsById($jobId);

            $this->view->schedules   = $utilObj->getSchedules();
            $this->view->jobDetails  = $jobDetails;
            $this->view->accessLevel = $accessLevel;
        }
    }

    public function updateJobIndexAction()
    {
        $jobOjb = new Employer_Model_Job();
        $jobOjb->updateJobIndex();
    }

    public function editAction()
    {
        if($this->isLoggedIn() == false)
        {
             $this->_redirect('/registration/account/login');
        }
        else
        {
            $siteMessage = new Zend_Session_Namespace('siteMessage');
            
            $jobObj = new Employer_Model_Job();
            $utilObj= new User_Model_Utilities();            

            $jobId = (int)$this->_request->getParam('job-id');

            $owner = $jobObj->getJobOwnerByJobId($jobId);
           
            if($owner)
            {
                 //Get All Locations for interns and employers
                $this->view->locations      = $utilObj->getLocations();

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

                $this->view->postedJobs = $jobObj->getPostedJobsByEmployer($owner, 3);

                if ($this->_request->isPost()) {
                    if($jobObj->updateJob($jobId, $this->_request->getParams()))
                        $siteMessage->message = "Job updated successfully.";
                    else
                        $siteMessage->errorMessage = "Error! Job cant update.";
                }
                else
                    $siteMessage->message = "All fields are required unless otherwise indicated.";

                $jobDetails = $jobObj->getJobDetailsById($jobId);

                $this->view->jobDetails = $jobDetails;

                $this->view->message      = $siteMessage->message;
                $this->view->errorMessage = $siteMessage->errorMessage;
                Zend_Session:: namespaceUnset('siteMessage');

                //this is true because no need to profile to post job
                $this->view->hasProfile = true;
            }            
        }
    }

    /*Active jobs*/
    public function deactiveAction()
    {
        $this->_helper->layout->disableLayout();
        if($this->isLoggedIn())
        {
            $jobObj = new Employer_Model_Job();
            $utilObj= new User_Model_Utilities();

            $jobId = (int)$this->_request->getParam('job-id');

            $owner = $jobObj->getJobOwnerByJobId($jobId);

            if($owner)
            {
                if($jobObj->deactiveJob($jobId))
                        echo '1';
            }
        }
    }

    /*Active jobs*/
    public function activeAction()
    {
        $this->_helper->layout->disableLayout();
        if($this->isLoggedIn())
        {
            $jobObj = new Employer_Model_Job();
            $utilObj= new User_Model_Utilities();

            $jobId = (int)$this->_request->getParam('job-id');

            $owner = $jobObj->getJobOwnerByJobId($jobId);

            if($owner)
            {
                if($jobObj->activeJob($jobId))
                        echo '1';
            }
        }
    }

}