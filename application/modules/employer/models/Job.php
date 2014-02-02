<?php

class Employer_Model_Job extends Zend_Db_Table_Abstract {

    /**
     *
     * @param <type> $userId
     * @param <type> $data 
     */
    public function saveJob($userId, $data = array())
    {
        try {
            if(count($data) > 0)
            {               
                $utilObj = new User_Model_Utilities();
                $locationId = $utilObj->getLocationId($data['location']);
                $hour       = $utilObj->getHourTitleById($data['hour_id']);

                
                $job = array(
                    'employer_id'       => $userId,
                    'job_title'         => $data['job_title'],
                    'company_name'      => $data['company_name'],
                    'location'          => $locationId,
                    'industry'          => $data['industry'],
                    'reports_to'        => $data['reports_to'],
                    'responsibilites'   => $data['responsibilites'],
                    'qualifications'    => $data['qualifications'],
                    'education'         => $data['education'],
                    'pay_type'          => $data['pay_type'],
                    'pay_amount'        => $data['pay_amount'],
                    'hour_id'           => $data['hour_id'],
                    'job_posted_on'     => date('Y-m-d H:i:s', strtotime(Zend_Date::DATETIME)),
                    'status'            => 'Active',
                    'total_view'        => 0,
                    'total_applied'     => 0,
                    'total_search_view' => 0,
                    'total_message'     => 0
                );

                $this->_db->insert('jobs', $job);

                $jobId = $this->_db->lastInsertId();

                foreach ($data['period_id'] as $value) {
                    if (!empty($value)) {
                        $this->_db->insert('job_periods', array('job_id' => $jobId, 'period_id' => $value));                                
                        if ($value == 1) {
                            for ($i = 2; $i <= 5; $i++) {
                                $this->_db->insert('job_periods', array('job_id' => $jobId, 'period_id' => $i));                              
                            }
                            break;
                        }
                    }
                }

                foreach ($data['skill_id'] as $value) {
                    if(!empty ($value)){
                        $this->_db->insert('job_skills', array('job_id' => $jobId, 'skill_id' => $value));                       
                    }
                }

                $jobSchedule = explode('###', $data['schedule_ids']);

                foreach ($jobSchedule as $value) {
                    if(!empty ($value))
                    $this->_db->insert('job_schedules', array('job_id' => $jobId, 'schedule_id' => $value));
                }

                $this->_db->insert('lucene_job_queue', array('job_id' => $jobId, 'action' => 'insert'));
              
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ext) {
            echo $ext->getMessage();
            return false;
        }
    }

    /**
     *
     * @param <type> $uersId
     * @param <type> $limit
     * @return <type> Employerwise jobs with limitation
     */
    public function getPostedJobsByEmployer($uersId, $limit = 0)
    {
        $select = $this->_db->select()
                            ->from('jobs', array('job_id', 'job_title', 'job_posted_on', 'status'))
                            ->where("employer_id = {$uersId}");
                            
        if($limit > 0)
            $select = $select->limit($limit);
                            
       return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
    }

    /**
     *
     * @param <type> $jobId
     * @return <type> 
     */
    public function getJobDetailsById($jobId = 0)
    {
        $jobId = (int)$jobId;     $jobDetails = array();
        
        try{
             //FROM JOB TABLES GET INFO BY JOING TABLES
             $select = $this->_db->select()
                    ->from( array('j' => 'jobs') )
                    ->join( array('l'  => 'locations')  , "l.location_id = j.location" , 'location_name')
                    ->join( array('i'  => 'industries') , "i.industry_id = j.industry" , 'industry_name')
                    ->join( array('h'  => 'hours')      , "h.hour_id     = j.hour_id"  , 'title')
                    ->where("j.job_id = {$jobId}")
                    ->limit(1);
             $jobDetails['jobInfo'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

             //FROM JOB SKILLS
             $select = $this->_db->select()
                    ->from( array('js' => 'job_skills') )
                    ->join( array('s'   => 'skills')  , "js.skill_id = s.skill_id" , 'skill_name')
                    ->where("js.job_id = {$jobId}");
                 
             $jobDetails['jobSkills'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

              //FROM JOB PERIODS
             $select = $this->_db->select()
                    ->from( array('jp'  => 'job_periods') )
                    ->join( array('a'   => 'availabilities')  , "a.availability_id = jp.period_id" , 'title')
                    ->where("jp.job_id = {$jobId}");

             $jobDetails['jobPeriods'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

              //FROM JOB PERIODS
             $select = $this->_db->select()
                    ->from( array('j'  => 'jobs'), 'job_id' )
                    ->join( array('e'   => 'educations')  , "e.education_id = j.education" , array('education_id','education_title'))
                    ->where("j.job_id = {$jobId}");

             $jobDetails['jobEducation'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
             
              //FROM JOB PERIODS
             $select = $this->_db->select()
                    ->from('job_schedules')
                    ->where("job_id = {$jobId}");

             $jobDetails['jobSchedules'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);


             //JOB COMPANY OWNER
             $select = $this->_db->select()
                ->from( array('j'  => 'jobs') , 'job_id')
                ->join( array('ep'   => 'employer_profiles')  , "ep.user_id = j.employer_id", 'logo_file')
                ->where("j.job_id = {$jobId}");

             $jobDetails['jobCompany'] = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

             $this->_db->update('jobs',
                                array('total_view' => new Zend_Db_Expr('total_view+1')),
                                "job_id = {$jobId}");             
             
             return $jobDetails;

        }
        catch (Zend_Db_Exception $ext) {
            echo $ext->getMessage();
        }
    }

    /**
     *
     * @param <type> $jobId
     * @return <type>
     */
    public function getJobOwnerByJobId($jobId = null)
    {
        if(isset ($jobId))
        {
            $select = $this->_db->select()
                    ->from("jobs", "employer_id")
                    ->where("job_id = $jobId")
                    ->limit(1);
            return $this->_db->fetchOne($select);            
        }
    }

    public function getTopJobIds($limit = null)
    {
        $select = $this->_db->select()
                ->from('jobs', 'job_id')
                ->order("total_view DESC");
        if(isset ($limit))
            $select->limit ((int)$limit);

        return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
    }

    public function updateJobIndex($offset=0, $limit=1)
    {
        $jobTitle = '';
        $companyName = '';
        $responsibilites  = '';
        $qualifications  = '';
        $skills = '';
        $hour = '';
        $interest = '';
        $available = '';
        $location = '';
        $payType = '';
        $education = '';
        $schedules = '';

        try {
            $select = $this->_db->select()
                            ->from('lucene_job_queue');                            

            $jobIds = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

            $path  = str_replace('\\', '/', getcwd() . '/data/indexes/jobs');
            $doc   = new Zend_Search_Lucene_Document();
            $index = Zend_Search_Lucene::open($path);
          
            foreach ($jobIds as $job) {

                switch ($job->action)
                {
                    case 'insert' :

                        $jobInfo     = $this->getJobDetailsById($job->job_id);
                        
                        $jobId       = $job->job_id;
                        $jobTitle    = $jobInfo['jobInfo'][0]->job_title;
                        $companyName = $jobInfo['jobInfo'][0]->company_name;
                        $responsibilites = $jobInfo['jobInfo'][0]->responsibilites;
                        $qualifications  = $jobInfo['jobInfo'][0]->qualifications;
                        $location  = $jobInfo['jobInfo'][0]->location_name;
                        $hour = $jobInfo['jobInfo'][0]->title;
                        $interest = $jobInfo['jobInfo'][0]->industry_name;
                        $payType  = $jobInfo['jobInfo'][0]->pay_type;
                        $education = $jobInfo['jobEducation']->education_title;
                        
                        foreach ($jobInfo['jobSkills'] as $skill) $skills .= $skill->skill_name. ', ';
                        foreach ($jobInfo['jobPeriods'] as $availability)  $available .= $availability->title . ', ';
                        foreach ($jobInfo['jobSchedules'] as $schedule)  $schedules .= ",{$schedule->schedule_id},";

//                      echo $jobId .'<br />';   echo $jobTitle .'<br />';  echo $companyName .'<br />';  echo $responsibilites .'<br />';                       echo $qualifications .'<br />';                        echo $location .'<br />';                        echo $hour .'<br />';                        echo $interest .'<br />';
//                      echo $payType .'<br />';  echo $skills .'<br />';   echo $available .'<br /><br />';

                        $doc->addField(Zend_Search_Lucene_Field::keyword('job_id', $jobId));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('job_title', $jobTitle));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('company_name', $companyName));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('responsibilites', $responsibilites));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('qualifications', $qualifications));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('skill', $skills));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('hour', $hour));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('education', $education));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('schedule', $schedules));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('interest', $interest));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('availability', $available));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('location', $location));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('pay_type', $payType));

                        $index->addDocument($doc);
                        $index->commit();
                        $index->optimize();
                        break;

                    case 'update' :
                        //=============DELETE===============
                        $jobId       = $job->job_id;
                        $hits = $index->find('job_id:'.$jobId);
                        foreach ($hits as $hit)
                            $index->delete($hit->id);
                        
                        $index->commit();                       

                        //=============UPDATE==============
                        $jobInfo     = $this->getJobDetailsById($job->job_id);

                        $jobId       = $job->job_id;
                        $jobTitle    = $jobInfo['jobInfo'][0]->job_title;
                        $companyName = $jobInfo['jobInfo'][0]->company_name;
                        $responsibilites = $jobInfo['jobInfo'][0]->responsibilites;
                        $qualifications  = $jobInfo['jobInfo'][0]->qualifications;
                        $location  = $jobInfo['jobInfo'][0]->location_name;
                        $hour = $jobInfo['jobInfo'][0]->title;
                        $interest = $jobInfo['jobInfo'][0]->industry_name;
                        $payType  = $jobInfo['jobInfo'][0]->pay_type;
                        $education = $jobInfo['jobEducation']->education_title;

                        foreach ($jobInfo['jobSkills'] as $skill) $skills .= $skill->skill_name. ', ';
                        foreach ($jobInfo['jobPeriods'] as $availability)  $available .= $availability->title . ', ';
                        foreach ($jobInfo['jobSchedules'] as $schedule)  $schedules .= ",{$schedule->schedule_id},";

