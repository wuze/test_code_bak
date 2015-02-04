<?php
/***
 * 模拟登陆　上传客服入职离职信息给
 * 一卡通平台
 */
include_once(dirname(__FILE__)."/action.php");

define('__ROOT__', dirname(dirname(dirname(dirname(__FILE__)).'/').'/').'/');

require_once __ROOT__. "lib/phpexcel/PHPExcel.php"; 
require_once __ROOT__.'include/global.php';
require_once __ROOT__.'include/function.php';
require_once __ROOT__.'include/Loader.class.php';
require_once __ROOT__.'lib/Email.class.php';
require_once __ROOT__.'model/DataLayer.class.php';

$cook['wedsmain'] = 'bsv5_language==cn';
$cook['wedsnow']  = 'bsv5_date_start==2015/02/01|bsv5_date_end==2015/02/28|bsv5_date_format==yyyy/MM/dd|bsv5_dep_select==|bsv5_dep_checked==';

$login_url	   = "$host/yy_login/yy3667_main.asp";
$import_url    = "$host/yy_import/yy3669_main.asp";
$valide_url    = "$host/yy_oledb/yy_validate.asp";
$default_url   = "$host/default.asp";
$yydefault_url = "$host/yy_select/yy_default.asp";

// 页面必须点击提交的数据
$yycase_url 	= "$host/yy_case.asp";

$send_mail 	= 1;  // 是否发送邮件
$GLOBALS['mail'] = array(
						"protocol" 	=> "smtp",
						"host" 		=> "",
						"user" 		=> "",
						"pass" 		=> "",
						"send_name"	=>"",
						"send_subject"=>"一卡通数据导入"
    );

Loader::loadModel('KefuBase', 'hr/kefu');

class ImportData extends KefuBase
{
	var $cookie;
	var $cookie_str;
	var $act;

	var $import_url;
	var $login_url; 
	var $valide_url;
	var $yy_default_url;
	var $yy_case_url;
	var $default_url;
	
	var $yy_data;
	
	var $account;
	var $passwd;
	var $code; 		// 验证码

	var $xls_name;  //xls 文件名称

	function __construct()
	{/*{{{*/
		parent::__construct();
		global $host, $login_url, $import_url, $valide_url, $default_url, $yydefault_url, $yycase_url; 
		$this->cookie['wedsmain'] = 'bsv5_language==cn';
		$this->cookie['wedsnow']  = 'bsv5_date_start==2015/02/01|bsv5_date_end==2015/02/28|bsv5_date_format==yyyy/MM/dd|bsv5_dep_select==|bsv5_dep_checked==';
	
		$this->cookie_str	 = "";
		$this->login_url 	 = $login_url;
		$this->import_url    = $import_url;
		$this->valide_url    = $valide_url;
		$this->default_url   = $default_url;
		$this->yydefault_url = $yydefault_url;
		$this->yy_case_url   = $yycase_url;

		// yy_default 页面的提交数据
		$this->yy_data = array("u_1"=>"cn", "u_2"=>"yyyy/MM/dd", "u_3"=>0);

		$this->account = "ccdev";
		$this->passwd  = "ccdev";
		$this->code    = "";

		$this->act = new Common();
		$this->xls_name = "data.xls";
	}
/*}}}*/

	function send_mail( $subject, $msg )
	{/*{{{*/
	}/*}}}*/

	function Login()
	{/*{{{*/
		$ret_def = $this->act->getDefault($this->default_url);
		$ret_yy  = $this->act->getYYDefault($this->yydefault_url, $this->yy_data);
		if ( $ret_yy )
		{
		 	$dt = explode("|-|", $ret_yy);
			$this->cookie['wedsnow'] = 'bsv5_date_start=='.$dt[0].'|bsv5_date_end=='.$dt[1].'|bsv5_date_format==yyyy/MM/dd|bsv5_dep_select==|bsv5_dep_checked==';
		}

		$ck = $this->act->GetCook();
		if ( $ck ) $this->cookie=array_merge($this->cookie, $ck);

		foreach($this->cookie as $k=>$v){
			$new_cookie[] = $k . "=". urlencode($v); 
		}
	
		$this->cookie_str = implode(";", $new_cookie);
		// 拉取验证码
		do {
			$this->code = $this->act->GenCode($this->valide_url, $this->cookie_str);
		} while (!$this->code);

		$ret = $this->act->Login($this->login_url, $this->account, $this->passwd, $this->code,$this->cookie_str);

		if(preg_match("/Save_ok/i",$ret)) return TRUE;
		return FALSE;
/*}}}*/
	}

	function XlsData( $date )
	{/*{{{*/
		$sql ="SELECT 
				worker_id, worker_name, dept_descr, hire_dt, email,phone,posn_descr
				FROM {$this->tbl_prefix}kefu_employee_hire_info
				where  hire_dt='{$date}'
				";

		return $this->_db->fetchAll($sql, 0, false);
	}/*}}}*/


