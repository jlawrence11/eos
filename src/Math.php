<?php
/**
 * Created by: Jon Lawrence on 2015-07-02 2:49 PM
 */

namespace jlawrence\eos;

/**
 * Class Math
 * @package jlawrence\eos
 *
 * Will be a holder for constants, variables, and other things commonly needed by the
 * rest of the package.
 */
class Math 
{
    /**
     * No matching open/close pair in equation
     */
    const E_NO_SET = 5500;

    /**
     * Division by zero
     */
    const E_DIV_ZERO = 5501;

    /**
     * No equation present
     */
    const E_NO_EQ = 5502;

    /**
     * No variable replacements available
     */
    const E_NO_VAR = 5503;

    /**
     * Not A Number (NAN)
     */
    const E_NAN = 5504;

    /**
     * @var bool Use debug features
     */
    public static $DEBUG = false;

} 