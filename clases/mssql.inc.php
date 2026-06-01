<?php
#######################################################################################
# Class Name : PHP-mssql Class
# Author : Henry Chen
# Last Mofidy Date : 2003-05-03
# License : GPL
# Contact Info
# 	E-Mail : henryi@wowphp.net
# 	ICQ : 55490755
# 	AIM : wowphp
# 	YIM : henryi_chen@yahoo.com
#######################################################################################
# Function List
# 	1.connection	 -->	function connection()
# 	2.disconnect	 -->	function disconnect()
# 	3.insert		 -->	function insert($tb_name,$cols,$val,$astriction)
# 	4.update		 -->	function update($tb_name,$string,$astriction)
# 	5.delete		 -->	function del($tb_name,$astriction)
# 	6.query			 -->	function query($string)
# 	7.num_rows		 -->	function nums($string="",$qid="")
# 	8.object		 -->	function objects($string="",$qid="")
# 	9.insert_id		 -->	function insert_id($qid="")
# 	10.pagecut		 -->	function page_cut($string,$nowpage = "0")
# 	11.show_page_cut -->	function show_page_cut($string="",$num="",$url="")
#######################################################################################
# About this changing
#	1. Add error handling
#	2. Modifing the new code format
#	3. More useful page cut function
#	4. Add the function about getting the ID generated from the previous INSERT operation 
#######################################################################################
# Change Log
#--------------------
#  2002-07-24
#--------------------
# 1. Correct page_cut error. 
# 2. Improve the function nums and function objects these two functions. 
# 3. The new pgae_cut and show_page_cut functions can let you use these functions 
#    with your template 
#
########################################################################################

class mod_db{
	var $setting_file = "setting.inc.php";
	var $sql_link;
	var $db_link;
	var $query_id;
	var $perpage;
	var $total;
	var $pagecut_query;
	var $debug = "0"; // If your code can't work well just change it to 1, you can see the sql string.

	function connection(){
		// Check if SQL is connect or not
		if(!$this->sql_link){
			// Define SQL Variables
			if(!$this->db_host || !$this->db_name || !$this->db_user || !$this->db_pass){
				include_once("$this->setting_file");
				global $sql_host,$sql_name,$sql_user,$sql_pass,$per_page;
					$this->db_host = $sql_host;
					$this->db_name = $sql_name;
					$this->db_user = $sql_user;
					$this->db_pass = $sql_pass;
				// Define other variables
					$this->perpage=$per_page;
			}
			// SQL Connection
			$this->sql_link = mssql_pconnect($this->db_host,$this->db_user,$this->db_pass);
				if(!$this->sql_link){
					echo "Connect to mssql Server Error";
				}

				if($this->debug == 1){
					echo "Connect to mssql Server Success<br>ServerHost=$this->db_host<br>";//for testing
				}

			// Select Database
			$this->db_link = mssql_select_db($this->db_name);
			if(!$this->db_link){
				echo "Connect to DB Error , DBName = $this->db_name";
			}

			if($this->debug == 1){
				echo "Connect to mssql DB:$this->db_name Success<br>";
			}
			return;
		}else{
			exit;
		}
	}

	function disconnect(){
		mssql_close($this->sql_link);
	}

	function insert($tb_name,$cols,$val,$astriction){
		if(empty($this->sql_link)){
			$this->connection();
		}

		// Check cols is empty or not
		if(!$cols){
			$cols = "";
		}elseif($cols != ""){
			$cols = "(".$cols.")";
		}

		// Check astriction is empty or not
		if(!$astriction){
			$ast = "";
		}elseif($astriction != ""){
			$ast = " WHERE ".$astriction;
		}

		$insert = mssql_query("INSERT INTO $tb_name $cols VALUES($val) $ast",$this->sql_link);
			if(!$insert){
				// echo "<script>alert('Insert Data Error');</script>"; // English Error Message
				echo "<script>alert('資料新增失敗');</script>"; // Chinese Error Message
				
			}
			if($this->debug == 1){
				echo "Insert String = Insert into $tb_name $cols Values($val) $ast<br>";
			}
			return;
	}

	function update($tb_name,$string,$astriction){
		if(empty($this->sql_link)){
			$this->connection();
		}

		// Check astriction is empty or not
		if(!$astriction){
			$ast = "";
		}elseif($astriction != ""){
			$ast = " WHERE ".$astriction;
		}
		$update = mssql_query("UPDATE $tb_name SET $string $ast",$this->sql_link);

		if(!$update){
			// echo "'<script>alert('Update Data Error');</script>"; // English Error Message
			echo "<script>alert('資料更新失敗');</script>"; // Chinese Error Message
			
		}
		if($this->debug == 1){
			echo "Update String = Update $tb_name Set $string $ast<br>";
		}
	}

	function del($tb_name,$astriction){
		if(empty($this->sql_link)){
			$this->connection();
		}

		// Check astriction is empty or not
		if(!$astriction){
			$ast = "";
		}elseif($astriction != ""){
			$ast = " WHERE ".$astriction;
		}
		$del = mssql_query("DELETE FROM $tb_name $ast",$this->sql_link);

		if(!$del){
			// echo "<script>alert('Delete Data Error');</script>"; // English Error Message
			echo "<script>alert('資料刪除失敗');</script>"; // Chinese Error Message
			
		}
		if($this->debug == 1){
			echo "Delete String = Delete From $tb_name $ast<br>";
		}
	}

