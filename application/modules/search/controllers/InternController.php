<?php

class Search_InternController extends Zend_Controller_Action
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
        $utilObj = new User_Model_Utilities();
        $profileObj    = new User_Model_Profile();
        

        //COMMON INFORMATION FOR ALL HOME VIEW
        $availablity   = $utilObj->getAvailabilites();
        $skills        = $utilObj->getSkills();
        $industry      = $utilObj->getIndustries();
        $location      = $utilObj->getLocations();


        $profileIds = $profileObj->getTopProfileIds(5);

        foreach ($profileIds as $value) {
            $topInternProfiles[] = $profileObj->getInternProfileInfo($value->user_id);
        }

        $this->view->topInternProfiles = $topInternProfiles;


        //bind data into view

        $this->view->availablity = $availablity;
        $this->view->skills      = $skills;
        $this->view->industry    = $industry;
        $this->view->location    = $location;
    }  

    public function internSearchResultsAction()
    {
        $utilObj = new User_Model_Utilities();        
        $internObj = new Search_Model_Intern();

        $searchResults = array();      $serializedResult = array();

        $perPage = $this->_request->getParam('per-page');
        $perPage = isset ($perPage) ? $perPage : 50;

        $sortBy  = $this->_request->getParam('sort-by');
        $sortBy  = isset ($sortBy) ? $sortBy : 'desc';
        
        $page=$this->_getParam('page',1);
        
        $keyword     = $this->_request->getParam('keyword');
        $city        = $this->_request->getParam('location_name');
        $hours       = $this->_request->getParam('hours');
        $seasons     = $this->_request->getParam('seasons');
        $skill       = $this->_request->getParam('skill');
        $education   = $this->_request->getParam('education');
        $industry    = $this->_request->getParam('industry');
        $schedule    = $this->_request->getParam('schedule_ids');
        $graphsc     = array();
        
        if($keyword == 'interns' || $keyword == 'type keyword or location')
            $keyword = '';

        $query = array(); 

        if($keyword != '')
            $query[] = $keyword;

        if($city != ''){
            $query[] = 'location_name:"'.$city.'"';
        }
        if(count($hours) > 0){
            $subQuery = array();
            foreach ($hours as $value) {
                if(!empty ($value))
                    $subQuery[] = 'hours:"'.$value.'"';
            }
            if(count($subQuery))
            $query[] = '('.implode(" OR ", $subQuery).')';
        }        
        if(count($seasons) > 0){
            $subQuery = array();
            foreach ($seasons as $value) {
                if(!empty ($value))
                    $subQuery[] = 'availability:"'.$value.'"';
            }
            if(count($subQuery))
            $query[] = '('.implode(" OR ", $subQuery).')';
        }
        if(count($skill) > 0){
            $subQuery = array();
            foreach ($skill as $value) {
                if(!empty ($value))
                    $subQuery[] = 'skill:"'.$value.'"';
            }
            if(count($subQuery))
            $query[] = '('.implode(" OR ", $subQuery).')';

        }
        if(count($education) > 0){
            $subQuery = array();
            foreach ($education as $value) {
                if(!empty ($value))
                    $subQuery[] = 'education:"'.$value.'"';
            }
            if(count($subQuery))
            $query[] = '('.implode(" OR ", $subQuery).')';
        }
        if(count($industry) > 0){
            $subQuery = array();
            foreach ($industry as $value) {
                if(!empty ($value))
                    $subQuery[] = 'interest:"'.$value.'"';
            }
            if(count($subQuery))
            $query[] = '('.implode(" OR ", $subQuery).')';

        }
        if($schedule != ''){
            $graphsc = explode('###', $schedule);
            if(count($graphsc) > 0){
                $subQuery = array();
                $graphsc = array_unique($graphsc);
                foreach ($graphsc as $value) {
                    if(!empty ($value))
                        $subQuery[] = 'schedule:",'.$value.',"';
                }
                if(count($subQuery))
                $query[] = '('.implode(" OR ", $subQuery).')';
            }
        }

        $find = implode(" AND ", $query);
        
        if(count($query) == 0)
        {
            $profiles = $internObj->getAllProfileIds();

            foreach ($profiles as $profile)
            {
                $serializedResult[] = $profile->user_id;
            }
        }
        else
        {
            $path = str_replace('\\', '/',getcwd() . '/data/indexes/profiles');

            $index = Zend_Search_Lucene::open($path);

            $profiles = $index->find($find);

            foreach ($profiles as $profile)
            {
                $serializedResult[] = $profile->user_id;
            }
        }

        
        if(count($serializedResult)>0)
        {            
            $paginator = Zend_Paginator::factory($serializedResult);
            $paginator->setItemCountPerPage($perPage);
            $paginator->setCurrentPageNumber($page);

            $this->view->from  = $from = (($page -1) * $perPage) + 1;
            $this->view->to    = $to   = ($paginator->getCurrentPageNumber() * $perPage) > count($serializedResult) ? count($serializedResult) : ($paginator->getCurrentPageNumber() * $perPage);
            $this->view->total         = count($serializedResult);

            if($sortBy == 'best')
                $toSearch = $serializedResult;
            else
                $toSearch = $internObj->getFilterIds( implode(',', $serializedResult), $sortBy);

            $filterIds = array_slice($toSearch, $from-1, $perPage);

            foreach ($filterIds as $value)
            {              
                 $searchResults[] = $internObj->getInternProfileInfo($value);
            }

            $this->view->searchResults = $searchResults;
        
            $this->view->pageLink      = $paginator;
        }
        else
            $this->view->searchResults=$searchResults;

        if($keyword != '')
            $this->view->keyword = $keyword;
        else
            $this->view->keyword = 'type keyword or location';

        
        $this->view->city        = $city; 
        $this->view->hour        = array_unique($hours);
        $this->view->seasons     = array_unique($seasons);
        $this->view->industry    = array_unique($industry);
        $this->view->skill       = array_unique($skill);
        $this->view->education   = array_unique($education);
        $this->view->perPage     = $perPage;
        $this->view->sortBy      = $sortBy;
        $this->view->schedule    = $schedule;
        $this->view->graphsc     = $graphsc;

        //========================================================

        //Get Availablity options for inters employers
        $this->view->availabilites  = $utilObj->getAvailabilites();

        //Get All locations
        $this->view->location       = $utilObj->getLocations();

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

        if($this->isLoggedIn())
            $this->view->loggedin = true;
        else
            $this->view->loggedin = false;
    }

    public function saveSearchAction()
    {
        if($this->isLoggedIn())
        {
            $internObj = new Search_Model_Intern();

            $this->_helper->layout->disableLayout();

            $searchName =  $this->_request->getParam('search_name', 'my saved search.');
            $sendEmail  =  $this->_request->getParam('send_email_alert', 0);
            $criteria   =  $this->_request->getParam('squery');

            if($internObj->saveInterSearch($searchName, $criteria, $sendEmail))
                echo '1';
        }
        else
        {
            echo '0';
        }        
    }

    public function deleteEmployerSearchAction()
    {
       $this->_helper->layout->disableLayout();

       $internObj = new Search_Model_Intern();

       $auth     = Zend_Auth::getInstance();

       $userId = $auth->getIdentity()->user_id;

       $searchId = $this->_request->getParam('search-id', null);

       if(isset ($searchId) && isset ($userId))
       {
            $afterDelete = $internObj->deleteEmployerSearch($userId, $searchId);
            $this->view->saveSearch = $afterDelete;
       }
    }
}