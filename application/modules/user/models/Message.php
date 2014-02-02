<?php

class User_Model_Message extends Zend_Db_Table_Abstract {

    public function sendMessage($data = array())
    {
        if(isset ($data['mail']) && $data['mail'] == 1)
        {
            $html_massage = "
                            <div style='font-family: verdana;font-size: 13px;color:#6C9FB9;margin: 0 auto;width: 600px; min-height: 300px; border: solid 13px #3C80A2; overflow:hidden;background-color: #FFF'>
                            <div style='background-color:#3C80A2;color: #FFF; font-size: 18px; padding:30px 0px 5px 10px;'>
                                </div>

                            <div style='padding:30px 0px 15px 15px;'>


                                <a href='http://beta.urbaninterns.com/user/profile/intern-profile/profile-id/{$data['from']}'> Click Here for view his profile.</a> <br /><br />

                               Thanks<br />

                               http://beta.urbaninterns.com

                            </div>

                        </div>";

//                            $mail = new Zend_Mail();
//                            $mail->setBodyHtml($html_massage);
//                            $mail->setFrom('notifications@urbaninterns.com');
//                            $mail->addTo($data['email']);
//                            $mail->setSubject("Application for {$data['job_title']}");
//                            $mail->send();
        }

        if(isset ($data['apply']) && $data['apply'] == 1)
        {
             $this->_db->update('jobs',
                                array('total_applied' => new Zend_Db_Expr('total_applied+1'),
                                      'total_message' => new Zend_Db_Expr('total_message+1'))
                                ,"job_id = {$data['job_id']}");
        }

        $sendMessageInfo = array(
                        'from'              => (int)$data['from'],
                        'to'                => (int)$data['to'],
                        'message_subject'   => $data['message_subject'],
                        'job_id'            => $data['job_id'],
                        'message_body'      => $data['message_body'],
                        'sent_on'           => date('Y-m-d H:i:s', strtotime(Zend_Date::DATETIME)),
                        'status'            => "Unread",
                        'parent_message_id' => isset ($data['parent_id']) ? (int)$data['parent_id'] : 0
        );




        if(isset ($data['save_profile']))
        {
            $this->saveProfile((int)$data['from'], (int)$data['to']);
        }
        else if(isset ($data['save_job']))
        {
            $this->saveJob((int)$data['from'], (int)$data['job_id']);
        }

        if($this->_db->insert('user_messages', $sendMessageInfo))     return true;   else return false;
    }

    public function saveJob($userId, $jobId)
    {
        try{
            $jobInfo = array(
                'user_id' => $userId,
                'job_id' => $jobId
            );
            $this->_db->insert('user_saved_jobs', $jobInfo);
            return true;
         }
         catch (Exception $ex)
         {
             return false;
         }
    }

    public function saveProfile($empId, $profileId)
    {
        try{
            $profileInfo = array(
                'employer_id' => $empId,
                'intern_profile_id' => $profileId
            );

            $this->_db->insert('employer_saved_profiles', $profileInfo);
            return true;
         }
         catch (Exception $ex)
         {
             return false;
         }
    }

    /**
     *
     * @param <type> $userId
     * @return <type>
     */
    public function showInbox($userType = 'intern', $userId = null)
    {
        if(isset ($userId))
        {
            try
            {
                if(!strcasecmp($userType, 'employer'))
                {
                    $select = $this->_db->select()
                    ->from( array('um' => 'user_messages') )
                    ->join( array('up' => 'user_profiles') , "um.from = up.user_id" , array('first_name', 'last_name'))
                    ->where("um.to = {$userId}")
                    ->order("um.status DESC");
                }
                else if(!strcmp($userType, 'intern'))
                {
                    $select = $this->_db->select()
                    ->from( array('um' => 'user_messages') )
                    ->join( array('ep' => 'employer_profiles') , "um.from = ep.user_id" , array('company_name'))                    
                    ->where("um.to = {$userId}")
                    ->order("um.status DESC");
                }

                return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
            }catch(Zend_Db_Exception $e)
            {
                echo $e->getMessage();
            }           
        }
    }


