<?php
function mysqli_connect($host,$user,$pass,$db){
	mysql_connect($host,$user,$pass);
	mysql_select_db($db);
	return $this;
}

function mysqli_query($db_link, $sql){
	return mysql_query($sql);
}

function mysqli_fetch_array($query){
	return mysql_fetch_array($query);
}
function mysqli_num_rows($query){
	return mysql_num_rows($query);
}
function RemoveFalseButNotZero($value) {
	  return ($value || is_numeric($value));
}

class mysqli{
	private $sql;
	private $query;
	public function __construct($host,$user,$pass,$db){
		mysqli_connect($host,$user,$pass,$db);
	}

	public function prepare($sql){
		$this->sql = $sql;
		return $this;
	}
	
	public function bind_param($type, $value1, $value2 = null, $value3 = null, $value4 = null, $value5 = null){
		$stmts = array_filter(array($value1, $value2, $value3, $value4, $value5), "RemoveFalseButNotZero");
		foreach($stmts as $stmt){
			if(!is_numeric($stmt)){
				$stmt = "'".$stmt."'";
			}
			$this->sql = preg_replace('/[?]/', $stmt, $this->sql, 1);
		}
	}

	public function execute(){
		$this->query = mysqli_query(null, $this->sql);
	}

	public function get_result(){
		return new mysqli_result($this->query);
	}


	public function close(){

	}
}
class mysqli_result{
	private $query;
	public $num_rows;
	public function __construct($query){
		$this->query = $query;
		$this->num_rows = $query ? mysqli_num_rows($query) : 0;
	}

	public function fetch_array($type){
		return $this->query ? mysqli_fetch_array($this->query) : false;
	}
}
?>