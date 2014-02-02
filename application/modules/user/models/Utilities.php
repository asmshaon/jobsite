<?php

class User_Model_Utilities extends Zend_Db_Table_Abstract {

    /**
     *
     */
    public function getLocations()
    {
        try{
             $select = $this->_db->select()
                            ->from('locations')
                            ->order('sort_order ASC');

            return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
        }
        catch (Zend_Db_Exception $ex)
        {
            echo $ex->getMessage();
        }
    }

    /**
     *
     */
    public function getAvailabilites()
    {
        try{
             $select = $this->_db->select()
                            ->from('availabilities')
                            ->order('sort_order ASC');

            return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
        }
        catch (Zend_Db_Exception $ex)
        {
            ;
        }
    }

    /**
     *
     */
    public function getSchedules()
    {
        try{
             $select = $this->_db->select()
                            ->from('schedules')
                            ->order('sort_order ASC');

            return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
        }
        catch (Zend_Db_Exception $ex)
        {
            ;
        }
    }

    /**
     *
     */
    public function getHours()
    {
        try{
             $select = $this->_db->select()
                            ->from('hours')
                            ->order('sort_order ASC');

            return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
        }
        catch (Zend_Db_Exception $ex)
        {
            ;
        }
    }

    /**
     *
     */
    public function getIndustries()
    {
        try{
             $select = $this->_db->select()
                            ->from('industries')
                            ->order('sort_order ASC');

            return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
        }
        catch (Zend_Db_Exception $ex)
        {
            ;
        }
    }

    /**
     *
     */
    public function getSkills()
    {
        try{
             $select = $this->_db->select()
                            ->from('skills')
                            ->order('sort_order ASC');

            return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
        }
        catch (Zend_Db_Exception $ex)
        {
            ;
        }
    }

    /**
     * 
     */
    public function getEducations()
    {
        try{
             $select = $this->_db->select()
                            ->from('educations')
                            ->order('sort_order ASC');

            return $this->_db->fetchAll($select, NULL, Zend_DB::FETCH_OBJ);
        }
        catch (Zend_Db_Exception $ex)
        {
            ;
        }
    }

    /**
     *
     * @param <type> $id
     * @return <type>
     */
    public function getAvailabilitiTitleById($id = 0)
    {
        $select = $this->_db->select()
                ->from("availabilities", "title")
                ->where("availability_id = $id")
                ->limit(1);
        return $this->_db->fetchOne($select);
    }

    /**
     *
     * @param <type> $id
     * @return <type>
     */
    public function getSkillTitleById($id = 0)
    {
        $select = $this->_db->select()
                ->from("skills", "skill_name")
                ->where("skill_id = $id")
                ->limit(1);
        return $this->_db->fetchOne($select);
    }

    /**
     *
     * @param <type> $id
     * @return <type>
     */
    public function getIndustryTitleById($id = 0)
    {
        $select = $this->_db->select()
                ->from("industries", "industry_name")
                ->where("industry_id = $id")
                ->limit(1);
        return $this->_db->fetchOne($select);
    }


    /**
     *
     * @param <type> $id
     * @return <type>
     */
    public function getHourTitleById($id = 0)
    {
        $select = $this->_db->select()
                ->from("hours", "title")
                ->where("hour_id = $id")
                ->limit(1);
        return $this->_db->fetchOne($select);
    }

    /**
     *IF NEW LOCATION FRIST INSERT THEN RETURN ID FOR PROFILE CREATE AND JOB POST
     * @param <type> $locationName
     * @return <type>
     */
    public function getLocationId($locationName = '')
    {
        $location = strtolower($locationName);

        $select = $this->_db->select()
                ->from('locations', 'location_id')
                ->where("LOWER(location_name) = '{$location}'")
                ->limit(1);
        if($this->_db->fetchOne($select))
                return $this->_db->fetchOne($select);
        else
        {
            $this->_db->insert('locations', array('location_name' => $locationName));
            return $this->_db->lastInsertId();
        }
    }

    public function getLocationName($id = 0)
    {
        try{
             $select = $this->_db->select()
                            ->from('locations', 'location_name')
                            ->limit(1);

            return $this->_db->fetchOne($select);
        }
        catch (Zend_Db_Exception $ex)
        {
            echo $ex->getMessage();
        }
    }
   
}
?>