<?php

namespace jlawrence\eos;

// fun class that requires the GD libraries to give visual output to the user 

/**
 * Equation Graph
 *
 * Fun class that requires the GD libraries to give visual output of an
 * equation to the user.  Extends the Parser class.
 *
 * @author Jon Lawrence <jlawrence11@gmail.com>
 * @copyright Copyright ©2005-2013 Jon Lawrence
 * @license http://opensource.org/licenses/LGPL-2.1 LGPL 2.1 License
 * @package Math
 * @subpackage EOS
 * @version 3.x
 */
class Graph
{
    private static $width = 640;
    private static $height = 480;

    /**
     * @var resource
     */
    private static $image;

    public static $labelAxis = true;
    public static $backgroundColor = array(255, 255, 255);
    public static $gridColor = array(150, 150, 150);
    public static $axisColor = array(0, 0, 0);
    public static $lineColor = array(0, 0, 0);

    /**
     * Initializer
     *
     * Sets up the Graph class with an image width and height defaults to
     * 640x480
     *
     * @param int $width Image width
     * @param int $height Image height
     */
    public static function init($width = 640, $height = 480)
    {
        // default width and height equal to that of a poor monitor (in early 2000s)
        self::$width = $width;
        self::$height = $height;

        // initialize main class
        Parser::init();
        //can't really mess this up, return true
        return true;
    }


