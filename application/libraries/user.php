<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User {
	private $db;

	function __construct()
	{

		$this->CI =& get_instance();




		$this->db = $this->CI->load->database('default', true);

		//$this->valid_session();
	}


	// logintype = {all,admin,station,vote}
	public function valid_session($logintype = "all"){
		if ($this->CI->session->userdata('logintype')==FALSE) {
			return FALSE;
		}else{

			if ($this->valid_account(
					$this->CI->session->userdata('logintype'),
					$this->CI->session->userdata('username'),
					$this->CI->session->userdata('passen'),
					TRUE
				) //login test
			) {
				if ($this->CI->session->userdata('logintype') == $logintype || $logintype == "all") {
					return TRUE;
				}else{
					return FALSE;
				}
				
			}else{
				return FALSE;
			}

		}
	}



	// return bool
	public function valid_account($login_type , $username , $password , $hashed = FALSE){


		switch ($login_type) {
			case 'admin':
			case 'station':

				$this->db->from('account')->where('username',$username);
				$query = $this->db->get();
				$row = $query->row();
				if ($query->num_rows()==0) {
					return FALSE;
				}

				if ($hashed) {
					
					if ($row->{'password'} == md5($password.$row->{'salt'})) {
						$this->CI->session->set_userdata('logintype' , $row->{'rule'});
						$this->CI->session->set_userdata('username' , $username);
						$this->CI->session->set_userdata('passen' , $password);
						return TRUE;
					}else{
						log_message('debug' , "password not match , hashed=T");
						return FALSE;
					}


				}else{
					if ($row->{'password'} == md5(md5($password).$row->{'salt'})) {
						$this->CI->session->set_userdata('logintype' , $row->{'rule'});
						$this->CI->session->set_userdata('username' , $username);
						$this->CI->session->set_userdata('passen' , md5($password));
						return TRUE;
					}else{
						log_message('debug' , $row->{'password'}."password not match , hashed=F");
						return FALSE;
					}

				}


				break;
			case 'vote':
				
				$this->db->from('booth')->where('username',$username);
				$query = $this->db->get();
				$row = $query->row();
				if ($query->num_rows()==0) {
					return FALSE;
				}


		        $this->db->from('account')->where('a_id',$row->{'a_id'});
		        $query2 = $this->db->get();
		        if ($query2->num_rows()>0) {
		        	$booth_name = $query2->row(1)->{'name'};
		        }
		        

				if ($hashed) {
					
					if ($row->{'password'} == md5($password.$username)) {
						$this->CI->session->set_userdata('booth_name' , $booth_name);
						$this->CI->session->set_userdata('logintype' , "vote");
						$this->CI->session->set_userdata('username' , $username);
						$this->CI->session->set_userdata('passen' , $password);
						return TRUE;
					}else{
						log_message('debug' , "password not match , hashed=T");
						return FALSE;
					}


				}else{
					if ($row->{'password'} == md5(md5($password).$username)) {
						$this->CI->session->set_userdata('booth_name' , "booth_name");
						$this->CI->session->set_userdata('logintype' , "vote");
						$this->CI->session->set_userdata('username' , $username);
						$this->CI->session->set_userdata('passen' , md5($password));
						return TRUE;
					}else{
						log_message('debug' , $row->{'password'}."password not match , hashed=F");
						return FALSE;
					}

				}
				break;
			
			default:
				return FALSE;
				break;
		}
	}

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */