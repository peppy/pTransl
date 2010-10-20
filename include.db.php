<?
global $conn;

if ($conn == null)
{
	$conn = new mysqlp("localhost","ptransl","","ptransl");
	$conn->query('SET CHARACTER SET utf8');
}

function sqlstr($string)
{
  return addslashes(stripslashes($string));
}

class mysqlp extends mysqli 
{ 
	function queryOne($query)
	{
		$result = $this->query($query);

		if ($result === FALSE)
			return 0;

		if ($result === TRUE)
			return true;

		if ($row = $result->fetch_row())
			$returnValue = array_pop($row);
		$result->close();
		return $returnValue;
	}
	
	function queryRow($query, $ordered = false) 
	{
		$result = $this->query($query);

		if ($result === FALSE)
			return 0;

		if ($ordered)
		{
			if ($row = $result->fetch_row())
				$returnValue = $row;
		}
		else
		{
			if ($row = $result->fetch_assoc())
				$returnValue = $row;
		}
		$result->close();
		return $returnValue;
	}
	
	function queryAllRekey($query, $ordered = false)
	{
		$result = $this->query($query);

		$arr = array();

		if ($ordered)
		{
			while ($row = $result->fetch_row()) { 
				$arr[current($row)] = $row;
	 		} 			
		}
		else
		{
			while ($row = $result->fetch_assoc()) { 
				$arr[current($row)] = $row;
	 		} 
		}

		$result->close();

		return $arr;
	}
	
	function queryAll($query, $ordered = false)
	{
		$result = $this->query($query);

		$arr = array();

		if ($ordered)
		{
			while ($row = $result->fetch_row()) { 
				array_push($arr,$row);
	 		} 			
		}
		else
		{
			while ($row = $result->fetch_assoc()) { 
				array_push($arr,$row);
	 		} 
		}


		$result->close();

		return $arr;
	}
	
	function exec($query)
	{
		return $this->real_query($query);
	}
}

?>