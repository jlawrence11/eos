<?php

//Equation Operating System
//created by Jon Lawrence
// (c) 2005 AAB Software

if(!defined(DEBUG))
	define('DEBUG', false);

require_once "stack.class.php";

/* Define arrays for use in the parser */
$eqSEP = array('open' => array('(', '['), 'close' => array(')', ']'));
$eqSGL = array('!');
$eqST = array('^');
$eqST1 = array('/', '*', '%');
$eqST2 = array('+', '-');
$eqFNC = array('sin', 'cos', 'tan', 'csc', 'sec', 'cot');


//Create the basic EOS for solving equations and the likes ;]
class eqEOS
{
	/* PRIVATE variable scope */
	var $postFix;
	var $inFix;

	/* __construct() */
	function eqEOS($inFix = "")
	{
		//set up our variables
		if($inFix)
			$this->inFix = $inFix;
		else
			$this->inFix = "";
		$this->postFix = array();
	}

	/* convert a standard equation into RPN notation */
	function in2post($inFix = "")
	{
		/* Put our operator arrays in scope for this function */
		global $eqSEP, $eqSGL, $eqST1, $eqST2, $eqFNC, $eqST;

		/* if a new equation was not passed, use the one that was passed in the constructor */
		$infix = ($inFix != "") ? $inFix : $this->inFix;
		$pf = array();
		$ops = new phpStack();
		$vars = new phpStack();
		$lChar = "";

		/* remove all white-space */
		preg_replace("/\s/", "", $infix);

		/* Create postfix array index */
		$pfIndex = 0;

		//what was the last character? (useful for decerning between a sign for negation and subtraction)
		$lChar = '';

		//loop through all the characters and start doing stuff ^^
		for($i=0;$i<strlen($infix);$i++)
		{
			/* pull out 1 character from the string */
			$chr = substr($infix, $i, 1);
			
			/* if the character is a number character */
			if(eregi('[0-9.]', $chr))
			{
				/* if the previous character was not a '-' or a number */
				if((!eregi('[0-9.]', $lChar) && ($lChar != "")) && ($pf[$pfIndex]!="-"))
					$pfIndex++;	/* increase the index so as not to overlap anything */
				/* Add the number character to the array */
				$pf[$pfIndex] .= $chr;
			}
			/* If the character opens a set e.g. '(' or '[' */
			else if(in_array($chr, $eqSEP['open']))
			{
				/* if the last character was a number, place an assumed '*' on the stack */
				if(eregi('[0-9.]', $lChar))
					$ops->push('*');

				$ops->push($chr);
			}
			/* if the character closes a set e.g. ')' or ']' */
			else if(in_array($chr, $eqSEP['close']))
			{
				/* find what set it was i.e. matches ')' with '(' or ']' with '[' */
				$key = array_search($chr, $eqSEP['close']);
				/* while the operator on the stack isn't the matching pair...pop it off */
				while($ops->peek() != $eqSEP['open'][$key] && $ops->peek() != false)
				{
					$nchr = $ops->pop();
					if($nchr)
						$pf[++$pfIndex] = $nchr;
					else
						return "Error while searching for '". $eqSEP['open'][$key] ."'";
				}
				$ops->pop();
			}
			/* If a special operator that has precedence over everything else */
			else if(in_array($chr, $eqST))
			{
				//if(in_array($ops->peek(), $eqST))
				//	$pf[++$pfIndex] = $ops->pop();

				$ops->push($chr);
				$pfIndex++;
			}
			/* Any other operator other than '+' and '-' */
			else if(in_array($chr, $eqST1))
			{
				while(in_array($ops->peek(), $eqST1) || in_array($ops->peek(), $eqST))
					$pf[++$pfIndex] = $ops->pop();

				$ops->push($chr);
				$pfIndex++;
			}
			/* if a '+' or '-' */
			else if(in_array($chr, $eqST2))
			{
				/* if it is a '-' and the character before it was an operator or nothingness (e.g. it negates a number) */
				if(((in_array($lChar, $eqST1) || in_array($lChar, $eqST2) || in_array($lChar, $eqST)) || $lChar=="") && $chr=="-")
				{
					/* increase the index because there is no reason that it shouldn't.. */
					$pfIndex++;
					$pf[$pfIndex] .= $chr; 
				}
				/* Otherwise it will function like a normal operator */
				else
				{
					while(in_array($ops->peek(), $eqST1) || in_array($ops->peek(), $eqST2) || in_array($ops->peek(), $eqST))
						$pf[++$pfIndex] = $ops->pop();

					$ops->push($chr);
					$pfIndex++;
				}
			}
			/* make sure we record this character to be refered to by the next one */
			$lChar = $chr;
		}
		/* if there is anything on the stack after we are done...add it to the back of the RPN array */
		while(($tmp = $ops->pop()) != false)
			$pf[++$pfIndex] = $tmp;

		/* re-index the array at 0 */
		$i = 0;
		foreach($pf as $tmp)
			$pfTemp[$i++] = $tmp;
		$pf = $pfTemp;
		
		/* set the private variable for later use if needed */
		$this->postFix = $pf;

		/* return the RPN array in case developer wants to use it fro some insane reason (bug testing ;] */
		return $pf;
	} //end function in2post

