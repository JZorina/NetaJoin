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
}