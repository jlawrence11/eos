<?php

//Basic Stack Class
//written by Jon Lawrence

class phpStack
{
	var $index;
	var $locArray;

	function phpStack()
	{
		//define the private vars
		$this->locArray = array();
		$this->index = -1;
	}

	function peek()
	{
		if($this->index > -1)
			return $this->locArray[$this->index];
		else
			return false;
	}

	function poke($data)
	{
		$this->locArray[++$this->index] = $data;
	}

	function push($data)
	{
		//allias for 'poke'
		$this->poke($data);
	}

	function pop()
	{
		if($this->index > -1)
		{
			$this->index--;
			return $this->locArray[$this->index+1];
		}
		else
			return false;
	}

	function clear()
	{
		$this->index = -1;
		$this->locArray = array();
	}

	function getStack()
	{
		if($this->index > -1)
		{
			$tmpArray = array();
			for($i=0;$i<$this->index;$i++)
				$tmpArray[$i] = $this->locArray[$i];
			return $tmpArray;
		}
		else
			return false;
	}
}

?>