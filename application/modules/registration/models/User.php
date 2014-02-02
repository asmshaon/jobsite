<?php

class Registration_Model_User extends Zend_Db_Table_Abstract {

    //Database Table name users
    protected $_name = 'users';

    public function isExistingUser($userEmail = '')
    {
        $select = $this->_db->select()
                ->from('users')
                ->where("user_email = '{$userEmail}'")
                ->limit(1);
        return $this->_db->fetchOne($select);
    }

    /**
     *
     * @param <type> $data 
     */
    public function saveUser($data = array()) {
        try {
            $users = array();
           
            // set the row data
            // Insert into users TABLE
            $users['user_email']        = $data['user_email'];
            $users['user_password']     = md5($data['password1']);
            $users['user_type']         = $data['user_type'];
            $users['membership_type']   = $data['membership_type'];
            $users['joined_on']         = date('Y-m-d H:i:s', strtotime(Zend_Date::DATETIME));
            $users['verification_code'] = md5(microtime() . rand(10000, 99999));
            $users['subscription_id']   = isset ($data['subscription_id']) ? $data['subscription_id'] : '000000';
            $users['customer_id']       = isset ($data['customer_id']) ? $data['customer_id'] : 0;
            $users['status']            = isset ($data['status']) ? $data['status'] : 'inactive';
            

            try {
                $this->_db->insert('users', $users);
                $html_massage = "
                            <div style='font-family: verdana;font-size: 13px;color:#6C9FB9;margin: 0 auto;width: 600px; min-height: 300px; border: solid 13px #3C80A2; overflow:hidden;background-color: #FFF'>
                            <div style='background-color:#3C80A2;color: #FFF; font-size: 18px; padding:30px 0px 5px 10px;'>
                                </div>

                            <div style='padding:30px 0px 15px 15px;'>

                                Thank you for registration in the http://beta.urbaninterns.com<br /><br /><br />

                                Please Click the link below to active your account.<br />

                                http://beta.urbaninterns.com/registration/account/active-new-user/activation-key/{$users['verification_code']} <br /><br />

                               Thanks<br />

                               http://beta.urbaninterns.com

                            </div>

                        </div>";

                $mail = new Zend_Mail();
                $mail->setBodyHtml($html_massage);
                $mail->setFrom('membership@urbaninterns.com');
                $mail->addTo($data['user_email']);
                $mail->setSubject('Welcome to Urban Interns');
                $mail->send();
            } catch (Exception $ex) {
                return false;
            }
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param <type> $userId
     * @param <type> $active_code
     * @return <type>
     */
    public function activeNewUser($active_code) {
        $select = $this->_db->select()
                        ->from('users', 'user_id')
                        ->where("verification_code='$active_code'")
                        ->limit(1);

        $userId = $this->_db->fetchOne($select);
        if ($userId) {
            $data = array(
                'status'            => 'active',
                'verification_code' => md5(microtime() . rand(10000, 99999))
            );

            $this->_db->update('users', $data, "user_id ='{$userId}'");

            return true;
        }
        else
            return false;
    }

    /**
     *
     * @param <type> $email
     * @param <type> $password
     * @return <type> 
     */
    public function isActive($email, $password) {
        $select = $this->_db->select()
                        ->from('users')
                        ->where("user_email = '$email'")
                        ->where("user_password = '$password'")
                        ->where("status = 'active'");

        if ($this->_db->fetchOne($select))
            return true;
        else
            return false;
    }

    /**
     *
     * @param <type> $data
     * @return <type> 
     */
    public function sendResetPasswordLink($data = array()) {

        $requestCode = md5(microtime() . rand(10000, 99999));
        $requestInfo = array(
            'user_email'   => $data['email'],
            'request_code' => $requestCode,
            'requested_on' => date('Y-m-d H:i:s', strtotime(Zend_Date::DATETIME))
        );
        
        if (count($this->getUserInfo($data['email'])) > 0){

            $expire = array('status' => 'Used');

            $this->_db->update('password_requests', $expire, "user_email = '{$data['email']}'");

            $this->_db->insert('password_requests', $requestInfo);
                $html_massage = "
                            <div style='font-family: verdana;font-size: 13px;color:#6C9FB9;margin: 0 auto;width: 600px; min-height: 300px; border: solid 13px #3C80A2; overflow:hidden;background-color: #FFF'>
                            <div style='background-color:#3C80A2;color: #FFF; font-size: 18px; padding:30px 0px 5px 10px;'>
                                </div>

                            <div style='padding:30px 0px 15px 15px;'>
                                
                                Please Click the link below to reset password.<br />

                                http://beta.urbaninterns.com/registration/account/reset-password/reset-key/{$requestCode} <br /><br />

                               Thanks<br />

                               http://beta.urbaninterns.com

                            </div>

                        </div>";

            $mail = new Zend_Mail();
            $mail->setBodyHtml($html_massage);
            $mail->setFrom('membership@urbaninterns.com');
            $mail->addTo($data['email']);
            $mail->setSubject('Your UrbanInterns.com password');
            $mail->send();

            return ture;
        }
        else {
            return false;
        }
    }

    /**
     *
     * @param <type> $user_email
     * @return <bool> true/false
     */
    public function getUserInfo($user_email = '') {
        if (isset($user_email)) {
            $select = $this->_db->select()
                            ->from('users')
                            ->where("user_email = '$user_email'")
                            ->limit(1);
            return $this->_db->fetchAll($select);
        }
    }

    /**
     *
     * @param <type> $userId
     * @return <type> 
     */
    public function getUserInfoById($userId = null) {
        if (isset($userId)) {
            $select = $this->_db->select()
                            ->from('users')
                            ->where("user_id = '$userId'")
                            ->limit(1);
            return $this->_db->fetchAll($select);
        }
    }


        /**
     *
     * @param <type> $userId
     * @return <type>
     */
    public function getUserSettings($userId = null) {
        try{
            if (isset($userId)) {
            $select = $this->_db->select()
                            ->from('user_settings')
                            ->where("user_id = '$userId'")
                            ->limit(1);
            return $this->_db->fetchAll($select);
        }
        }catch(Exception $ex)
        {
            echo $ex->getMessage();
        }        
    }

     public function saveSettings($userId , $data = array())
    {
        try{

            $user = array();

            if( !empty ($data['user_email']) )
            {
                $user['user_email'] = $data['user_email'];
            }
            if ( !empty ($data['password1']) && !empty ($data['password1']) && (strlen($data['password1']) == strlen($data['password2']) ) )
            {
                $user['user_password'] = md5($data['password1']);
            }

            if(count($user) > 0)
            {
                $this->_db->update('users', $user, "user_id = {$userId}");
            }

            $settings = array(
                'user_id'                 => $userId,
                'weekly_newsletter'       => isset ($data['weekly_newsletter']) ? 1 : 0 ,
                'email_notification'      => isset ($data['email_notification']) ? 1 : 0 ,
                'sms_notification'        => isset ($data['sms_notification']) ? 1 : 0,
                'sms_notification_number' => !empty ($data['sms_notification_number']) ? "{$data['sms_notification_number']}###{$data['sms_notification_via']}" : ''
            );

            $this->_db->delete('user_settings', "user_id = {$userId}");

            $this->_db->insert('user_settings', $settings);
            return true;
        }
        catch(Exception $ex)
        {
            return false;
        }
    }
   
    
    /**
     *
     * @param <type> $resetKey
     * @return <bool> true/false
     */
    public function getPasswordRequestInfo($resetKey = '')
    {
        $select = $this->_db->select()
                        ->from('password_requests')
                        ->where("request_code = '$resetKey'")
                        ->where("status = 'active'")
                        ->limit(1);

        return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
    }

    /**
     *
     * @param <type> $data
     * @param <type> $emailId
     * @return <string> message
     */
    public function resetPassword($data = array(), $emailId = '')
    {
        if (count($data) && !empty($emailId))
        {
            $password = array('user_password' => md5($data['password1']));

            $this->_db->update('users', $password, "user_email = '$emailId'");

            $requestInfo = array('status' => 'Used');

            $this->_db->update('password_requests', $requestInfo, "user_email = '$emailId'");

            return ture;
        }
        else
            return false;
    }

    /**
     *
     */
    public function getMembershipInfo($userType, $typeId = 0)
    {
        $select = $this->_db->select()
                ->from('membership_types')
                ->where("type_id={$typeId}")
                ->limit(1);
        $result = $this->_db->fetchAll($select);
        $info = array(
            'type_name'   => $result[0]['type_name'],
            'type_amount' => $result[0]['type_amount']
        );       
        
        $select2 = $this->_db->select()
                ->from('membership_types', array('m' => 'max(type_amount)'));
        
        if(!strcasecmp($userType, 'intern'))
                $select2->where ("type_id IN(1,2)");
        else if(!strcasecmp($userType, 'employer'))
                $select2->where ("type_id IN (3, 4, 5, 6, 7)");

        $result2 = $this->_db->fetchAll($select2);
        
        $ownAmount = intval($result[0]['type_amount']);
        $maxAmount = intval($result2[0]['m']);
       
        if($ownAmount < $maxAmount)
                $info['action'] = 'up';
        else
                $info['action'] = 'down';

        return $info;
    }

    public function changeMembershipType($userId, $newMembershipType, $subscriptionId = null)
    {
        if(isset ($userId) && isset ($newMembershipType))
        {
            $newMembershipInfo = array(
                'membership_type' => $newMembershipType,
                'subscription_id' => isset ($subscriptionId) ? $subscriptionId : 'canceled'
            );

            $this->_db->update('users', $newMembershipInfo, "user_id = $userId");
            return true;
        }
        return false;
    }

    /**
     *
     * @param <type> $userType
     * @param <type> $currentMembershipType
     * @param <type> $action
     * @return <type> 
     */
    public function getAvailableMembershipTypes($userType, $currentMembershipType, $action)
    {
        if($action == 'down')
        {
            try{
                $select2 = $this->_db->select()
                ->from('membership_types')
                ->where("type_id <= $currentMembershipType");
                 if( !strcasecmp($userType, 'intern') )
                 {
                     $select2->where("type_id IN(1, 2)")->order("type_id DESC");
                 }
                 else if ( !strcasecmp($userType, 'employer') )
                 {
                     $select2->where("type_id IN( 3, 4 , 5, 6, 7)")->order("type_id DESC");
                 }
                 return $this->_db->fetchAll($select2, NULL, Zend_DB::FETCH_OBJ);
            }
             catch (Zend_Db_Exception $ex)
             {
                 echo $ex->getMessage();
             }
        }
        else if($action == 'up')
        {
             try{
                $select2 = $this->_db->select()
                ->from('membership_types')
                ->where("type_id >= $currentMembershipType");
                 if( !strcasecmp($userType, 'intern') )
                 {
                     $select2->where("type_id IN(1, 2)")->order("type_id DESC");
                 }
                 else if ( !strcasecmp($userType, 'employer') )
                 {
                     $select2->where("type_id IN(3, 4, 5, 6, 7)")->order("type_id DESC");
                 }
                 return $this->_db->fetchAll($select2, NULL, Zend_DB::FETCH_OBJ);
            }
             catch (Zend_Db_Exception $ex)
             {
                 echo $ex->getMessage();
             }
        }
    }

    /**
     *
     * @param <type> $userId
     * @return <type> 
     */
    public function getUserType($userId)
    {
        $select = $this->select()->from("users", array("user_type"))->where("user_id = $userId")->limit(1);
        
        return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
    }

    /**
     *
     * @param <type> $userType
     * @param <type> $userId
     * @return profile ID
     */
    public function hasProfileById($userType , $userId)
    {
        if(isset ($userId) && $userType)
        {
            $select = $this->_db->select();
            if(!strcasecmp($userType, 'intern'))
                    $select->from ('user_profiles', 'profile_id');
            else if(!strcasecmp($userType, 'employer'))
                    $select->from ('employer_profiles', 'employer_profile_id');
            else
                return 0;
            
            $select->where("user_id = $userId");

            return $this->_db->fetchOne($select);
        }
        return 0;
    }

    /**
     *
     * @param <type> $userId
     * @return <type> 
     */
    public function getCurrentMembershipType($userId)
    {
        if(isset ($userId))
        {
            $select = $this->_db->select()
                    ->from('users', 'membership_type')
                    ->where("user_id = {$userId}");

            return $this->_db->fetchOne($select);
        }
    }

    /**
     *
     * @param <type> $userId
     * @return <type> 
     */
    public function getSubscriptionId($userId)
    {
        if(isset ($userId))
        {
            $select = $this->_db->select()
                    ->from('users', 'subscription_id')
                    ->where("user_id = {$userId}");

            return $this->_db->fetchOne($select);
        }
    }
}
?>