    /**
     *
     * @param <type> $userType
     * @param <type> $messageId
     * @return <type>
     */
    public function getMessageById($userType, $messageId = 0)
    {
        if (!strcasecmp($userType, 'employer')) {

            $select = $this->_db->select()
                            ->from(array('um' => "user_messages"))
                            ->join(array('up' => "user_profiles"), "um.from = up.user_id", array('first_name', 'last_name'))
                            ->join(array('j'  => 'jobs'), "j.job_id = um.job_id", 'job_title')
                            ->where("um.message_id = $messageId");
        }
        else if(!strcmp($userType, 'intern'))
        {
            $select = $this->_db->select()
                            ->from(array('um' => 'user_messages'))
                            ->join(array('ep' => 'employer_profiles'), "um.from = ep.user_id", array('company_name'))
                            ->join(array('j'  => 'jobs'), "j.job_id = um.job_id", 'job_title')
                            ->where("um.message_id = $messageId");
        }

        $this->_db->update('user_messages', array('status' => 'Read'), "message_id = $messageId");

        return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
    }

    /**
     *
     * @param <type> $fromUser
     * @param <type> $data
     * @return string of messages
     */
    public function sendReferenceRequest($fromUser, $data = array()) 
    {
        $msg = "";
        
        if (count($data) > 0)
        {
            $toUser = trim($data['to_user']);
            try{
                    $alreadySentRequest = $this->_db->select()
                            ->from('user_request_references', 'request_reference_id')
                            ->where("from_user = $fromUser")
                            ->where("to_user = '{$toUser}'")
                            ->limit(1);                            

                    if ($this->_db->fetchOne($alreadySentRequest) == false)
                    {
                        $toUserId = $this->_db->select()->from("users", "user_id")
                                        ->where("user_email = '{$toUser}'")
                                        ->limit(1);
                                        
                        if ($this->_db->fetchOne($toUserId) == $fromUser) {                            
                            $msg = "This is your email address.";                            
                        }
                        else
                        { 

                            $referenceInfo = array(
                                'from_user'       => $fromUser,
                                'to_user'         => $toUser,
                                'request_date'    => date('Y-m-d H:i:s', strtotime(Zend_Date::DATETIME)),
                                'request_subject' => $data['request_subject'],
                                'request_message' => $data['request_message']
                            );
                            
                            $this->_db->insert('user_request_references', $referenceInfo);

                            $referenceId = $this->_db->lastInsertId('user_request_references');
                            $encId       = base64_encode($referenceId);
                            $html_massage = "
                                        <div style='font-family: verdana;font-size: 13px;color:#6C9FB9;margin: 0 auto;width: 600px; min-height: 300px; border: solid 13px #3C80A2; overflow:hidden;background-color: #FFF'>
                                        <div style='background-color:#3C80A2;color: #FFF; font-size: 18px; padding:30px 0px 5px 10px;'>
                                            </div>

                                        <div style='padding:30px 0px 15px 15px;'>

                                           
                                            <a href='http://216.86.147.121/user/profile/recommendation/reference-id/{$encId}'> Click Here for recommendation him.</a> <br /><br />

                                           Thanks<br />

                                           http://216.86.147.121

                                        </div>

                                    </div>";

//                            $mail = new Zend_Mail();
//                            $mail->setBodyHtml($html_massage);
//                            $mail->setFrom('membership@urbaninterns.com');
//                            $mail->addTo($data['email']);
//                            $mail->setSubject($data['user_name'] .'has requested a recommendation');
//                            $mail->send();

                             $msg = "Your reference request have been sent, successfully.";
                        }
                    }
                    else
                    {
                        $msg = "Already sent reference to this email.";
                    }
             }
             catch (Zend_Db_Exception $ex)
             {
                 echo $ex->getMessage();
             }            
        }

        return $msg;
    }