	/* This function will solve a RPN array */
	function solvePF($pfArray = "")
	{
		/* put operator arrays in function scope */
		global $eqSEP, $eqSGL, $eqST1, $eqST2, $eqFNC, $eqST;

		/* if no RPN array is passed - use the one stored in the private var */
		$pf = (!is_array($pfArray)) ? $this->postFix : $pfArray;
		
		/* create our temporary function variables */
		$temp = array();
		$tot = 0;
		$hold = 0;

		/* Loop through each number/operator */
		for($i=0;$i<count($pf); $i++)
		{
			/* If the string isn't an operator, add it to the temp var as a holding place */
			if(!in_array($pf[$i], $eqST1) && !in_array($pf[$i], $eqST2) && !in_array($pf[$i], $eqST))
			{
				$temp[$hold++] = $pf[$i];
			}
			/* ...Otherwise preform the operator on the last two numbers */
			else
			{
				$opr = $pf[$i];
				if($opr=="+")
					$temp[$hold-2] = $temp[$hold-2] + $temp[$hold-1];
				else if($opr=="-")
					$temp[$hold-2] = $temp[$hold-2] - $temp[$hold-1];
				else if($opr=="*")
					$temp[$hold-2] = $temp[$hold-2] * $temp[$hold-1];
				else if($opr=="/" && $temp[$hold-1] != 0)
					$temp[$hold-2] = $temp[$hold-2] / $temp[$hold-1];
				else if($opr=="^")
					$temp[$hold-2] = pow($temp[$hold-2], $temp[$hold-1]);
				else if($opr=="%" && $temp[$hold-2] > -1)
					$temp[$hold-2] = bcmod($temp[$hold-2], $temp[$hold-1]);

				/* Decrease the hold var to one above where the last number is */
				$hold = $hold-1;
			}
		}
		/* return the last number in the array */
		return $temp[$hold-1];

	} //end function solvePF