	function query($string){
		if(empty($this->sql_link)){
			$this->connection();
		}
		$this->query_id = mssql_query($string,$this->sql_link);

		if(!$this->query_id){
			// echo "<script>alert('Unable to Perform the query:$string');</script>"; // English Error Message
			echo "<script>alert('查詢失敗');</script>"; // Chinese Error Message
			
		}
		if($this->debug == 1){
			echo "Query String = $string <br>";
		}
		return $this->query_id;

	}

	function nums($string="",$qid=""){
		if($string != ""){
			$this->query($string);
			$this->total = mssql_num_rows($this->query_id);
		}elseif($qid != ""){
			$this->total = mssql_num_rows($qid);
		}elseif(empty($sting) && empty($qid)){
			$this->total = mssql_num_rows($this->query_id);
		}
		if($this->debug == 1){
			echo "Number = ".$this->total."<br>";
		}
		return $this->total;
	}

	function objects($string="",$qid=""){
		if($string != ""){
			$this->query($string);
			$objects = mssql_fetch_object($this->query_id);
				if($this->debug == 1){
					echo "qid = ".$qid."<br>";
					echo "obj = ".$objects."<br><br>";
				}
		}elseif($qid != ""){
			$objects = mssql_fetch_object($qid);
				if($this->debug == 1){
					echo "qid = ".$qid."<br>";
					echo "obj = ".$objects."<br><br>";
				}
		}elseif(empty($string) && empty($qid)){
			$objects = mssql_fetch_object($this->query_id);
				if($this->debug == 1){
					echo "qid = ".$qid."<br>";
					echo "obj = ".$objects."<br><br>";
				}
		}
		return $objects;
	}
	
	function insert_id($qid=""){
		if($qid){
			$insert_id = mssql_insert_id($qid);
		}elseif(!$qid){
			$insert_id = mssql_insert_id();
		}
		
		if($this->debug == 1){
			echo "Insert ID = ".$insert_id."<br>";
		}
		return $insert_id;
	}

	function page_cut($string,$nowpage = "0"){
		if(empty($this->sql_link)){
			$this->connection();
		}

			$query_str=$string;

		//Limit
		if(!$nowpage){
			$start = "0";
		}else{
			$start = ($nowpage-1)*$this->perpage;
		}
			$this->pagecut_query=$query_str." LIMIT $start,".$this->perpage."";
				if($this->debug == 1){
					echo"pagecut string=".$query_str." LIMIT $start,".$this->perpage."";
				}
		Return $this->pagecut_query;
	}

	function show_page_cut($string="",$num="",$url=""){
		global $nowpage;

		if($string){
			$this->nums($string,'');
			$pages = ceil($this->total / $this->perpage);
		}elseif($num){
			$this->total = $num;
			$pages = ceil($this->total / $this->perpage);
		}
		//echo "pages = ".$pages."<br>";

		###First Page###
		if(!$nowpage || $nowpage == "1"){
			$pagecut = "<font color = 'C0C0C0'>第一頁</font>";
		}else{
			$pagecut .=  "<a href='$PHP_SELF?".$url."&nowpage=1'>";
			$pagecut .=  "<font color = '#000066'style = 'font-size:12pt'>";
			$pagecut .=  "<<<第一頁</font>";
			$pagecut .=  "</a>";
		}
		$pagecut .=  "&nbsp;&nbsp;";

		###Previous Page###
		if(($nowpage-1) > 0){
			$prevpage = $nowpage-1;
			$pagecut .=  "<a href='$PHP_SELF?".$url."&nowpage=$prevpage'>";
			$pagecut .=  "<font color = '#336600' style = 'font-size:12pt'>";
			$pagecut .=  "<<前一頁</font>";
			$pagecut .=  "</a>";
		}else{
			$pagecut .=  "<font color = 'C0C0C0'>前一頁</font>";
		}
			$pagecut .=  "&nbsp;&nbsp;";

		###At which Page###
		if(!$nowpage){
			$i = "1";
		}else{
			$i = $nowpage;
		}
		$pagecut .=  "目前在第&nbsp;";
		$pagecut .=  $i;
		$pagecut .=  "&nbsp;頁<font color = '#663300'>/共&nbsp;".$pages."&nbsp;頁</font>";
		$pagecut .=  "&nbsp;&nbsp;";

		###Next Page###
		if(!$nowpage){$nowpage = '1';}
		if(($pages-$nowpage) > 0){
			$nextpage = $nowpage+1;
			$pagecut .=  "<a href='$PHP_SELF?".$url."&nowpage=$nextpage'>";
			$pagecut .=  "<font color = '336600'style = 'font-size:12pt'>";
			$pagecut .=  "下一頁>></font>";
			$pagecut .=  "</a>";
		}else{
			$pagecut .=  "<font color = 'C0C0C0'>下一頁</font>";
		}
			$pagecut .=  "&nbsp;&nbsp;";

		###Last Page###
		if($nowpage == $pages){
			$pagecut .=  "<font color = 'C0C0C0'>最後一頁</font>";
		}else{
			$pagecut .=  "<a href='$PHP_SELF?".$url."&nowpage=$pages'>";
			$pagecut .=  "<font color = '000066'style = 'font-size:12pt'>";
			$pagecut .=  "最後一頁>>></font>";
			$pagecut .=  "</a>";
		}
		Return $pagecut;
		}

}
?>