    /**
     *
     * @return <type> 
     */
    public function countUserUnreadMessage()
    {
        $auth = Zend_Auth::getInstance();
        $userId = $auth->getIdentity()->user_id;
        
        $select = $this->_db->select()
                ->from( array('um' => 'user_messages'))
                ->where("um.status = 'Unread'")
                ->where("um.to = '{$userId}'");

        return count($this->_db->fetchAll($select));
    }

    /**
     *
     * @param <type> $data
     * @return <type>
     */
    public function saveUserNote($data = array())
    {
        if(count($data) > 0)
        {            
            $note = array(
                'job_id'       => $data['job_id'],
                'user_id'      => $data['user_id'],
                'note_text'    => $data['note_text'],
                'added_on'     => date('Y-m-d H:i:s', strtotime(Zend_Date::DATETIME))
            );
        }

        if($this->_db->insert('user_job_notes', $note))
            return true;
        else
            return false;
    }

    /**
     *
     * @param <type> $data
     * @return <type> 
     */
    public function saveEmployerNote($data = array())
    {
        if(count($data) > 0)
        {
            $note = array(
                'employer_id'    => $data['employer_id'],
                'intern_id'      => $data['intern_id'],
                'note_text'      => $data['note_text'],
                'note_added_on'  => date('Y-m-d H:i:s', strtotime(Zend_Date::DATETIME))
            );
        }

        if($this->_db->insert('employer_notes', $note))
            return true;
        else
            return false;
    }


    public function getSavedInterns($userId, $limit)
    {
        try{
             $select = $this->_db->select()
                    ->from('employer_saved_profiles', 'intern_profile_id')
                    ->where("employer_id = $userId")
                    ->order('saved_on DESC');
            if(isset ($limit))
                        $select->limit($limit);

            $results = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
        
            foreach($results as $row){

                $userIds[] = $row->intern_profile_id;
            }

            $ids = implode(',', $userIds);

            $select = $this->_db->select()
                    ->from( array('up' => 'user_profiles') , array('up.user_id', 'up.profile_title'))
                    ->distinct()->joinLeft( array('en' => 'employer_notes'), "up.user_id = en.intern_id", array('en.note_text'))
                    ->where("up.user_id IN ($ids)")
                    ->order("en.note_added_on ASC")
                    ->limit(count($results));

                return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
            
        }
        catch (Exception $ex)
        {
            return false;
        }       
    }


    public function getSavedJobs($userId, $limit)
    {
        try{
             $select = $this->_db->select()
                    ->from('user_saved_jobs', 'job_id')
                    ->where("user_id = $userId")
                    ->order('saved_on DESC');
            if(isset ($limit))
                        $select->limit($limit);

            $results = $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

            foreach($results as $row){
                $jobIds[] = $row->job_id;
            }
            
            $ids = implode(',', $jobIds);
           
            $select = $this->_db->select()
                    ->from( array('j' => 'jobs') , array('j.job_id', 'j.job_title', 'j.company_name'))
                    ->distinct()->joinLeft( array('ujn' => 'user_job_notes'), "j.job_id = ujn.job_id", array('ujn.note_text'))
                    ->distinct()->joinLeft( array('um' => 'user_messages'), "um.from = $userId", array('um.sent_on'))
                    ->where("j.job_id IN ($ids)")
                    ->order("ujn.added_on ASC")
                    ->limit(count($results));

                return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);

        }
        catch (Exception $ex)
        {
            echo $ex->getMessage();
            return false;
        }
    }

    /**
     *
     * @param <type> $userId
     * @return <type> 
     */
    public function countParentMessageByUserId($userId = null)
    {
        if(isset ($userId))
        {
            try
            {
            $select = $this->_db->select()
                    ->from("user_messages", "COUNT(*) AS total_message")
                    ->where("parent_message_id = 0")
                    ->where("user_messages.to = '{$userId}'");
            return $this->_db->fetchOne($select);
            }
            catch(Zend_Db_Exception $x)
            {
                echo $x->getMessage();
            }
        }
    }
}
?>