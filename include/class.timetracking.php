<?php
/*********************************************************************
    class.timetracking.php

    Everything about staff.

    Mack Hendricks <mack.hendricks@dopensource.com>
    Copyright (c) 2013 
    http://dopensource.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

if (is_file(INCLUDE_DIR.'class.ticket.php'))
{
	include_once(INCLUDE_DIR.'class.ticket.php');
}
else //Class is being used outside of OSTicket, so I need to define some database functions
{
        include_once('db_inc.php');
}
class Timetracking {
    
    var $ht;
    var $id;

    function Timetracking($var) {
        $this->id =0;
        return ($this->load($var));
    }

    //Used to load data from the database and provide state to the object.	
    function load($var='') {

        if(!$var && !($var=$this->getId()))
            return false;

/*
        $sql='SELECT staff.*, staff.created as added, grp.* '
            .' FROM '.STAFF_TABLE.' staff '
            .' LEFT JOIN '.GROUP_TABLE.' grp ON(grp.group_id=staff.group_id) ';

        $sql.=sprintf(' WHERE %s=%s',is_numeric($var)?'staff_id':'username',db_input($var));

        if(!($res=db_query($sql)) || !db_num_rows($res))
            return NULL;

        
        $this->ht=db_fetch_array($res);
        $this->id  = $this->ht['staff_id'];
        $this->teams = $this->ht['teams'] = array();
        $this->group = $this->dept = null;
        $this->departments = $this->stats = array();

        //WE have to patch info here to support upgrading from old versions.
        if(($time=strtotime($this->ht['passwdreset']?$this->ht['passwdreset']:$this->ht['added'])))
            $this->ht['passwd_change'] = time()-$time; //XXX: check timezone issues.

        if($this->ht['timezone_id'])
            $this->ht['tz_offset'] = Timezone::getOffsetById($this->ht['timezone_id']);
        elseif($this->ht['timezone_offset'])
            $this->ht['tz_offset'] = $this->ht['timezone_offset'];
*/
        return ($this->id);
    }

    function reload() {
        return $this->load();
    }

    function getHastable() {
        return $this->ht;
    }

    function getInfo() {
        return $this->getHastable();
    }


    function getId() {

	return $this->$id;

    }

    /**** Static functions ********/

    //This function will update the timetracking table with the userid that the user used to register
    
   function updateTransaction($paypal_txn,$user_email_addr) {

	$sql = "update timetracking set user_email_addr='$user_email_addr' where paypal_txn='$paypal_txn'"; 

	if(!($res=db_query($sql)) || !db_num_rows($res))
            return NULL;
	else
	    return TRUE;

   }



    //This function will calculate the available support time that a customer has available
    function getAvailableTime($email,$account_number='') {

	if (!empty($account_number))
{	
	$sql = "select sum(minutes)/60 as hours from timetracking where minutes not in ('79','-20000') and account_number='$account_number'";
}
	else
{
	$sql = "select sum(minutes)/60 as hours from timetracking where (email_addr ='$email' or user_email_addr='$email') and minutes not in ('79','-20000')";
}
	if(!($res=db_query($sql)) || !db_num_rows($res))
            return NULL;

	$data=db_fetch_array($res);
	return ($data[hours]);
    }

   function setTimeUsed($ticketID,$ticketThreadID,$email,$minutes) {

	//Turn minutes used into a negative number to be stored in that database
	$minutes=$minutes*-1;

	$sql = "insert into timetracking (ticket_id,note_id,email_addr,minutes) values ('$ticketID','$ticketThreadID','$email', '$minutes')"; 

	if(!($res=db_query($sql)) || !db_num_rows($res))
            return NULL;
	else
	    return TRUE;

   }
   
   function getThreadTimeUsed($ticketID,$noteID) {

	 $sql = "select (sum(minutes)*-1)/60 as hours from timetracking where ticket_id ='$ticketID' and note_id = '$noteID'";

        if(!($res=db_query($sql)) || !db_num_rows($res))
            return NULL;

        $data=db_fetch_array($res);
        return ($data[hours]);

   }
    
    /* Return a list of monthly subscriptions 
   
	 */

    function getMonthlySubscriptions($account_number) {

         $sql = "select * from timetracking where minutes in ('79','-20000') and now() <= adddate(datetime, INTERVAL 1 MONTH) and account_number='$account_number'; ";

        if(!($res=db_query($sql)) || !db_num_rows($res))
            return NULL;

        
	while ($row = db_fetch_array($res) )
		$subscriptions[]=array('lastpaid'=>$row[datetime]);
//	echo var_dump($subscriptions);
        return ($subscriptions);

   }


}
?>
