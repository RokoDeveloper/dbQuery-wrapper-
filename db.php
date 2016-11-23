<?php
class DBQuery
{
	public static $connection = null;
	var $queryResult;
	
	function __construct($sql,$params = array())
	{
		if(empty(self::$connection))
		{
			self::openConnection();
		}
		$sql = self::parseQuery($sql);
		//print $sql;
		$this->queryResult = self::$connection->prepare($sql);
		if(is_array($params)) {
			foreach($params as $key=>$value)
			{
				$this->queryResult->bindValue($key,$value);
			}
		}
		$this->queryResult->execute();
	}
	
	static function parseQuery($sql)
	{
		return str_replace("#__",_DB_TABLE_PREFIX,$sql);
	}
	
	static function openConnection()
	{
		try
		{
			self::$connection = new PDO("mysql"._HOST.";dbname=" . _DB, _DBUSER, _DBPASSWORD);
			self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			self::$connection->query("set names utf8");
		}
		catch(PDOException $e)
		{
			global $app;
			$app->errors[] = $e->getMessage();
			return false;
		}

	}
	static function closeConnection()
	{
		$db = null;
	}
	
	function fetch($fetchStyle = PDO::FETCH_ASSOC)
	{
		if(!empty($this->queryResult))
		{
			return  $this->queryResult->fetch($fetchStyle);
		}
		else
		{
			return false;
		}
	}
	
}


function dbQuery($query,$params = array(),$count_all = false,&$all_count = 0) {

	$query = DBQuery::parseQuery($query);
	$result = array();

	if($count_all){

		$sql_query = explode("SELECT",$query,2);
		$query = "SELECT SQL_CALC_FOUND_ROWS" . $sql_query[1];
	}

	$db = new DBQuery($query,$params);

	while($r = $db->fetch())
	{
		array_push($result,$r);
	}

	if($count_all){
		$all_count = dbGetOne("SELECT FOUND_ROWS();");
	}

	return $result;
}

function dbNonQuery($query,$params = array(),$is_insert = false) {

    $result = '';
	
	$query = DBQuery::parseQuery($query);
   
	$db = new DBQuery($query,$params);

	if($is_insert)
	{
		$result = DBQuery::$connection->lastInsertId();
	}

	return $result;
}

function dbGetOne($query,$params = array()) {

    $query = DBQuery::parseQuery($query);
	$db = new DBQuery($query,$params);
	$row = $db->fetch(PDO::FETCH_BOTH);
	return isset($row[0]) ? $row[0] : "";
}

function dbGetRow($query,$params = array()) {

	$query = DBQuery::parseQuery($query);
	$db = new DBQuery($query,$params);
	return $db->fetch();
}

function dbQueryToArray($query,$params = array())
{
	$arr = array();
	
	$result = dbQuery($query,$params);
	foreach($result as $r)
	{
		$arr[] = implode(",",$r);
	}
	
	return $arr;
}
