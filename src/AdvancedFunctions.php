<?php
/**
 * Created by: Jon Lawrence on 2015-07-13 8:42 AM
 */

namespace jlawrence\eos;


class AdvancedFunctions 
{

    /**
     * Create a list for the parser of 'expressiveFunction' => 'class::function'
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
     * @param $input String In the form of "$equation, start, stop"
     * @param $vars Array of variables used for solving the current equation.  Unused here.
     * @return Float The summation of the equation
     */
    public static function sum($input, /** @noinspection PhpUnusedParameterInspection */
                               $vars)
    {
       //remove whitespace
        $input = preg_replace("/\s/", "", $input);
        //split in to parts
        list($eq, $start, $stop) = explode(",", $input);
        $ret = 0;
        for($i=$start; $i <= $stop; $i++) {
            $ret += Parser::solveIF($eq, $i);
        }

        return $ret;
    }

    /**
     * Log function for all non-natural logs.  Defaults to base 10
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
        $ans = log($sc, $base);
        if(is_nan($ans) || is_infinite($ans)) {
            throw new \Exception("Result of 'log({$eq}, {$base}) = {$ans}' is either infinite or a non-number.", Math::E_NAN);
        }
        return $ans;
    }
} 