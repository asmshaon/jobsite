<?php

class Search_Model_Job extends Zend_Db_Table_Abstract {

    public function jobSearch($data = array()) {
        try {
            if (isset($data['availability'])) {
                $availabilityId = (int) $data['availability'];

                $select = $this->_db->select()
                                ->from(array('j' => 'jobs'), "j.job_id")
                                ->join(array('jp' => 'job_periods'), "jp.job_id = j.job_id", "jp.job_id")
                                ->where("jp.period_id = $availabilityId")
                                ->order("j.job_posted_on DESC");
            } elseif (isset($data['skill'])) {
                $skillId = (int) $data['skill'];

                $select = $this->_db->select()
                                ->from(array('j' => 'jobs'), "j.job_id")
                                ->join(array('js' => 'job_skills'), "js.job_id = j.job_id")
                                ->where("js.skill_id = $skillId")
                                ->order("j.job_posted_on DESC");
            } elseif (isset($data['industry'])) {
                $industryId = (int) $data['industry'];

                $select = $this->_db->select()
                                ->from(array('j' => 'jobs'), "j.job_id")
                                ->where("j.industry = $industryId")
                                ->order("j.job_posted_on DESC");
            } elseif (isset($data['location'])) {
                $locationId = (int) $data['location'];

                $select = $this->_db->select()
                                ->from(array('j' => 'jobs'), "j.job_id")
                                ->where("j.location = $locationId")
                                ->order("j.job_posted_on DESC");
            }

            if(count($this->_db->fetchAll($select) > 0))
            {
                 $ids = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
                 foreach ($ids as $id) {
                        $this->_db->update('jobs',
                                array('total_search_view' => new Zend_Db_Expr('total_search_view+1')),
                                "job_id = {$id->job_id}");
                 }
            }

            return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
        } catch (Zend_Db_Exception $e) {
            echo $e->getMessage();
        }
    }

     public function getFilterIds($ids, $sortBy = 'desc') {
        try {
            $select = $this->_db->select()
                            ->from('jobs', 'job_id')
                            ->where("job_id IN($ids)");

             if($sortBy == 'desc')
                $select->order("job_posted_on desc");
            else if($sortBy == 'alpha')
                $select->order("job_title");

            $result = array();    $jobIds = array();

            $result = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

            foreach ($result as $id) {
                $jobIds[] = $id->job_id;
            }
            return $jobIds;
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function getAllJobIds()
    {
        $select = $this->_db->select()->from('jobs', 'job_id');

        return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
    }


    public function saveJobSearch($searchName, $criteria , $sendEmail)
    {
        $auth     = Zend_Auth::getInstance();

        $search_name      = $searchName;
        $user_id          = $auth->getIdentity()->user_id;
        $send_email_alert = $sendEmail;

        $searchInfo = array(
            'user_id'     => $user_id,
            'search_name' => $search_name,
            'search_url'  => $criteria,
            'send_email_alert' => $send_email_alert
        );

        if($this->_db->insert('user_saved_searches', $searchInfo)) return true;
        else return false;
    }


    public function getJobSavedSearch($userId, $limit)
    {
        try{
            $select = $this->_db->select()
                ->from('user_saved_searches')
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


    public function deleteJobSearch($userId, $searchId)
    {
        $this->_db->delete('user_saved_searches', "user_saved_search_id  = {$searchId}");

        return $this->getJobSavedSearch($userId, 5);
    }


}
?>