<?php

class Search_Model_Intern extends Zend_Db_Table_Abstract {

    public function internSearch($data = array())
    {
        
        if(isset ($data['availability']) )
        {
            $availabilityId = (int)$data['availability'];
            
            $select = $this->_db->select()
                    ->from( array('up' => 'user_profiles'), "up.user_id" )
                    ->join( array('a'  =>'user_availabilities'), "a.user_id = up.user_id", "a.user_id")
                    ->where("a.availability_id = $availabilityId")
                    ->order("up.created_on DESC"); 
        }
        elseif (isset ($data['skill']))
        {
            $skillId = (int)$data['skill'];

            $select = $this->_db->select()
                    ->from( array('up' => 'user_profiles') )
                    ->join( array('us'  =>'user_skills'), "us.user_id = up.user_id")
                    ->where("us.skill_id = $skillId")
                    ->order("up.created_on DESC");
        }
        elseif (isset ($data['industry']))
        {
            $industryId = (int)$data['industry'];

            $select = $this->_db->select()
                    ->from( array('up' => 'user_profiles') )
                    ->join( array('uji'  =>'user_job_interests'), "uji.user_id = up.user_id")
                    ->where("uji.interested_in = $industryId")
                    ->order("up.created_on DESC");
        }
        elseif (isset ($data['location']))
        {
            $locationId = (int)$data['location'];

             $select = $this->_db->select()
                    ->from( 'user_profiles' )
                    ->where("location_id = $locationId")
                    ->order("created_on DESC");            
        }

         if(count($this->_db->fetchAll($select) > 0))
         {
             $ids = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
             foreach ($ids as $id) {
                    $this->_db->update('user_profiles',
                            array('appeared_in_search' => new Zend_Db_Expr('appeared_in_search+1')),
                            "user_id = {$id->user_id}");
             }
         }

         return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
    }

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

               
                 //FROM USER  JOB INTERESTS
                 $select = $this->_db->select()
                        ->from( array('uji'  => 'user_job_interests') )
                        ->join( array('i'   => 'industries')  , "i.industry_id = uji.interested_in" , 'industry_name')
                        ->where("uji.user_id = {$userId}");

                 if(count($this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ)) > 0)
                 $internInfo['userInterests'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

             
                 return $internInfo;

            }
            catch (Zend_Db_Exception $ext) {
                echo $ext->getMessage();
            }
        }
    }

    /**
     *
     * @param <type> $ids
     * @param <type> $sortBy
     * @return <type> 
     */
    public function getFilterIds($ids, $sortBy = 'desc') {
        try {
            $select = $this->_db->select()                            
                            ->from('user_profiles', 'user_id')
                            ->where("user_id IN($ids)");
            if($sortBy == 'desc')
                $select->order("last_updated_on desc");
            else if($sortBy == 'alpha')
                $select->order("first_name");

            $result = array();    $userIds = array();
            
            $result = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

            foreach ($result as $id) {
                $userIds[] = $id->user_id;
            }            
            return $userIds;
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function getAllProfileIds()
    {
        $select = $this->_db->select()->from('user_profiles', 'user_id');

        return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
    }

    public function saveInterSearch($searchName, $criteria, $sendEmail)
    {
        $auth     = Zend_Auth::getInstance();        

        $search_name      =  $searchName;
        $user_id          = $auth->getIdentity()->user_id;
        $send_email_alert = $sendEmail;

        $searchInfo = array(
            'user_id'     => $user_id,
            'search_name' => $search_name,
            'search_url'  => $criteria,
            'send_email_alert' => $send_email_alert
        );

        if($this->_db->insert('employer_saved_searches', $searchInfo)) return true;
        else return false;
    }

    public function getEmployerSavedSearch($userId, $limit)
    {
        try{
            $select = $this->_db->select()
                ->from('employer_saved_searches')
                ->where("user_id = $userId");
            if(isset ($limit))
                $select->limit ($limit);

                return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
        }
        catch(Exception $ex)
        {
            return false;
        }        
    }

    public function deleteEmployerSearch($userId, $searchId)
    {
        $this->_db->delete('employer_saved_searches', "saved_search_id  = {$searchId}");

        return $this->getEmployerSavedSearch($userId, 5);
    }
}

?>