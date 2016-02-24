<?php
/**
 * Created by: Jon Lawrence on 2015-07-13 8:42 AM
 */

namespace jlawrence\eos;


class AdvancedFunctions
{

    /**
     * Create a list for the parser of 'expressiveFunction' => 'class::function'
     *
     * @return array
     */
    public static function map()
    {
        $ret = array(
            'sum' => __NAMESPACE__ .'\AdvancedFunctions::sum',
            'log' => __NAMESPACE__ .'\AdvancedFunctions::log'
        );

        return $ret;
    }

    /**
     * Summation function
     *
     * Will take an equation and run it through a summation algorithm. All parts
     * of the input can be in equation form, so the start and stops can have
     * equations to determine what they should be using the globally inputted
     * variables from the user.
     *
     * @param string $input String in the form of "equation, start, stop"
     * @param array $vars Array of variables used for solving the current equation.
     * @return float The summation of the equation
     */
    public static function sum($input, $vars)
    {
       //remove whitespace
        $input = preg_replace("/\s/", "", $input);
        //split in to parts
        list($eq, $start, $stop) = explode(",", $input);
        $ret = 0;
        //make sure there's a variable, or return equation as-is
        if((Parser::solveIF($eq,0)) == preg_replace("/[\(\)]/", "", $eq)) {
            return $eq;
        }
        $start = Parser::solveIF($start, $vars);
        $stop = Parser::solveIF($stop, $vars);
        for($i=$start; $i <= $stop; $i++) {
            $ret += Parser::solveIF($eq, $i);
        }

        return $ret;
    }

    /**
     * Log function for all non-natural logs.  Defaults to base 10
     *
     * @param $input
     * @param array $vars Variable replacement
     * @return float
     * @throws \Exception
     */
    public static function log($input, $vars)
    {
        $base = 10;
        if(stripos($input, ",")) {
            list($eq, $base) = explode(",", $input);
        } else {
            $eq = $input;
        }
        //Make sure no functions or operators are hidden inside
        $sc = Parser::solveIF($eq, $vars);
        if(10 != $base) {
            $base = Parser::solveIF($base, $vars);
        }
        $ans = log($sc, $base);
        if(is_nan($ans) || is_infinite($ans)) {
            throw new \Exception("Result of 'log({$eq}, {$base}) = {$ans}' is either infinite or a non-number in ". Parser::$inFix, Math::E_NAN);
        }
        return $ans;
    }
} 