    /**
     * Create GD Graph Image
     *
     * Creates a GD image based on the equation given with the parameters that are set
     *
     * @param string $eq Equation to use.  Needs variable in equation to create graph, all variables are interpreted as 'x'
     * @param integer $xLow Lower x-bound for graph
     * @param integer $xHigh Upper x-bound for graph
     * @param float $xStep Stepping points while solving, the lower, the better precision. Slow if lower than .01
     * @param bool $xyGrid Draw grid-lines?
     * @param bool $yGuess Guess the upper/lower yBounds?
     * @param int $yLow Lower y-bound
     * @param int $yHigh Upper y-bound
     * @return null
     */
    public static function graph($eq, $xLow, $xHigh, $xStep = null, $xyGrid = false, $yGuess = true, $yLow = null, $yHigh = null)
    {
        //create our image and allocate the two colors
        $img = ImageCreate(self::$width, self::$height);
        //The following noinspection needed because the first color allocated is the background, but not used for anything else.
        /** @noinspection PhpUnusedLocalVariableInspection */
        $bgColor = ImageColorAllocate($img, self::$backgroundColor[0], self::$backgroundColor[1], self::$backgroundColor[2]);
        $aColor = ImageColorAllocate($img, self::$axisColor[0], self::$axisColor[1], self::$axisColor[2]);
        $lColor = ImageColorAllocate($img, self::$lineColor[0], self::$lineColor[1], self::$lineColor[2]);
        $gColor = ImageColorAllocate($img, self::$gridColor[0], self::$gridColor[1], self::$gridColor[2]);
        //$black = ImageColorAllocate($img, 0, 0, 0);
        //$grey = ImageColorAllocate($img, 150, 150, 150);
        //$darkGrey = ImageColorAllocate($img, 50, 50, 50);
        if ($xLow > $xHigh)
            list($xLow, $xHigh) = array($xHigh, $xLow); //swap function
        //Smart xStep calc
        if ($xStep == false) {
            $xStep = ($xHigh - $xLow) / self::$width;
        }
        $xStep = abs($xStep);
        $hand = null;
        $xVars = array();
        //If yGuess is true, make sure yLow and yHigh are not set
        if ($yGuess) {
            $yLow = null;
            $yHigh = null;
        }
        //We want to limit the number of lines/ticks/etc so graph remains readable, set max now
        $xMaxLines = 30;
        $yMaxLines = 30;
        //DEVELOPER, UNCOMMENT NEXT LINE IF WANTING TO PREVENT SLOW GRAPHS
        //$xStep = ($xStep < .01) ? $xStep : 0.01;

        $xScale = self::$width / ($xHigh - $xLow);
        $counter = 0;
        // @codeCoverageIgnoreStart
        if (Math::$DEBUG) {
            $hand = fopen("Graph.txt", "w");
            fwrite($hand, "$eq\n");
        }
        // @codeCoverageIgnoreEnd
        for ($i = $xLow; $i <= $xHigh; $i += $xStep) {
            $tester = sprintf("%10.3f", $i);
            if ($tester == "-0.000") $i = 0;
            $y = Parser::solve($eq, $i);
            //eval('$y='. str_replace('&x', $i, $eq).";"); /* used to debug my Parser class results */
            // @codeCoverageIgnoreStart
            if (Math::$DEBUG) {
                $tmp1 = sprintf("y(%5.3f) = %10.3f\n", $i, $y);
                fwrite($hand, $tmp1);
            }
            // @codeCoverageIgnoreEnd

            // If developer asked us to find the upper and lower bounds for y...
            if ($yGuess == true) {
                $yLow = ($yLow === null || ($y < $yLow)) ? $y : $yLow;
                $yHigh = ($yHigh === null || $y > $yHigh) ? $y : $yHigh;
            }
            $xVars[$counter] = $y;
            $counter++;
        }


        //Now that we have all the variables stored...find the yScale
        $yScale = self::$height / (($yHigh) - ($yLow));
        // @codeCoverageIgnoreStart
        //Calculate the stepping points for lines now
        if ($yHigh - $yLow > $yMaxLines) {
            $yJump = ceil(($yHigh - $yLow) / $yMaxLines);
        } else {
            $yJump = 1;
        }
        if ($xHigh - $xLow > $xMaxLines) {
            $xJump = ceil(($xHigh - $xLow) / $xMaxLines);
        } else {
            $xJump = 1;
        }
        // @codeCoverageIgnoreEnd

        // add 0.01 to each side so that if y is from 1 to 5, the lines at 1 and 5 are seen
        $yLow -= 0.01;
        $yHigh += 0.01;

        // @codeCoverageIgnoreStart
        if (Math::$DEBUG) {
            fwrite($hand, $yLow . " -- " . $yHigh . "\n");
        }
        // @codeCoverageIgnoreEnd

        // if developer wanted a grid on the graph, add it now
        if ($xyGrid == true) {
            // @codeCoverageIgnoreStart
            if (Math::$DEBUG) {
                fwrite($hand, "Drawing Grid\n");
            }
            // @codeCoverageIgnoreEnd
            for ($i = ceil($yLow); $i <= floor($yHigh); $i += $yJump) {
                $i0 = abs($yHigh - $i);
                ImageLine($img, 0, $i0 * $yScale, self::$width, $i0 * $yScale, $gColor);
                imagestring($img, 1, 2, $i0 * $yScale + 2, $i, $gColor);
            }
            for ($i = ceil($xLow); $i <= floor($xHigh); $i += $xJump) {
                $i0 = abs($xLow - $i);
                ImageLine($img, $i0 * $xScale, 0, $i0 * $xScale, self::$height, $gColor);
                imagestring($img, 1, $i0 * $xScale + 2, 2, $i, $gColor);
            }
        }

        //Now that we have the scales, let's see if we can draw an x/y-axis
        if ($xLow <= 0 && $xHigh >= 0) {
            //the y-axis is within our range - draw it.
            $x0 = abs($xLow) * $xScale;
            ImageLine($img, $x0, 0, $x0, self::$height, $aColor);
            for ($i = ceil($yLow); $i <= floor($yHigh); $i += $yJump) {
                $i0 = abs($yHigh - $i);
                ImageLine($img, $x0 - 3, $i0 * $yScale, $x0 + 3, $i0 * $yScale, $aColor);
                //If we want the axis labeled... (call in the allies?)
                if (self::$labelAxis) {
                    imagestring($img, 1, $x0 + 2, $i0 * $yScale + 1, $i, $aColor);
                }
            }
        }
        if ($yLow <= 0 && $yHigh >= 0) {
            //the x-axis is within our range - draw it.
            $y0 = abs($yHigh) * $yScale;
            ImageLine($img, 0, $y0, self::$width, $y0, $aColor);
            //Create ticks for y
            for ($i = ceil($xLow); $i <= floor($xHigh); $i += $xJump) {
                $i0 = abs($xLow - $i);
                ImageLine($img, $i0 * $xScale, $y0 - 3, $i0 * $xScale, $y0 + 3, $aColor);
                //If we want the axis labeled....
                if (self::$labelAxis) {
                    imagestring($img, 1, $i0 * $xScale + 2, $y0 + 1, $i, $aColor);
                }
            }
        }
        $counter = 1;

        //now graph it all ;]
        for ($i = $xLow + $xStep; $i <= $xHigh; $i += $xStep) {
            $x1 = (abs($xLow - ($i - $xStep))) * $xScale;
            $y1 = (($xVars[$counter - 1] < $yLow) || ($xVars[$counter - 1] > $yHigh)) ? -1 : (abs($yHigh - $xVars[$counter - 1])) * $yScale;
            $x2 = (abs($xLow - $i)) * $xScale;
            $y2 = (($xVars[$counter] < $yLow) || ($xVars[$counter] > $yHigh)) ? -1 : (abs($yHigh - $xVars[$counter])) * $yScale;

            // if any of the y values were found to be off of the y-bounds, don't graph those connecting lines
            if ($y1 != -1 && $y2 != -1)
                ImageLine($img, $x1, $y1, $x2, $y2, $lColor);
            $counter++;
        }
        // @codeCoverageIgnoreStart
        if (Math::$DEBUG) {
            fclose($hand);
        }
        // @codeCoverageIgnoreEnd
        self::$image = $img;
    }

    /**
     * Sends JPG to browser
     *
     * Sends a JPG image with proper header to output
     *
     * @codeCoverageIgnore
     */
    public static function outJPG()
    {
        header("Content-type: image/jpeg");
        ImageJpeg(self::$image);
    }

    /**
     * Sends PNG to browser
     *
     * Sends a PNG image with proper header to output
     *
     * @codeCoverageIgnore
     */
    public static function outPNG()
    {
        header("Content-type: image/png");
        ImagePng(self::$image);
    }

    /**
     * Output GD Image
     *
     * Will give the developer the GD resource for the graph that
     * can be used to store the graph to the FS or other media
     *
     * @return Resource GD Image Resource
     */
    public static function getImage()
    {
        return self::$image;
    }

    /**
     * Output GD Image
     *
     * Alias for eqGraph::getImage()
     *
     * @return Resource GD Image resource
     */
    public static function outGD()
    {
        return self::getImage();
    }
}