	/** 
	 *  生成XLS
 	 */
	function NewXls( $users, $filename="data.xls" )
	{/*{{{*/
		$PHPExcel = new PHPExcel();

		// Set document properties
		$PHPExcel->getProperties()->setCreator("fuwei")
					 ->setLastModifiedBy("fuwei")
					 ->setTitle("Office 2007 XLSX Test Document")
					 ->setSubject("Office 2007 XLSX Test Document")
					 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
					 ->setKeywords("office 2007 openxml php")
					 ->setCategory("Test result file");


		// 隐藏头三列
		$PHPExcel->getActiveSheet()->getColumnDimension('A')->setVisible(false);
		$PHPExcel->getActiveSheet()->getColumnDimension('B')->setVisible(false);
		$PHPExcel->getActiveSheet()->getColumnDimension('C')->setVisible(false);
		$current_sheet = $PHPExcel->setActiveSheetIndex(0)->setTitle('sheet');
		$current_sheet->mergeCells('A1:U1');  
		$current_sheet->mergeCells('A2:U2');  

		$cell = array("D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U");


		//$title = array("user_no", "user_lname", "dep__", "user_workday", "user_fname", "user_duty", "user_sex", "user_nation", "user_xueli", "user_birthday", "user_telephone", "user_id", "user_native", "user_adress", "user_email", "user_post", "user_linkman", "user_bz");
		//$desctitle = array("*工号", "*姓名", "部门", "入职日期", "班组", "职务", "性别", "民族", "学历", "出生日期", "联系电话", "身份证号", "籍贯", "家庭住址", "电子邮箱", "邮编", "联系人", "备注");

		$title = array("user_no", "user_lname", "dep__", "user_workday", "user_email","user_telephone","user_duty");
		$desctitle = array("*工号", "*姓名", "部门", "入职日期","邮箱","联系电话","职务");

		// 表头对应名称
		$i = 0;
		foreach($cell as $value){
			$no = $value . 3;
			if(isset($title[$i])){
				$current_sheet->setCellValue($no, $title[$i]);
			}
			$i++;
		}
		// 表头对应名称
		$i = 0;
		foreach($cell as $value){
			$no = $value . 4;
			if(isset($desctitle[$i])){
				$current_sheet->setCellValue($no, $desctitle[$i]);
			}
			$i++;
		}

		// 写输入D8 开始
		$j = 8;
		foreach($users as $uservalue){
			$i = 0;
			foreach($cell as $value){
				$no = $value . $j;
				if(isset($uservalue[$i])){
					$current_sheet->setCellValue($no, $uservalue[$i]);
				}
				$i++;
			}
			$j++;
		}
		
		$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');  
		$objWriter->save($filename);
		echo "Export Xls OK\n";
	}
	/*}}}*/

	// 跳过必点页面
	function HopPage()
	{/*{{{*/
		$data = array("u_id"=>"M00023","u_pur"=>126,"u_name"=>""); 
		$this->act->doPost($this->yy_case_url, $data, $this->cookie_str);
		return true;
	}/*}}}*/

	/***
	 * 写日志
	 */
	function setLog($txt,$title="error")
	{/*{{{*/
		$type = basename(__FILE__)."__".$title;
		$log = array (
					"type"  =>  $type,
					"date"  =>  date("Y-m-d H:i:s"),
					"text"	=>  $txt
				);
		@$this->_db->insert($this->tbl_prefix."kefu_script_error", $log, true);;
	}/*}}}*/

	function Import( $date ,&$count=0)
	{/*{{{*/
		$items = $this->XlsData($date);

		$count = count($items);
		if ( $count > 0 )
		{
			$result = array();
			// 字符填充8800 左边
			foreach($items as $v)
			{
				$len=strlen($v['worker_id']);
				if ( ($len+2)<8 )
				{
					$v['worker_id']="88".str_pad($v['worker_id'], 6,"0",STR_PAD_LEFT);
				}

				$node = array($v['worker_id'], $v['worker_name'], $v['dept_descr'], $v['hire_dt'], $v['email'], $v['phone'],$v['posn_descr']);
				$result[] = $node;
			}

			$this->NewXls($result, $this->xls_name);

			$file_path = dirname(__FILE__)."/".$this->xls_name;
			$data = array (
							"type"   	=> 1,
							"dep_serial"=> 	"200090",
							"photo"  	=>	$this->xls_name,
							"pathes" 	=> '@'.$file_path
						);
			
			$ret = $this->act->doPost($this->import_url, $data, $this->cookie_str);
			if (preg_match("/import_ok/i", $ret)) return TRUE;
		}

		return FALSE;
	}
/*}}}*/

	function Run()
	{/*{{{*/
		$dt = date("Y-m-d");
		$try_times = 0;
		do{
			$f = $this->Login();
			echo "登陆失败!\n";
			$try_times++;

		} while(!$f && $try_times<3); 

		if ( !$f )
		{
			$txt=$dt."同步数据到打卡机,登陆失败". $try_times."次";
			$this->setLog($txt,"error");
		}

		echo "登陆成功\n";
		$this->HopPage();

		$date = date("Y-m-d", strtotime("-1 day"));
		$cnt   = 0;

		if ( $this->Import( $date,$cnt ) )
			echo "导入成功\n";
		else	
			echo "导入失败\n";
		
		$txt=$dt."同步数据到打卡机,登陆".$try_times."次,导入 ".$cnt." 条记录成功!";
		$this->setLog($txt,"success");
	}/*}}}*/
}

$upload = new ImportData();
$upload->run();

