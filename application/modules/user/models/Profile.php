<?php

class User_Model_Profile extends Zend_Db_Table_Abstract {

    public function saveEmployerProfile($data = array())
    {
        try
        {
            if(count($data) > 0)
            {
                $auth   = Zend_Auth::getInstance();
                $userId = $auth->getIdentity()->user_id;
                $utilObj = new User_Model_Utilities();

                $upload = new Zend_File_Transfer_Adapter_Http();
                $file   = $upload->getFileInfo();
                $logoFile = 'default-logo.png';

                if($file['logo_file']['error'] === 0) {
                     // upload the image

                        $ext       = pathinfo($file['logo_file']['name']);
                        $logoFile = $userId.'.'.$ext['extension'];
                        $target_path = str_replace('\\', '/',getcwd() . '/public/uploads/employers/logos/').$logoFile;
                        $upload->addFilter(
                                'Rename', array(
                                    'target' => $target_path,
                                    'overwrite' => true
                                ),
                                'logo_file'
                        );
                        if (!$upload->receive($file['logo_file']['name'])) {
                            $messages = $adapter->getMessages();
                            echo implode("<br />", $messages);
                        }
                        else
                        {
                            $oImage= str_replace('\\', '/',getcwd() . '/public/uploads/employers/logos/').$logoFile;
                            $rImage = str_replace('\\', '/',getcwd() . '/public/uploads/employers/thumbs/').$logoFile;
                            $thumb = Thumb_PhpThumb_PhpThumbFactory::create($oImage);
                            $thumb->resize(61, 61);
                            $thumb->save($rImage, $ext['extension']);
                        }

                }

                $locationId = $utilObj->getLocationId($data['location']);

                //EDIT
                $employerProfileInfo  = array(
                                        'user_id'             => $userId,
                                        'company_name'        => $data['company_name'],
                                        'location'            => $locationId,
                                        'state'               => $data['state'],
                                        'street_address'      => $data['street_address'],
                                        'zip'                 => $data['zip'],
                                        'industry'            => $data['industry'],
                                        'interns_hired'       => $data['interns_hired'],
                                        'company_description' => $data['company_description'],
                                        'logo_file'           => $logoFile,
                                        'video_url'           => $data['video_url'],
                                        'company_website'     => $data['company_website'],
                                        'twitter_url'         => $data['twitter_url'],
                                        'linkedin_url'        => $data['linkedin_url']
                );

                $this->_db->insert('employer_profiles', $employerProfileInfo);
                return true;
            }
            return false;
        }
        catch (Exception $ex){
            return false;
        }
    }

