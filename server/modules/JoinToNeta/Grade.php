<?php
/**
 * Created by PhpStorm.
 * User: yulia
 * Date: 3/21/2018
 * Time: 02:09 PM
 */

class Grade
{
    function GetClasses()
    {
        global $db;
        $Classes = $db->smartQuery(array(
            'sql' => "Select * FROM class Order By classname",
            'par' => array(),
            'ret' => 'all'
        ));
        return $Classes;
    }

    function AddClass($data)
    {
        global $db;
        foreach($data as $Grade)
        {
            $ArabicClassName = isset($Grade->ArabicClassName)?$Grade->ArabicClassName :'';
            if(isset($Grade->classid))
            {
                $id = $Grade->classid;
                $result = $db->smartQuery(array(
                    'sql' => "update class set classname= :classname, nameinarabic =:nameinarabic where classid=:id",
                    'par' => array('classname'=>$Grade->classname,'ArabicClassName'=>ArabicClassName),
                    'ret' => 'result'
                ));
            }else
            {
                $result = $db->smartQuery(array(
                    'sql' => "insert into class (ArabicClassName,classname)values(:name,:ArabicClassName)",
                    'par' => array('name'=>$Grade->classname,'ArabicClassName'=>ArabicClassName),
                    'ret' => 'result'
                ));
            }
        }
        return $result;
    }
}