	/* This will take a standard equation and solve it for the developer */
	/* INPUTS:
		$infix -> standard equation
			unknowns must be passed with a pre-fixed ampersand (e.g. '&x' for 'x')
		$vArray -> either a numeric value or a hash of variables with their equivelent values
			e.g. "6" for all unknowns to become 6
					or
				array('x' => 3, 'y' => 2, 'fun' => 7) for x=3; y=2; fun=7
	*/
	function solveIF($infix, $vArray = "")
	{
		/* put operator arrays in function scope */
		global $eqSEP, $eqSGL, $eqST1, $eqST2, $eqFNC, $eqST;

		$if = ($infix != "") ? $infix : $this->inFix;
		if($infix=="") die;

		$ops = new phpStack();
		$vars = new phpStack();

		//remove all white-space
		preg_replace("/\s/", "", $infix);
		if(DEBUG)
			$hand=fopen("eq.txt","a");

		//Find all the variables that were passed and replaces them
		while((preg_match("/(.){0,1}\&([a-zA-Z]+)(.){0,1}/", $infix, $match)) != 0)
		{

			if(DEBUG)
				fwrite($hand, "{$match[1]} || {$match[3]}\n");
			/* Ensure that the variable has an operator or something of that sort in front and back - if it doesn't, add an implied '*' */
			if((!in_array($match[1], $eqST1) && !in_array($match[1], $eqST2) && !in_array($match[1], $eqST) && !in_array($match[1], $eqSEP['open']) && !in_array($match[1], $eqSEP['close']) && ($match[1] != "")) || is_numeric($match[1]))
				$front = "*";
			else
				$front = "";

			if(!in_array($match[3], $eqST1) && !in_array($match[3], $eqST2) && !in_array($match[3], $eqST) && !in_array($match[3], $eqSEP['open']) && !in_array($match[3], $eqSEP['close']) && ($match[3] != ""))
				$back = "*";
			else
				$back = "";
			
			//Make sure that the variable does have a replacement
			if(!isset($vArray[$match[2]]) && (!is_array($vArray != "") && !is_numeric($vArray)))
				return "Mal-formed equation : variable '{$match[2]}' not found";
			else if(!isset($vArray[$match[2]]) && (!is_array($vArray != "") && is_numeric($vArray)))
				$infix = str_replace($match[0], $match[1] . $front. $vArray. $back . $match[3], $infix);
			else if(isset($vArray[$match[2]]))
				$infix = str_replace($match[0], $match[1] . $front. $vArray[$match[2]]. $back . $match[3], $infix);
		}

		if(DEBUG)
			fwrite($hand, "$infix\n");

		/* Finds all the 'functions' within the equation and calculates them */
		/* NOTE - when using function, only 1 set of paranthesis will be found, instead use brackets for sets within functions!! */
		while((preg_match("/(". implode("|", $eqFNC) . ")\(([^\)\(]*(\([^\)]*\)[^\(\)]*)*[^\)\(]*)\)/", $infix, $match)) != 0)
		{
			$func = $this->solveIF($match[2]);
			$func = ($func);
			switch($match[1])
			{
				case "cos":
					$ans = cos($func);
					break;
				case "sin":
					$ans = sin($func);
					break;
				case "tan":
					$ans = tan($func);
					break;
				case "sec":
					if(($tmp = cos($func)) != 0)
						$ans = 1/$tmp;
					break;
				case "csc":
					if(($tmp = sin($func)) != 0)
						$ans = 1/$tmp;
					break;
				case "cot":
					if(($tmp = tan($func)) != 0)
						$ans = 1/$tmp;
					break;
				default:
					break;
			}
			$infix = str_replace($match[0], $ans, $infix);
		}
		if(DEBUG)
			fclose($hand);
		return $this->solvePF($this->in2post($infix));


	} //end function solveIF
} //end class 'eqEOS'


/* fun class that requires the GD libraries to give visual output to the user */
/* extends the eqEOS class so that it doesn't need to create it as a private var 
    - and it extends the functionality of that class */
class eqGraph extends eqEOS
{
	var $width, $height;	//width and heigt of the graph to be created
	var $image;				//image reference

	/* __construct */
	function eqGraph($width=640, $height=480)
	{
		/* default width and height equal to that of a poor monitor */
		$this->width = $width;
		$this->height = $height;
	} //end function eqGraph