//                      echo $jobId .'<br />';   echo $jobTitle .'<br />';  echo $companyName .'<br />';  echo $responsibilites .'<br />';                       echo $qualifications .'<br />';                        echo $location .'<br />';                        echo $hour .'<br />';                        echo $interest .'<br />';
//                      echo $payType .'<br />';  echo $skills .'<br />';   echo $available .'<br /><br />';

                        $doc->addField(Zend_Search_Lucene_Field::keyword('job_id', $jobId));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('job_title', $jobTitle));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('company_name', $companyName));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('responsibilites', $responsibilites));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('qualifications', $qualifications));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('skill', $skills));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('hour', $hour));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('education', $education));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('schedule', $schedules));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('interest', $interest));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('availability', $available));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('location', $location));
                        $doc->addField(Zend_Search_Lucene_Field::unStored('pay_type', $payType));
                        
                        $index->addDocument($doc);
                        $index->commit();
                        
                        break;
                        
                    case 'delete' :

                         //=============DELETE===============
                        $jobId       = $job->job_id;
                        $hits = $index->find('job_id:'.$jobId);
                        foreach ($hits as $hit)
                            $index->delete($hit->id);
                        
                        $index->commit();
                        
                        break;

                    default :
                        break;
                }
                
                //DELETE FROM QUEUE
                $this->_db->delete('lucene_job_queue', "job_id = {$job->job_id}");
                
            }

            $index->optimize();

        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }


    public function updateJob($jobId, $data = array())
    {
        try {
            if(count($data) > 0)
            {
                $utilObj    = new User_Model_Utilities();
                $locationId = $utilObj->getLocationId($data['location']);
                $hour       = $utilObj->getHourTitleById($data['hour_id']);

                $job = array(                   
                    'job_title'         => $data['job_title'],
                    'company_name'      => $data['company_name'],
                    'location'          => $locationId,
                    'industry'          => $data['industry'],
                    'reports_to'        => $data['reports_to'],
                    'responsibilites'   => $data['responsibilites'],
                    'qualifications'    => $data['qualifications'],
                    'education'         => $data['education'],
                    'pay_type'          => $data['pay_type'],
                    'pay_amount'        => $data['pay_amount'],
                    'hour_id'           => $data['hour_id'],                  
                    'status'            => 'Active'
                );

                $this->_db->update('jobs', $job, "job_id = {$jobId}");

                $this->_db->delete('job_periods', "job_id={$jobId}");
                foreach ($data['period_id'] as $value) {
                    if (!empty($value)) {
                        $this->_db->insert('job_periods', array('job_id' => $jobId, 'period_id' => $value));                        
                        if ($value == 1) {
                            for ($i = 2; $i <= 5; $i++) {
                                $this->_db->insert('job_periods', array('job_id' => $jobId, 'period_id' => $i));                                
                            }
                            break;
                        }
                    }
                }

                $this->_db->delete('job_skills', "job_id={$jobId}");
                foreach ($data['skill_id'] as $value) {
                    if(!empty ($value)){
                        $this->_db->insert('job_skills', array('job_id' => $jobId, 'skill_id' => $value));                        
                    }
                }

                $jobSchedule = explode('###', $data['schedule_ids']);

                $this->_db->delete('job_schedules', "job_id={$jobId}");
                foreach ($jobSchedule as $value) {
                    if(!empty ($value))
                    $this->_db->insert('job_schedules', array('job_id' => $jobId, 'schedule_id' => $value));
                }

                $this->_db->insert('lucene_job_queue', array('job_id' => $jobId, 'action' => 'update'));

                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ext) {            
            return false;
        }
    }

    public function deactiveJob($jobId)
    {
        if($this->_db->update("jobs", array('status' => 'Inactive'), "job_id = {$jobId}")) return true;
        else return false;
    }

    public function activeJob($jobId)
    {
        if($this->_db->update("jobs", array('status' => 'Active'), "job_id = {$jobId}")) return true;
        else return false;
    }
}

?>