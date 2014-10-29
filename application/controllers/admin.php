﻿<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */

	function __construct()
	{

		parent::__construct();

		$this->load->library(array('user'));

		if (!$this->user->valid_session())
		{
			redirect('login/logout', 'location');
		}

	}


	public function index()
	{
		$this->dashboard();
	}

	public function dashboard()
	{
		$pageid = "dashboard";




		$this->load->model('vote_model');
		$this->load->library('table');

		$tmpl = array (
		                    'table_open'          => '<table class="table table-striped">',

		                    'heading_row_start'   => '<tr>',
		                    'heading_row_end'     => '</tr>',
		                    'heading_cell_start'  => '<th>',
		                    'heading_cell_end'    => '</th>',

		                    'row_start'           => '<tr>',
		                    'row_end'             => '</tr>',
		                    'cell_start'          => '<td>',
		                    'cell_end'            => '</td>',

		                    'row_alt_start'       => '<tr>',
		                    'row_alt_end'         => '</tr>',
		                    'cell_alt_start'      => '<td>',
		                    'cell_alt_end'        => '</td>',

		                    'table_close'         => '</table>'
		              );

		$this->table->set_template($tmpl);

		print_r(date("U"));
		$table = array(array("地點","一號平版","二號平版","三號平版","四號平版"));
		$tmp_aid = "start";
		$tmp_row = array();

		$boothlist = $this->vote_model->get_booth_status();

		array_push($boothlist , "end");

		foreach ($boothlist as $key => $value) {

			if ($value=="end") {

				for ($i=0; $i < 6-count($tmp_row); $i++) { 
					array_push($tmp_row, "-");
				}

				array_push($table, $tmp_row);
				break;
			}

			if ($value->{'a_id'}!=$tmp_aid) {
				if ($tmp_aid!="start") {

					for ($i=0; $i < 6-count($tmp_row); $i++) { 
						array_push($tmp_row, "-");
					}

					array_push($table, $tmp_row);
				}
				$tmp_row=array(base64_decode($value->{'name'}));
			}

			array_push($tmp_row, $this->status_to_button($value->{'status'},$value->{'a_id'},$value->{'lastseen'}));

			$tmp_aid=$value->{'a_id'};
		}

		$data = array(
					'sider_array'=>$this->generateSiderArray($pageid),
					'pageid'=>$pageid,
					'booth_table'=>$this->table->generate($table)
					);
		$this->load->view('admin/'.$pageid , $data);
	}

	private function status_to_button($status,$a_id , $lastseen){
		$html = "";
		if (date("U") - $lastseen > 60) {
			$html = '<span class="label label-danger">離線</span>';
		}else{

			switch ($status) {
				case 'lock':
					$html = '<span class="label label-warning">投票中</span><span class="label label-danger"><a href="/admin/account">KICK</a></span>';
					break;

				case 'free':
					$html = '<span class="label label-success">待命中</span>';
					break;
				
				
				default:
					$html = '<span class="label label-danger">離線</span>';
					break;
			}

		}

		return $html;
	}


	public function account()
	{
		$pageid = "account";






		$this->load->model('vote_model');
		$this->load->library('table');

		$tmpl = array (
		                    'table_open'          => '<table class="table table-striped">',

		                    'heading_row_start'   => '<tr>',
		                    'heading_row_end'     => '</tr>',
		                    'heading_cell_start'  => '<th>',
		                    'heading_cell_end'    => '</th>',

		                    'row_start'           => '<tr>',
		                    'row_end'             => '</tr>',
		                    'cell_start'          => '<td>',
		                    'cell_end'            => '</td>',

		                    'row_alt_start'       => '<tr>',
		                    'row_alt_end'         => '</tr>',
		                    'cell_alt_start'      => '<td>',
		                    'cell_alt_end'        => '</td>',

		                    'table_close'         => '</table>'
		              );

		$this->table->set_template($tmpl);

		$table = array(array("地點名稱","帳號","平版狀態","操作"));
		$tmp_row = array();

		$query_result = $this->vote_model->get_account_list();

		foreach ($query_result as $key => $value) {
			array_push($table , array(
										base64_decode($value->{'name'}) , 
										$value->{'username'} , 
										$value->{'boothcount'} , 
										'<button class="btn btn-danger" onclick="javascript:location.href=\''.base_url('/admin/account_del/'.$value->{'a_id'}).'\';">刪除</span>'
									)
			);
		}



		$data = array(
					'sider_array'=>$this->generateSiderArray($pageid),
					'pageid'=>$pageid,
					'account_table'=>$this->table->generate($table)
					);
		$this->load->view('admin/'.$pageid , $data);
	}


	public function account_del($a_id)
	{

		$this->load->model('vote_model');

		$this->vote_model->del_account($a_id);
		redirect("/admin/account" , "location");
	}

	public function account_new()
	{
		$pageid = "account_new";
		$data = array(
					'sider_array'=>$this->generateSiderArray($pageid),
					'pageid'=>$pageid
					);
		$this->load->view('admin/'.$pageid , $data);
	}

	public function account_new_do()
	{

		$this->load->model('vote_model');

		if($this->input->post('location')==""){
			$content = '<span class="label label-danger">錯誤</span>地點不得為空';
		}elseif($this->input->post('username')==""){
			$content = '<span class="label label-danger">錯誤</span>帳號不得為空';
		}elseif($this->input->post('password')==""){
			$content = '<span class="label label-danger">錯誤</span>密碼不得為空';
		}elseif(preg_match("([1234])",$this->input->post('boothcount'))!==1){
			$content = '<span class="label label-danger">錯誤</span>數量錯誤';
		}else{

			$query_result = $this->vote_model->add_account(
													$this->input->post('location') , 
													$this->input->post('username') , 
													$this->input->post('password') , 
													$this->input->post('boothcount')
													);

		}



		$this->load->library('table');

		$tmpl = array (
		                    'table_open'          => '<table class="table table-striped">',

		                    'heading_row_start'   => '<tr>',
		                    'heading_row_end'     => '</tr>',
		                    'heading_cell_start'  => '<th>',
		                    'heading_cell_end'    => '</th>',

		                    'row_start'           => '<tr>',
		                    'row_end'             => '</tr>',
		                    'cell_start'          => '<td>',
		                    'cell_end'            => '</td>',

		                    'row_alt_start'       => '<tr>',
		                    'row_alt_end'         => '</tr>',
		                    'cell_alt_start'      => '<td>',
		                    'cell_alt_end'        => '</td>',

		                    'table_close'         => '</table>'
		              );

		$this->table->set_template($tmpl);

		$table = array(array("票亭名稱","票亭帳號","票亭密碼","平版帳號","平版密碼"));		
		foreach ($query_result as $key => $value) {
			array_push($table , array(
										$this->input->post('location') , 
										$this->input->post('username'),
										"********",
										$this->input->post('username')."-".$key , 
										$value
									)
			);
		}

		$content = "<p>您成功新增了票亭帳號，並產生".$this->input->post("boothcount")."個平板帳號，這些平板的帳密如下</p>";
		$content .= $this->table->generate($table);

		$pageid = "account_new";
		$data = array(
					'sider_array'=>$this->generateSiderArray($pageid),
					'pageid'=>$pageid,
					'result_title'=>"票亭新增結果",
					'content'=>$content
					);
		$this->load->view('admin/result' , $data);
	}

	public function ballot_list()
	{




		$this->load->model('vote_model');
		$this->load->library('table');


		$query_result = $this->vote_model->get_ballot_list();



		$tmpl = array (
		                    'table_open'          => '<table class="table table-striped">',

		                    'heading_row_start'   => '<tr>',
		                    'heading_row_end'     => '</tr>',
		                    'heading_cell_start'  => '<th>',
		                    'heading_cell_end'    => '</th>',

		                    'row_start'           => '<tr>',
		                    'row_end'             => '</tr>',
		                    'cell_start'          => '<td>',
		                    'cell_end'            => '</td>',

		                    'row_alt_start'       => '<tr>',
		                    'row_alt_end'         => '</tr>',
		                    'cell_alt_start'      => '<td>',
		                    'cell_alt_end'        => '</td>',

		                    'table_close'         => '</table>'
		              );

		$this->table->set_template($tmpl);

		$table = array(array("票別名稱","授權碼前綴","對應票種"));		

		foreach ($query_result as $key => $value) {
			$mapping_html = "";
			foreach ($value->{'t_arr'} as $key2 => $value2) {
				switch ($value2->{'type'}) {
					case 'single':
						$mapping_html .= '<span class="label label-primary">'.$value2->{'title1'}.'</span>';
						break;
					
					case 'multi':
						$mapping_html .= '<span class="label label-success">'.$value2->{'title1'}.'</span>';
						break;
					
				}
			}

			array_push($table , array(
										$value->{'l_id'} , 
										$value->{'prefix'} ,
										$mapping_html 
									)
			);
		}


		$pageid = "ballot_list";
		$data = array(
					'sider_array'=>$this->generateSiderArray($pageid),
					'pageid'=>$pageid,
					'ballot_list_table' => $this->table->generate($table)
					);
		$this->load->view('admin/'.$pageid , $data);
	}

	public function ballot_list_new()
	{
		$pageid = "ballot_list_new";
		$data = array(
					'sider_array'=>$this->generateSiderArray($pageid),
					'pageid'=>$pageid
					);
		$this->load->view('admin/'.$pageid , $data);
	}

	public function ballot_type()
	{
		$pageid = "ballot_type";
		$data = array(
					'sider_array'=>$this->generateSiderArray($pageid),
					'pageid'=>$pageid
					);
		$this->load->view('admin/'.$pageid , $data);
	}

	public function ballot_type_new()
	{
		$pageid = "ballot_type_new";
		$data = array(
					'sider_array'=>$this->generateSiderArray($pageid),
					'pageid'=>$pageid
					);
		$this->load->view('admin/'.$pageid , $data);
	}

	public function candidate()
	{
		$pageid = "candidate";
		$data = array(
					'sider_array'=>$this->generateSiderArray($pageid),
					'pageid'=>$pageid
					);
		$this->load->view('admin/'.$pageid , $data);
	}

	public function candidate_new()
	{
		$pageid = "candidate_new";
		$data = array(
					'sider_array'=>$this->generateSiderArray($pageid),
					'pageid'=>$pageid
					);
		$this->load->view('admin/'.$pageid , $data);
	}

	public function mapping()
	{
		$pageid = "mapping";
		$data = array(
					'sider_array'=>$this->generateSiderArray($pageid),
					'pageid'=>$pageid
					);
		$this->load->view('admin/'.$pageid , $data);
	}

	public function setting()
	{
		$pageid = "setting";
		$data = array(
					'sider_array'=>$this->generateSiderArray($pageid),
					'pageid'=>$pageid
					);
		$this->load->view('admin/'.$pageid , $data);
	}

	private function generateSiderArray($id){
		$id_mapping = array(
						'dashboard' => "Dashboard",
						'account' => "票亭管理",
						'account_new' => ">>票亭新增",
						'ballot_list' => "票別管理",
						'ballot_list_new' => ">>票別新增",
						'ballot_type' => "票種管理",
						'ballot_type_new' => ">>票種新增",
						'candidate' => "候選人管理",
						'candidate_new' => ">>候選人新增",
						'mapping' => "票種關連設定",
						'setting' => "系統設定"
							 );

		//remove _new from $id
		if (preg_match("/(\w+)_new/", $id , $matches)===1) {
			$id = $matches[1];
		}

		//remove all *_new element except itself
		foreach ($id_mapping as $key => $value) {
			if (preg_match("/(\w+)_new/", $key)===1 && $key!=$id."_new") {
				unset($id_mapping[$key]);
			}
		}

		return $id_mapping;

	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */