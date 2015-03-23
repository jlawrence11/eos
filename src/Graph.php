<?php

namespace jlawrence\eos;

// fun class that requires the GD libraries to give visual output to the user 
/* extends the Parser class so that it doesn't need to create it as a private var 
    - and it extends the functionality of that class */
/**
 * Equation Graph
 *
 * Fun class that requires the GD libraries to give visual output of an
 * equation to the user.  Extends the Parser class.
 *
 * @author Jon Lawrence <jlawrence11@gmail.com>
 * @copyright Copyright �2005-2013 Jon Lawrence
 * @license http://opensource.org/licenses/LGPL-2.1 LGPL 2.1 License
 * @package Math
 * @subpackage EOS
 * @version 2.0
 */
class Graph extends Parser {
	private $width;
	private $height;
	//GD Image reference
	private $image;

	/**
	 * Constructor
	 *
	 * Sets up the Graph class with an image width and height defaults to
	 * 640x480
	 *
	 * @param Integer $width Image width
	 * @param Integer $height Image height
	 */
	public function __construct($width=640, $height=480) {
		// default width and height equal to that of a poor monitor (in early 2000s)
		$this->width = $width;
		$this->height = $height;
		//initialize main class variables
		parent::__construct();
	} //end function eqGraph


	/**
	 * Create GD Graph Image
	 *
	 * Creates a GD image based on the equation given with the parameters that are set
	 *
	 * @param String $eq Equation to use.  Needs variable in equation to create graph, all variables are interpreted as 'x'
	 * @param Integer $xLow Lower x-bound for graph
	 * @param Integer $xHigh Upper x-bound for graph
	 * @param Float $xStep Stepping points while solving, the lower, the better precision. Slow if lower than .01
	 * @param Boolean $xyGrid Draw gridlines?
	 * @param Boolean $yGuess Guess the upper/lower yBounds?
	 * @param Integer $yLow Lower y-bound
	 * @param Integer $yHigh Upper y-bound
	 * @return Null
	 */
	public function graph($eq, $xLow, $xHigh, $xStep, $xyGrid = false, $yGuess = true, $yLow=false, $yHigh=false) {
		//create our image and allocate the two colors
		$img = ImageCreate($this->width, $this->height);
		//$white = ImageColorAllocate($img, 255, 255, 255);
		$black = ImageColorAllocate($img, 0, 0, 0);
		$grey = ImageColorAllocate($img, 220, 220, 220);
		$xStep = abs($xStep);
        $hand = null;
        $xVars = array();
		//DEVELOPER, UNCOMMENT NEXT LINE IF WANTING TO PREVENT SLOW GRAPHS
		//$xStep = ($xStep < .01) ? $xStep : 0.01;
		if($xLow > $xHigh)
			list($xLow, $xHigh) = array($xHigh, $xLow);	//swap function
		
		$xScale = $this->width/($xHigh-$xLow);
		$counter = 0;
		if(Parser::$debug) {
			$hand=fopen("eqgraph.txt","w");
			fwrite($hand, "$eq\n");
		}
		for($i=$xLow;$i<=$xHigh;$i+=$xStep) {
			$tester = sprintf("%10.3f",$i);
			if($tester == "-0.000") $i = 0;
			$y = $this->solveIF($eq, $i);
			//eval('$y='. str_replace('&x', $i, $eq).";"); /* used to debug my Parser class results */
			if(Parser::$debug) {
				$tmp1 = sprintf("y(%5.3f) = %10.3f\n", $i, $y);
				fwrite($hand, $tmp1);
			}

			// If developer asked us to find the upper and lower bounds for y... 
			if($yGuess==true) {
				$yLow = ($yLow===false || ($y<$yLow)) ? $y : $yLow;
				$yHigh = ($yHigh===false || $y>$yHigh) ? $y : $yHigh;
			}
			$xVars[$counter] = $y;
			$counter++;			
		}
		if(Parser::$debug)
			fclose($hand);
		// add 0.01 to each side so that if y is from 1 to 5, the lines at 1 and 5 are seen 
		$yLow-=0.01;$yHigh+=0.01;

		//Now that we have all the variables stored...find the yScale
		$yScale = $this->height/(($yHigh)-($yLow));

		// if developer wanted a grid on the graph, add it now 
		if($xyGrid==true) {
			for($i=ceil($yLow);$i<=floor($yHigh);$i++) {
				$i0 = abs($yHigh-$i);
				ImageLine($img, 0, $i0*$yScale, $this->width, $i0*$yScale, $grey);
			}
			for($i=ceil($xLow);$i<=floor($xHigh);$i++) {
				$i0 = abs($xLow-$i);
				ImageLine($img, $i0*$xScale, 0, $i0*$xScale, $this->height, $grey);
			}
		}
		
		//Now that we have the scales, let's see if we can draw an x/y-axis
		if($xLow <= 0 && $xHigh >= 0) {
			//the y-axis is within our range - draw it.
			$x0 = abs($xLow)*$xScale;
			ImageLine($img, $x0, 0, $x0, $this->height, $black);
			for($i=ceil($yLow);$i<=floor($yHigh);$i++) {
				$i0 = abs($yHigh-$i);
				ImageLine($img, $x0-3, $i0*$yScale, $x0+3, $i0*$yScale, $black);
			}
		}
		if($yLow <= 0 && $yHigh >= 0) {
			//the x-axis is within our range - draw it.
			$y0 = abs($yHigh)*$yScale;
			ImageLine($img, 0, $y0, $this->width, $y0, $black);
			for($i=ceil($xLow);$i<=floor($xHigh);$i++) {
				$i0 = abs($xLow-$i);
				ImageLine($img, $i0*$xScale, $y0-3, $i0*$xScale, $y0+3, $black);
			}
		}
		$counter=2;

		//now graph it all ;]
		for($i=$xLow+$xStep;$i<=$xHigh;$i+=$xStep) {
			$x1 = (abs($xLow - ($i - $xStep)))*$xScale;
			$y1 = (($xVars[$counter-1]<$yLow) || ($xVars[$counter-1] > $yHigh)) ? -1 : (abs($yHigh - $xVars[$counter-1]))*$yScale;
			$x2 = (abs($xLow - $i))*$xScale;
			$y2 = (($xVars[$counter]<$yLow) || ($xVars[$counter] > $yHigh)) ? -1 : (abs($yHigh - $xVars[$counter]))*$yScale;
			
			// if any of the y values were found to be off of the y-bounds, don't graph those connecting lines 
			if($y1!=-1 && $y2!=-1)
				ImageLine($img, $x1, $y1, $x2, $y2, $black);
			$counter++;
		}
		$this->image = $img;
	} //end function 'graph'

	/**
	 * Sends JPG to browser
	 *
	 * Sends a JPG image with proper header to output
	 */
	public function outJPG() {
		header("Content-type: image/jpeg");
		ImageJpeg($this->image);
	}

	/**
	 * Sends PNG to browser
	 *
	 * Sends a PNG image with proper header to output
	 */
	function outPNG() {
		header("Content-type: image/png");
		ImagePng($this->image);
	}
	
	/**
	 * Output GD Image
	 *
	 * Will give the developer the GD resource for the graph that
	 * can be used to store the graph to the FS or other media
	 *
	 * @return Resource GD Image Resource
	 */
	public function getImage() {
		return $this->image;
	}
	
	/**
	 * Output GD Image
	 *
	 * Alias for eqGraph::getImage()
	 *
	 * @return Resource GD Image resource
	 */
	public function outGD() {
		return $this->getImage();
	}
} //end class 'eqGraph'