	/* fill the private var '$image' with a GD image of the graph */
	/* INPUTS:
		$eq -> the equation; same as inputed for eqEOS::solveIF()
		$xLow -> lower x-bound of the graph
		$xHight -> upper x-bound of the graph
		$xStep -> precision of the graph (No need to go less than 0.01 )
		$xyGrid -> (true/false) show a grid on the map at every x,y point
		$yGuess -> (true/false) have the function calculate the upper and lower y-bounds
		$yLow -> lower y-bound (if assigned and $yGuess is true, this will be the starting lower bound
			i.e. if the function would calculate $yLow to be '5' and this is set at '0'; '0' will be the lower bound
		$yHigh -> upper y-bound.  same type of functionality as the $yLower if set and $yGuess is true
	*/
	function graph($eq, $xLow, $xHigh, $xStep, $xyGrid = false, $yGuess = true, $yLow=false, $yHigh=false)
	{	
		//create our image and allocate the two colors
		$img = ImageCreate($this->width, $this->height);
		$white = ImageColorAllocate($img, 255, 255, 255);
		$black = ImageColorAllocate($img, 0, 0, 0);
		$grey = ImageColorAllocate($img, 220, 220, 220);
		$xStep = abs($xStep);
		if($xLow > $xHigh)
			list($xLow, $xHigh) = array($xHigh, $xLow);	//swap function
		
		$xScale = $this->width/($xHigh-$xLow);
		$counter = 0;
		if(DEBUG)
		{
			$hand=fopen("crappers.txt","w");
			fwrite($hand, "$eq\n");
		}
		for($i=$xLow;$i<=$xHigh;$i+=$xStep)
		{
			$tester = sprintf("%10.3f",$i);
			if($tester == "-0.000") $i = 0;
			$y = $this->solveIF($eq, $i);
			//eval('$y='. str_replace('&x', $i, $eq).";"); /* used to debug my eqEOS class results */
			if(DEBUG)
			{
				$tmp1 = sprintf("y(%5.3f) = %10.3f\n", $i, $y);
				fwrite($hand, $tmp1);
			}

			/* If developer asked us to find the upper and lower bounds for y... */
			if($yGuess==true)
			{
				$yLow = ($yLow===false || ($y<$yLow)) ? $y : $yLow;
				$yHigh = ($yHigh===false || $y>$yHigh) ? $y : $yHigh;
			}
			$xVars[$counter] = $y;
			$counter++;			
		}
		if(DEBUG)
			fclose($hand);
		/* add 0.01 to each side so that if y is from 1 to 5, the lines at 1 and 5 are seen */
		$yLow-=0.01;$yHigh+=0.01;

		//Now that we have all the variables stored...find the yScale
		$yScale = $this->height/(($yHigh)-($yLow));

		/* if developer wanted a grid on the graph, add it now */
		if($xyGrid==true)
		{
			for($i=ceil($yLow);$i<=floor($yHigh);$i++)
			{
				$i0 = abs($yHigh-$i);
				ImageLine($img, 0, $i0*$yScale, $this->width, $i0*$yScale, $grey);
			}
			for($i=ceil($xLow);$i<=floor($xHigh);$i++)
			{
				$i0 = abs($xLow-$i);
				ImageLine($img, $i0*$xScale, 0, $i0*$xScale, $this->height, $grey);
			}
		}
		
		//Now that we have the scales, let's see if we can draw an x/y-axis
		if($xLow <= 0 && $xHigh >= 0)
		{
			//the y-axis is within our range - draw it.
			$x0 = abs($xLow)*$xScale;
			ImageLine($img, $x0, 0, $x0, $this->height, $black);
			for($i=ceil($yLow);$i<=floor($yHigh);$i++)
			{
				$i0 = abs($yHigh-$i);
				ImageLine($img, $x0-3, $i0*$yScale, $x0+3, $i0*$yScale, $black);
			}
		}
		if($yLow <= 0 && $yHigh >= 0)
		{
			//the x-axis is within our range - draw it.
			$y0 = abs($yHigh)*$yScale;
			ImageLine($img, 0, $y0, $this->width, $y0, $black);
			for($i=ceil($xLow);$i<=floor($xHigh);$i++)
			{
				$i0 = abs($xLow-$i);
				ImageLine($img, $i0*$xScale, $y0-3, $i0*$xScale, $y0+3, $black);
			}
		}
		$counter=2;

		//now graph it all ;]
		for($i=$xLow+$xStep;$i<=$xHigh;$i+=$xStep)
		{
			$x1 = (abs($xLow - ($i - $xStep)))*$xScale;
			$y1 = (($xVars[$counter-1]<$yLow) || ($xVars[$counter-1] > $yHigh)) ? -1 : (abs($yHigh - $xVars[$counter-1]))*$yScale;
			$x2 = (abs($xLow - $i))*$xScale;
			$y2 = (($xVars[$counter]<$yLow) || ($xVars[$counter] > $yHigh)) ? -1 : (abs($yHigh - $xVars[$counter]))*$yScale;
			
			/* if any of the y values were found to be off of the y-bounds, don't graph those connecting lines */
			if($y1!=-1 && $y2!=-1)
				ImageLine($img, $x1, $y1, $x2, $y2, $black);
			$counter++;
		}
		$this->image = $img;
	} //end function 'graph'

	function outJPG()
	{
		header("Content-type: image/jpeg");
		ImageJpeg($this->image);
	}

	function outPNG()
	{
		header("Content-type: image/png");
		ImagePng($this->image);
	}
	
	/* will give the developer the GD resource for the graph */
	/* can be used to store the graph to the FS or other mediu, */
	function outGD()
	{
		return $this->image;
	}

} //end class 'eqGraph'
?>