<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Authcode_lib {
    /**
    * MIT License (MIT)
    * Copyright (c) 2014 MouseMs <mousems.kuo@gmail.com>
    * http://opensource.org/licenses/MIT
    * https://github.com/mousems/NTUVoteV2
    **/
    
	private $db;

	function __construct()
	{

		$this->CI =& get_instance();
		$this->db = $this->CI->load->database('default', true);

	}

    function vaild_authcode_format($authcode)
    {
        preg_match("/^(.*)\-([A-Z0-9]{9})-([A-Z0-9]{9})-([A-Z0-9]{5})$/", $authcode, $matches);


        if (isset($matches[4])) {




            $this->db->from('ballot_list')->where('prefix' , $matches[1]);
            $query = $this->db->get();
            if ($query->num_rows()==0) {
                return FALSE;
            }



            $part1 = $matches[2];
            $part2 = $matches[3];
            $part3 = strtoupper(substr(md5($part1.md5($part2)), 1, 5)) ;
            if ($part3==$matches[4]) {
                return TRUE;
            }else{
                return FALSE;

            }
        }else{
            return FALSE;
        }
    }
    function get_authcode_status($authcode)
    {
        if ($this->vaild_authcode_format($authcode)===FALSE) {
            return FALSE;
        }


        $this->db->select("hash ,prefix , step , b_id")->from('authcode')->where('hash' , sha1($authcode));
        $query = $this->db->get();

        if($query->num_rows()==0){
            return FALSE;
        }else{
            return $query->row(1);
        }
        
    }
    function plus_authcode($authcode)
    {
    	$authcode_status = $this->get_authcode_status($authcode);
        if ($authcode_status===FALSE) {
            return FALSE;
        }

        $data = array(
        	"step"=>$authcode_status->{'step'}+1
        );
        $this->db->where('hash',sha1($authcode));
        $this->db->update('authcode',$data);
		return True;        
    }
}