    /**
     *
     * @param <type> $userId
     * @return <type> Company Information based on userid
     */
    public function getEmployerProfileInfo($userId = null)
    {
        $employerInfo = array();
        
        if(isset ($userId))
        {
            $select = $this->_db->select()
                                ->from(array('ep' => 'employer_profiles') )
                                ->join(array('u' => 'users'),'u.user_id = ep.user_id', 'joined_on')
                                ->where("ep.user_id = {$userId}");
            $employerInfo['empInfo'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

            $select = $this->_db->select()
                       ->from(array('ep' => 'employer_profiles'), 'user_id')
                       ->join(array('i' => 'industries'), 'i.industry_id = ep.industry' )
                       ->where("ep.user_id = {$userId}");
            $employerInfo['industry'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

            $select = $this->_db->select()
                    ->from(array('ep' => 'employer_profiles'), 'user_id')
                    ->join(array('l' => 'locations'), 'ep.location = l.location_id')
                    ->where("ep.user_id = {$userId}");

             $employerInfo['location'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);            
        }
       return $employerInfo;
    }


    /**
     *
     * @param <type> $userId
     * @return <type> 
     */
    public function getInternProfileInfo($userId = null)
    {
        if(isset ($userId))
        {
            $internInfo = array();
            try{
                 //FROM JOB TABLES GET INFO BY JOING TABLES
                 $select = $this->_db->select()
                        ->from( array('up' => 'user_profiles') )
                        ->join( array('l'  => 'locations')  , "l.location_id = up.location_id" , 'location_name')                        
                        ->join( array('h'  => 'hours')      , "h.hour_id     = up.hours"  , 'title')
                        ->where("up.user_id = {$userId}")
                        ->limit(1);
                 if(count($this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ)) > 0)
                 $internInfo['internInfo'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
   
                 //FROM USER SKILLS
                 $select = $this->_db->select()
                        ->from( array('us' => 'user_skills') )
                        ->join( array('s'   => 'skills')  , "us.skill_id = s.skill_id" , 'skill_name')
                        ->where("us.user_id = {$userId}");
                 if(count($this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ)) > 0)
                 $internInfo['userSkills'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

                
                  //FROM USER AVAILABILITIES
                 $select = $this->_db->select()
                        ->from( array('ua'  => 'user_availabilities') )
                        ->join( array('a'   => 'availabilities')  , "a.availability_id = ua.availability_id" , 'title')
                        ->where("ua.user_id = {$userId}");

                 if(count($this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ)) > 0)
                 $internInfo['userAvailabilities'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

                 //FROM USER EDUCATIONS
                 $select = $this->_db->select()
                        ->from( array('ue'  => 'user_educations') )
                        ->join( array('e'   => 'educations')  , "e.education_id = ue.degree" , 'education_title')
                        ->where("ue.user_id = {$userId}");
                 if(count($this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ)) > 0)
                 $internInfo['userEducations'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

                 //FROM USER  JOB INTERESTS
                 $select = $this->_db->select()
                        ->from( array('uji'  => 'user_job_interests') )
                        ->join( array('i'   => 'industries')  , "i.industry_id = uji.interested_in" , 'industry_name')
                        ->where("uji.user_id = {$userId}");

                 if(count($this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ)) > 0)
                 $internInfo['userInterests'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

                 //FROM USER  SCHEDULES
                 $select = $this->_db->select()
                        ->from( array('us'  => 'user_schedules') )
                        ->join( array('s'   => 'schedules')  , "s.schedule_id = us.schedule_id" , 'schedule_id')
                        ->where("us.user_id = {$userId}");
                 if(count($this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ)) > 0)
                 $internInfo['userSchedules'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

                 return $internInfo;

            }
            catch (Zend_Db_Exception $ext) {
                echo $ext->getMessage();               
            }
        }
    }

    /**
     *
     * @param <type> $data 
     */
    public function saveInternProfile($data = array()) {
        try {
            if (count($data) > 0)
            {            

                $auth = Zend_Auth::getInstance();
                $userId = $auth->getIdentity()->user_id;
                $utilObj = new User_Model_Utilities();

                $upload = new Zend_File_Transfer_Adapter_Http();
                $file = $upload->getFileInfo();

                $locationId = $utilObj->getLocationId($data['location_id']);                

                $photoFileName = 'default-photo.png';
                $resumeFileName = '';

                if ($file['photo_file_name']['error'] === 0) {

                    // upload the image

                    $ext = pathinfo($file['photo_file_name']['name']);
                    $photoFileName = $userId . '.' . $ext['extension'];
                    $target_path = str_replace('\\', '/', getcwd() . '/public/uploads/interns/images/') . $photoFileName;
                    $upload->addFilter(
                            'Rename', array(
                        'target' => $target_path,
                        'overwrite' => true
                            ),
                            'photo_file_name'
                    );
                    if (!$upload->receive($file['photo_file_name']['name'])) {
                        $messages = $adapter->getMessages();
                        echo implode("<br />", $messages);
                    } else {
                        $oImage = str_replace('\\', '/', getcwd() . '/public/uploads/interns/images/') . $photoFileName;
                        $rImage = str_replace('\\', '/', getcwd() . '/public/uploads/interns/thumbs/') . $photoFileName;
                        $thumb = Thumb_PhpThumb_PhpThumbFactory::create($oImage);
                        $thumb->resize(61, 61);
                        $thumb->save($rImage, $ext['extension']);
                    }
                }
                if ($file['resume_file_name']['error'] === 0) {
                    // upload the image

                    $ext = pathinfo($file['resume_file_name']['name']);
                    $resumeFileName = $userId . '.' . $ext['extension'];
                    $target_path = str_replace('\\', '/', getcwd() . '/public/uploads/interns/resumes/') . $resumeFileName;
                    $upload->addFilter(
                            'Rename', array(
                        'target' => $target_path,
                        'overwrite' => true
                            ),
                            'resume_file_name'
                    );
                    if (!$upload->receive($file['resume_file_name']['name'])) {
                        $messages = $adapter->getMessages();                        
                    }
                }
                $internProfileInfo = array(
                    'user_id' => $userId,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'profile_title' => $data['profile_title'],
                    'hours' => $data['hours'],
                    'location_id' => $locationId,
                    'about_me' => $data['about_me'],
                    'photo_file_name' => $photoFileName,
                    'resume_file_name' => $resumeFileName,
                    'linkedin_url' => $data['linkedin_url'],
                    'twitter_url' => $data['twitter_url'],
                    'personal_url' => $data['personal_url'],
                    'video_url' => $data['video_url'],
                    'created_on' => date('Y-m-d H:i:s', strtotime(Zend_Date::DATETIME)),
                    'keep_name_private' => isset($data['keep_name_private']) ? 1 : 0 ,
                    'virtual_job'       => isset($data['virtual_job']) ? 1 : 0
                );

                $this->_db->insert('user_profiles', $internProfileInfo);

                /* --------------------------------------------- */

                foreach ($data['availability_id'] as $value) {
                    if (!empty($value)) {
                        $this->_db->insert('user_availabilities', array('user_id' => $userId, 'availability_id' => $value));                        
                        if ($value == 1) {
                            for ($i = 2; $i <= 5; $i++) {
                                $this->_db->insert('user_availabilities', array('user_id' => $userId, 'availability_id' => $i));
                            }
                            break;
                        }
                    }
                }

                /* --------------------------------------------- */
               
                foreach ($data['skill_id'] as $value) {
                    if (!empty($value)){
                        $this->_db->insert('user_skills', array('user_id' => $userId, 'skill_id' => $value));                       
                    }
                }

                /* --------------------------------------------- */
              
        
                $no = $data['nedu'];
                
                for($i=0; $i<$no; $i++)
                {
                    if(isset ($data['school_name'][$i]) && $data['school_name'][$i] != 'Name of College / University' && $data['degree'][$i] != '')
                    {
                         $educationInfo = array(
                            'user_id' => $userId,
                            'school_name' => $data['school_name'][$i],
                            'degree' => isset ($data['degree'][$i]) ? $data['degree'][$i] : '',
                            'graduation_year' => isset ($data['graduation_year'][$i]) ? $data['graduation_year'][$i] : '',
                            'major' => isset ($data['major'][$i]) && $data['major'][$i] != 'Major' ? $data['major'][$i] : '',
                            'sort_order' => 0
                        );

                        $this->_db->insert('user_educations', $educationInfo);
                    }
                }
                             

                /* --------------------------------------------- */               
                foreach ($data['interested_in'] as $value) {
                    if (!empty($value)){
                        $this->_db->insert('user_job_interests', array(
                            'user_id' => $userId,
                            'interested_in' => $value,
                            'sort_order' => 0));                       
                    }
                }

                /* --------------------------------------------- */
                $jobSchedule = explode('###', $data['schedule_ids']);

                foreach ($jobSchedule as $value) {
                    if (!empty($value))
                        $this->_db->insert('user_schedules', array('user_id' => $userId, 'schedule_id' => $value));
                }

                $this->_db->insert('lucene_user_profile_queue', array('user_id' => $userId, 'action' => 'insert'));
             
                return true;
            }
            return false;
        } catch (Exception $ex) {           
            return false;
        }
    }

    /**
     *
     * @param <type> $userId
     */
    public function increaseProfileClicked($userId = null)
    {
        if(isset ($userId))
        {
          $this->_db->update('user_profiles',
                            array('profile_clicked' => new Zend_Db_Expr('profile_clicked+1')),
                            "user_id = {$userId}");
        }
    }

    /**
     *
     * @param <type> $userId
     * @return <type> 
     */
    public function getInternStats($userId = null)
    {
        if(isset ($userId))
        {
            $select = $this->_db->select()
                    ->from('user_profiles', array('appeared_in_search', 'profile_clicked'))
                    ->where("user_id = {$userId}")
                    ->limit(1);

            return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
        }
    }

    /**
     *
     * @param <type> $userId
     * @return <type> 
     */
    public function getProfileOwnerByJobId($userId = null)
    {
        if(isset ($userId))
        {
            $select = $this->_db->select()
                    ->from("user_profiles", "user_id")
                    ->where("user_id = $userId")
                    ->limit(1);
            return $this->_db->fetchOne($select);
        }
    }

    /**
     *
     * @param <type> $limit
     */
    public function getTopProfileIds($limit = null)
    {
        $select = $this->_db->select()
                ->from('user_profiles', 'user_id')
                ->order("profile_clicked DESC");
        if(isset ($limit))
            $select->limit ((int)$limit);
        
        return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
    }


    public function updateProfileIndex($offset=0, $limit=1)
    {
        /*Initialize variable which will be index */      
        $location      = '';
        $profileTitle  = '';
        $aboutMe       = '';
        $hours         = '';
        $education     = '';
        $interest      = '';
        $skills        = '';
        $available     = '';
        $schedules     = '';
        $schoolName    = '';
        
        $utilObj       = new User_Model_Utilities();

        try {
            $select = $this->_db->select()->from('lucene_user_profile_queue');
                            
            $profileIds = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

            $path  = str_replace('\\', '/', getcwd() . '/data/indexes/profiles');
            $doc   = new Zend_Search_Lucene_Document();
            $index = Zend_Search_Lucene::open($path);

            foreach ($profileIds as $profile)
            {               

                switch ($profile->action)
                {
                    case 'insert':

                        $internInfo = $this->getInternProfileInfo($profile->user_id);
                        
                        $userId       = $profile->user_id;                       
                        $location     = $internInfo['internInfo'][0]->location_name;
                        $profileTitle = $internInfo['internInfo'][0]->profile_title;
                        $aboutMe      = $internInfo['internInfo'][0]->about_me;
                        $hours        = $internInfo['internInfo'][0]->title;     

                        $noedu = count($internInfo['userEducations']);

                        for($k=0; $k<$noedu; $k++)
                        {
                            $schoolName .= $internInfo['userEducations'][$k]->school_name. ', ';
                            $education  .= $internInfo['userEducations'][$k]->education_title. ', ';
                        }

                        foreach ($internInfo['userSkills'] as $skill) $skills .= $skill->skill_name . ', ';
                        foreach ($internInfo['userInterests'] as $industry) $interest .= $industry->industry_name . ', ';
                        foreach ($internInfo['userAvailabilities'] as $availability)  $available .= $availability->title . ', ';
                        foreach ($internInfo['userSchedules'] as $schedule) $schedules .= ",$schedule->schedule_id,";

//                      echo $userId . '<br />'; echo $location . '<br />'; echo $profileTitle . '<br />';
//                      echo $aboutMe . '<br />'; echo $hours . '<br />'; echo $education . '<br />'; echo $skills . '<br />'; echo $interest . '<br />'; echo $available . '<br />';  echo $schedules . '<br />'; echo "=============================================================================<br />";

                        $doc->addField(Zend_Search_Lucene_Field::keyword('user_id', $userId));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('location_name', $location));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('profile_title', $profileTitle));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('about_me', $aboutMe));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('hours', $hours));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('school', $schoolName));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('education', $education));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('schedule', $schedules));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('skill', $skills));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('interest', $interest));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('availability', $available));

                        $index->addDocument($doc);
                        $index->commit();                      

                        break;                       

                 case 'update':

                        //=============DELETE===============
                        $userId       = $profile->user_id;   
                        $hits = $index->find('user_id:'.$userId);
                        foreach ($hits as $hit)
                            $index->delete($hit->id);          

                        //=============ADD=============

                        $internInfo = $this->getInternProfileInfo($profile->user_id);

                        $userId       = $profile->user_id;
                        $firstName    = $internInfo['internInfo'][0]->first_name;
                        $lastName     = $internInfo['internInfo'][0]->last_name;
                        $location     = $internInfo['internInfo'][0]->location_name;
                        $profileTitle = $internInfo['internInfo'][0]->profile_title;
                        $aboutMe      = $internInfo['internInfo'][0]->about_me;
                        $hours        = $internInfo['internInfo'][0]->title;
                        
                        $noedu = count($internInfo['userEducations']);

                        for($k=0; $k<$noedu; $k++)
                        {
                            $schoolName .= $internInfo['userEducations'][$k]->school_name. ', ';
                            $education  .= $internInfo['userEducations'][$k]->education_title. ', ';
                        }

                        foreach ($internInfo['userSkills'] as $skill) $skills .= $skill->skill_name . ', ';                        
                        foreach ($internInfo['userInterests'] as $industry) $interest .= $industry->industry_name . ', ';
                        foreach ($internInfo['userAvailabilities'] as $availability)  $available .= $availability->title . ', ';
                        foreach ($internInfo['userSchedules'] as $schedule) $schedules .= ",$schedule->schedule_id,";

        //               echo $userId . '<br />'; echo $firstName . '<br />'; echo $lastName . '<br />'; echo $location . '<br />'; echo $profileTitle . '<br />';
        //               echo $aboutMe . '<br />'; echo $hours . '<br />'; echo $education . '<br />'; echo $skills . '<br />'; echo $interest . '<br />'; echo $available . '<br />';  echo "=============================================================================<br />";

                        $doc->addField(Zend_Search_Lucene_Field::keyword('user_id', $userId));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('location_name', $location));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('profile_title', $profileTitle));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('about_me', $aboutMe));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('hours', $hours));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('school', $schoolName));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('education', $education));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('schedule', $schedules));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('skill', $skills));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('interest', $interest));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('availability', $available));

                        $index->addDocument($doc);
                        $index->commit();

                        break;

                case 'delete':
                        $userId = $profile->user_id;                                           
                        $hits   = $index->find('user_id:'.$userId);
                        foreach ($hits as $hit)
                            $index->delete($hit->id);                        
                        $index->commit();                      
                        break;

                    default:
                        break;
                }               

                //DELETE FROM QUEUE
                $this->_db->delete('lucene_user_profile_queue', "user_id = {$profile->user_id}");
               
            }

            $index->optimize();
            
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }


    /**
     *
     * @param <type> $data
    */
    public function updateInternProfile($data = array()) {
        try {
            if (count($data) > 0)
            {
                $auth = Zend_Auth::getInstance();
                $userId = $auth->getIdentity()->user_id;
                $utilObj = new User_Model_Utilities();

                $upload = new Zend_File_Transfer_Adapter_Http();
                $file = $upload->getFileInfo();

                $locationId = $utilObj->getLocationId($data['location_id']);

                if ($file['photo_file_name']['error'] === 0) {

                    // upload the image

                    $ext = pathinfo($file['photo_file_name']['name']);
                    $photoFileName = $userId . '.' . $ext['extension'];
                    $target_path = str_replace('\\', '/', getcwd() . '/public/uploads/interns/images/') . $photoFileName;
                    $upload->addFilter(
                            'Rename', array(
                        'target' => $target_path,
                        'overwrite' => true
                            ),
                            'photo_file_name'
                    );
                    if (!$upload->receive($file['photo_file_name']['name'])) {
                        $messages = $adapter->getMessages();
                        echo implode("<br />", $messages);
                    } else {
                        $oImage = str_replace('\\', '/', getcwd() . '/public/uploads/interns/images/') . $photoFileName;
                        $rImage = str_replace('\\', '/', getcwd() . '/public/uploads/interns/thumbs/') . $photoFileName;
                        $thumb = Thumb_PhpThumb_PhpThumbFactory::create($oImage);
                        $thumb->resize(61, 61);
                        $thumb->save($rImage, $ext['extension']);
                    }
                }
                if ($file['resume_file_name']['error'] === 0) {
                    // upload the image

                    $ext = pathinfo($file['resume_file_name']['name']);
                    $resumeFileName = $userId . '.' . $ext['extension'];
                    $target_path = str_replace('\\', '/', getcwd() . '/public/uploads/interns/resumes/') . $resumeFileName;
                    $upload->addFilter(
                            'Rename', array(
                        'target' => $target_path,
                        'overwrite' => true
                            ),
                            'resume_file_name'
                    );
                    if (!$upload->receive($file['resume_file_name']['name'])) {
                        $messages = $adapter->getMessages();
                    }
                }
                $internProfileInfo = array(
                    'user_id' => $userId,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'profile_title' => $data['profile_title'],
                    'hours' => $data['hours'],
                    'location_id' => $locationId,
                    'about_me' => $data['about_me'],                    
                    'linkedin_url' => $data['linkedin_url'],
                    'twitter_url' => $data['twitter_url'],
                    'personal_url' => $data['personal_url'],
                    'video_url' => $data['video_url'],                    
                    'keep_name_private' => isset($data['keep_name_private']) ? 1 : 0 ,
                    'virtual_job'       => isset($data['virtual_job']) ? 1 : 0
                );

                if(isset ($photoFileName))  $internProfileInfo['photo_file_name'] = $photoFileName;
                if(isset ($resumeFileName)) $internProfileInfo['resume_file_name'] = $resumeFileName;
                
                $this->_db->update('user_profiles', $internProfileInfo, "user_id = {$userId}");

                /* --------------------------------------------- */

                $this->_db->delete("user_availabilities", "user_id = {$userId}");
                foreach ($data['availability_id'] as $value) {
                    if (!empty($value)) {
                        $this->_db->insert('user_availabilities', array('user_id' => $userId, 'availability_id' => $value));
                        if ($value == 1) {
                            for ($i = 2; $i <= 5; $i++) {
                                $this->_db->insert('user_availabilities', array('user_id' => $userId, 'availability_id' => $i));
                            }
                            break;
                        }
                    }
                }

                /* --------------------------------------------- */
                $this->_db->delete("user_skills", "user_id = {$userId}");
                foreach ($data['skill_id'] as $value) {
                    if (!empty($value)){
                        $this->_db->insert('user_skills', array('user_id' => $userId, 'skill_id' => $value));
                    }
                }

                /* --------------------------------------------- */
                $this->_db->delete("user_educations", "user_id = {$userId}");               
         

                $no = $data['nedu'];

                for($i=0; $i<$no; $i++)
                {
                    if(isset ($data['school_name'][$i]) && $data['school_name'][$i] != 'Name of College / University' && $data['degree'][$i] != '')
                    {
                         $educationInfo = array(
                            'user_id' => $userId,
                            'school_name' => $data['school_name'][$i],
                            'degree' => isset ($data['degree'][$i]) ? $data['degree'][$i] : '',
                            'graduation_year' => isset ($data['graduation_year'][$i]) ? $data['graduation_year'][$i] : '',
                            'major' => isset ($data['major'][$i]) && $data['major'][$i] != 'Major' ? $data['major'][$i] : '',
                            'sort_order' => 0
                        );

                        $this->_db->insert('user_educations', $educationInfo);
                    }
                }

                /* --------------------------------------------- */
                $this->_db->delete("user_job_interests", "user_id = {$userId}");
                foreach ($data['interested_in'] as $value) {
                    if (!empty($value)){
                        $this->_db->insert('user_job_interests', array(
                            'user_id' => $userId,
                            'interested_in' => $value,
                            'sort_order' => 0));
                    }
                }

                /* --------------------------------------------- */
                $jobSchedule = explode('###', $data['schedule_ids']);

                $this->_db->delete("user_schedules", "user_id = {$userId}");
                foreach ($jobSchedule as $value) {
                    if (!empty($value))
                        $this->_db->insert('user_schedules', array('user_id' => $userId, 'schedule_id' => $value));
                }

                $this->_db->insert('lucene_user_profile_queue', array('user_id' => $userId, 'action' => 'update'));

                return true;
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }

    public function updateEmployerProfile($data = array())
    {
        try
        {
            if(count($data) > 0)
            {
                $auth   = Zend_Auth::getInstance();
                $userId = $auth->getIdentity()->user_id;
                $utilObj = new User_Model_Utilities();

                $upload = new Zend_File_Transfer_Adapter_Http();
                $file   = $upload->getFileInfo();              

                if($file['logo_file']['error'] === 0) {
                     // upload the image

                        $ext       = pathinfo($file['logo_file']['name']);
                        $logoFile = $userId.'.'.$ext['extension'];
                        $target_path = str_replace('\\', '/',getcwd() . '/public/uploads/employers/logos/').$logoFile;
                        $upload->addFilter(
                                'Rename', array(
                                    'target' => $target_path,
                                    'overwrite' => true
                                ),
                                'logo_file'
                        );
                        if (!$upload->receive($file['logo_file']['name'])) {
                            $messages = $adapter->getMessages();
                            echo implode("<br />", $messages);
                        }
                        else
                        {
                            $oImage= str_replace('\\', '/',getcwd() . '/public/uploads/employers/logos/').$logoFile;
                            $rImage = str_replace('\\', '/',getcwd() . '/public/uploads/employers/thumbs/').$logoFile;
                            $thumb = Thumb_PhpThumb_PhpThumbFactory::create($oImage);
                            $thumb->resize(61, 61);
                            $thumb->save($rImage, $ext['extension']);
                        }

                }

                $locationId = $utilObj->getLocationId($data['location']);

                //EDIT
                $employerProfileInfo  = array(                                        
                                        'company_name'        => $data['company_name'],
                                        'location'            => $locationId,
                                        'state'               => $data['state'],
                                        'street_address'      => $data['street_address'],
                                        'zip'                 => $data['zip'],
                                        'industry'            => $data['industry'],
                                        'interns_hired'       => $data['interns_hired'],
                                        'company_description' => $data['company_description'],                                        
                                        'video_url'           => $data['video_url'],
                                        'company_website'     => $data['company_website'],
                                        'twitter_url'         => $data['twitter_url'],
                                        'linkedin_url'        => $data['linkedin_url']
                );

                if(isset ($logoFile))  $employerProfileInfo['logo_file'] = $logoFile;              

                $this->_db->update('employer_profiles', $employerProfileInfo, "user_id = {$userId}");

                return true;
            }
            return false;
        }
        catch (Exception $ex){
            return false;
        }
    }
}
?>