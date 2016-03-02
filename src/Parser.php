<?php
/**
 * Equation Operating System Classes.
 *
 * This class was created for the safe parsing of mathematical equations
 * in PHP.  There is a need for a way to successfully parse equations
 * in PHP that do NOT require the use of `eval`.  `eval` at its core
 * opens the system using it to so many security vulnerabilities it is oft
 * suggested /never/ to use it, and for good reason.  This class set will
 * successfully take an equation, parse it, and provide solutions to the
 * developer.  It is a safe way to evaluate expressions without putting
 * the system at risk.
 *
 *
 * @author Jon Lawrence <jlawrence11@gmail.com>
 * @copyright Copyright ©2005-2015, Jon Lawrence
 * @license http://opensource.org/licenses/LGPL-2.1 LGPL 2.1 License
 * @version 3.0.0
 */

namespace jlawrence\eos;


/**
 * Equation Operating System (EOS) Parser
 *
 * A class that can safely parse mathematical equations.  Re-written portions
 * from version 2.x to be extend-able with custom functions.
 *
 * @author Jon Lawrence <jlawrence11@gmail.com>
 * @copyright Copyright ©2005-2015, Jon Lawrence
 * @license http://opensource.org/licenses/LGPL-2.1 LGPL 2.1 License
 * @version 3.0.0
 */
class Parser {

    /**
     * @var string Infix equation
     * Public so advanced/user-defined/etc can access it when throwing exceptions.
     */
    public static $inFix;

    /**
     * @var array Opening and closing selectors
     */
    protected static $SEP = array(
        'open' => array('(', '['),
        'close' => array(')', ']')
    );

    // Top precedence following operator - not in use
    protected static $SGL = array('!');

    // Order of operations arrays follow
    protected static $ST = array('^', '!');
    protected static $ST1 = array('/', '*', '%');
    protected static $ST2 = array('+', '-');

    /**
     * @var array Allowed functions
     */
    protected static $FNC = array(
        'sin', 'cos', 'tan',
        'csc', 'sec', 'cot',
        'abs', 'ln', 'sqrt'
    );

    /**
     * @var array Advanced functions container
     */
    protected static $AFNC = array();

    /**
     * Initialize
     */
    public static function init() {
        if (empty(self::$AFNC)) {
            //No advanced functions yet, so this function has not run, do so now.
            self::$AFNC = AdvancedFunctions::map();
        }
    }

    /**
     * Add Advanced Function Class
     *
     * Adds a function class to the parser for user/programmer defined
     * functions that can be parsed with the parser.  For example
     * class structure see jlawrence\eos\AdvancedFunctions.
     * Class must be static, and must have a function named 'map'
     *
     * @param string $class Fully Qualified String to class (must include namespace)
     * @return bool True on success
     * @throws \Exception When the added class doesn't have the 'map' function or doesn't exist.
     *
     * @codeCoverageIgnore
     */
    public static function addFunctionClass($class)
    {
        self::init();
        if(is_callable("{$class}::map")) {
            $a = call_user_func($class.'::map');
            self::$AFNC = array_merge($a, self::$AFNC);
        } else {
            throw new \Exception("{$class}::map() is not callable");
        }
        return true;
    }

    /**
     * Check Infix for opening closing pair matches.
     *
     * This function is meant to solely check to make sure every opening
     * statement has a matching closing one, and throws an exception if
     * it doesn't.
     *
     * @param string $infix Equation to check
     * @throws \Exception if malformed.
     * @return Bool true if passes - throws an exception if not.
     */
    private static function checkInfix($infix) {
        self::init();
        if(trim($infix) == "") {
            throw new \Exception("No Equation given", Math::E_NO_EQ);
        }
        //Make sure we have the same number of '(' as we do ')'
        // and the same # of '[' as we do ']'
        if(substr_count($infix, '(') != substr_count($infix, ')')) {
            throw new \Exception("Mismatched parenthesis in ". self::$inFix, Math::E_NO_SET);
        } elseif(substr_count($infix, '[') != substr_count($infix, ']')) {
            throw new \Exception("Mismatched brackets in '". self::$inFix, Math::E_NO_SET);
        }
        return true;
    }

    /**
     * Infix to Postfix
     *
     * Converts an infix (standard) equation to postfix (RPN) notation.
     * Sets the internal variable $this->postFix for the Parser::solvePF()
     * function to use.
     *
     * @link http://en.wikipedia.org/wiki/Infix_notation Infix Notation
     * @link http://en.wikipedia.org/wiki/Reverse_Polish_notation Reverse Polish Notation
     * @param string $infix A standard notation equation
     * @throws \Exception When parenthesis are mismatched
     * @return array Fully formed RPN Stack
     */
    public static function in2post($infix) {
        // if an equation was not passed, use the one that was passed in the constructor
        //$infix = (isset($infix)) ? $infix : $this->inFix;

        //check to make sure 'valid' equation
        self::checkInfix($infix);
        $pf = array();
        $ops = new Stack();
        //$vars = new Stack();

        // remove all white-space
        $infix = preg_replace("/\s/", "", $infix);

        // Create postfix array index
        $pfIndex = 0;

        //what was the last character? (useful for discerning between a sign for negation and subtraction)
        $lChar = '';

        //loop through all the characters and start doing stuff ^^
        for($i=0;$i<strlen($infix);$i++) {
            // pull out 1 character from the string
            $chr = substr($infix, $i, 1);

            // if the character is numerical
            if(preg_match('/[0-9.]/i', $chr)) {
                // if the previous character was not a '-' or a number
                if((!preg_match('/[0-9.]/i', $lChar) && ($lChar != "")) && (isset($pf[$pfIndex]) && ($pf[$pfIndex]!="-")))
                    $pfIndex++;	// increase the index so as not to overlap anything
                // Add the number character to the array
                if(isset($pf[$pfIndex])) {
                    $pf[$pfIndex] .= $chr;
                } else {
                    $pf[$pfIndex] = $chr;
                }

            }
            // If the character opens a set e.g. '(' or '['
            elseif(in_array($chr, self::$SEP['open'])) {
                // if the last character was a number, place an assumed '*' on the stack
                if(preg_match('/[0-9.]/i', $lChar))
                    $ops->push('*');

                $ops->push($chr);
            }
            // if the character closes a set e.g. ')' or ']'
            elseif(in_array($chr, self::$SEP['close'])) {
                // find what set it was i.e. matches ')' with '(' or ']' with '['
                $key = array_search($chr, self::$SEP['close']);
                // while the operator on the stack isn't the matching pair...pop it off
                while($ops->peek() != self::$SEP['open'][$key]) {
                    $nchr = $ops->pop();
                    if($nchr)
                        $pf[++$pfIndex] = $nchr;
                    else {
                        //Should NEVER get here...
                        // @codeCoverageIgnoreStart
                        throw new \Exception("Error while searching for '". self::$SEP['open'][$key] ."' in ". self::$inFix, Math::E_NO_SET);
                        // @codeCoverageIgnoreEnd
                    }
                }
                $ops->pop();
            }
            // If a special operator that has precedence over everything else
            elseif(in_array($chr, self::$ST)) {
                while(in_array($ops->peek(), self::$ST))
                    $pf[++$pfIndex] = $ops->pop();
                $ops->push($chr);
                $pfIndex++;
            }
            // Any other operator other than '+' and '-'
            elseif(in_array($chr, self::$ST1)) {
                while(in_array($ops->peek(), self::$ST1) || in_array($ops->peek(), self::$ST))
                    $pf[++$pfIndex] = $ops->pop();

                $ops->push($chr);
                $pfIndex++;
            }
            // if a '+' or '-'
            elseif(in_array($chr, self::$ST2)) {
                // if it is a '-' and the character before it was an operator or nothingness (e.g. it negates a number)
                if((in_array($lChar, array_merge(self::$ST1, self::$ST2, self::$ST, self::$SEP['open'])) || $lChar=="") && $chr=="-") {
                    // increase the index because there is no reason that it shouldn't..
                    $pfIndex++;
                    $pf[$pfIndex] = $chr;
                }
                // Otherwise it will function like a normal operator
                else {
                    while(in_array($ops->peek(), array_merge(self::$ST1, self::$ST2, self::$ST)))
                        $pf[++$pfIndex] = $ops->pop();
                    $ops->push($chr);
                    $pfIndex++;
                }
            }
            // make sure we record this character to be referred to by the next one
            $lChar = $chr;
        }
        // if there is anything on the stack after we are done...add it to the back of the RPN array
        while(($tmp = $ops->pop()) !== false)
            $pf[++$pfIndex] = $tmp;

        // re-index the array at 0
        $pf = array_values($pf);

        // set the private variable for later use if needed
        //self::$postFix = $pf;

        // return the RPN array in case developer wants to use it for some insane reason (bug testing ;] )
        // Also... because we pass it right in to the RPN solver.  So I guess there's that too.
        return $pf;
    }

    /**
     * Solve Postfix (RPN)
     *
     * This function will solve a RPN array. Default action is to solve
     * the RPN array stored in the class from Parser::in2post(), can take
     * an array input to solve as well, though default action is preferred.
     *
     * @link http://en.wikipedia.org/wiki/Reverse_Polish_notation Postix Notation
     * @param array $pfArray RPN formatted array. Optional.
     * @throws \Exception on division by 0
     * @return float Result of the operation.
     */
    public static function solvePF($pfArray) {
        $pf = $pfArray;

        // create our temporary function variables
        $temp = array();
        //$tot = 0;
        $hold = 0;

        // Loop through each number/operator
        for($i=0;$i<count($pf); $i++) {
            // If the string isn't an operator, add it to the temp var as a holding place
            if(!in_array($pf[$i], array_merge(self::$ST, self::$ST1, self::$ST2))) {
                $temp[$hold++] = $pf[$i];
            }
            // ...Otherwise perform the operator on the last two numbers
            else {
                switch ($pf[$i]) {
                    case '+':
                        $temp[$hold-2] = $temp[$hold-2] + $temp[$hold-1];
                        break;
                    case '-':
                        $temp[$hold-2] = $temp[$hold-2] - $temp[$hold-1];
                        break;
                    case '*':
                        $temp[$hold-2] = $temp[$hold-2] * $temp[$hold-1];
                        break;
                    case '/':
                        if($temp[$hold-1] == 0) {
                            throw new \Exception("Division by 0 on: '{$temp[$hold-2]} / {$temp[$hold-1]}' in ". self::$inFix, Math::E_DIV_ZERO);
                        }
                        $temp[$hold-2] = $temp[$hold-2] / $temp[$hold-1];
                        break;
                    case '^':
                        $temp[$hold-2] = pow($temp[$hold-2], $temp[$hold-1]);
                        break;
                    case '!':
                        $temp[$hold-1] = self::factorial($temp[$hold-1]);
                        $hold++;
                        break;
                    case '%':
                        if($temp[$hold-1] == 0) {
                            throw new \Exception("Division by 0 on: '{$temp[$hold-2]} % {$temp[$hold-1]}' in ". self::$inFix, Math::E_DIV_ZERO);
                        }
                        $temp[$hold-2] = bcmod($temp[$hold-2], $temp[$hold-1]);
                        break;
                }
                // Decrease the hold var to one above where the last number is
                $hold = $hold-1;
            }
        }

        // return the last number in the array
        return $temp[$hold-1];
    }


    /**
     * Solve
     *
     * This function is called by the user to solve an equation within the parser system
     * No internal functions or added advanced functions should ever call this. Sets
     * the internal $infix variable for use in thrown exceptions. The variable array must
     * be in the format of 'variable' => value. If variable array is scalar (ie 5), all
     * variables will be replaced with it.
     *
     * @param string $equation Equation to Solve
     * @param array|double $values variable values
     * @return float Answer to the equation
     */
    public static function solve($equation, $values = null) {
        if(is_array($equation)) {
            return self::solvePF($equation);
        } else {
            self::$inFix = $equation;
            return self::solveIF($equation, $values);
        }
    }

    /**
     * Solve Infix (Standard) Notation Equation
     *
     * Will take a standard equation with optional variables and solve it.
     * This function is the one for programmers making modules for this
     * package should call as it does not set the internal variable for
     * the equation.  This should not be used by the programmer/user that
     * is using this package to solve equations.
     *
     * @param string $infix Standard Equation to solve
     * @param string|array $vArray Variable replacement
     * @throws \Exception On division by zero or NaN
     * @return float Solved equation
     */
    public static function solveIF($infix, $vArray = null) {
        //Check to make sure a 'valid' expression
        self::checkInfix($infix);

        //$ops = new Stack();
        //$vars = new Stack();
        $hand = null;

        //remove all white-space
        $infix = preg_replace("/\s/", "", $infix);

        $infix = self::checkAdvancedInput($infix,$vArray);

        // Finds all the 'functions' within the equation and calculates them
        //Nested parenthesis are now a go!
        while((preg_match("/(". implode("|", self::$FNC) . ")\(((?:[^()]|\((?2)\))*+)\)/", $infix, $match)) != 0) {
            $func = self::solveIF($match[2], $vArray);
            switch($match[1]) {
                case "cos":
                    $ans = Trig::cos($func);
                    break;
                case "sin":
                    $ans = Trig::sin($func);
                    break;
                case "tan":
                    $ans = Trig::tan($func);
                    break;
                case "sec":
                    $ans = Trig::sec($func);
                    break;
                case "csc":
                    $ans = Trig::csc($func);
                    break;
                case "cot":
                    $ans = Trig::cot($func);
                    break;
                case "abs":
                    $ans = abs($func);
                    break;
                case "ln":
                    $ans = log($func);
                    if(is_nan($ans) || is_infinite($ans)) {
                        throw new \Exception("Result of 'ln({$func}) = {$ans}' is either infinite or a non-number in ". self::$inFix, Math::E_NAN);
                    }
                    break;
                case "sqrt":
                    if($func < 0) {
                        throw new \Exception("Result of 'sqrt({$func}) = i' in ". self::$inFix .".  We can't handle imaginary numbers", Math::E_NAN);
                    }
                    $ans = sqrt($func);
                    break;
                // @codeCoverageIgnoreStart
                default:
                    $ans = 0;
                    break;
                // @codeCoverageIgnoreEnd
            }
            $infix = str_replace($match[0], "({$ans})", $infix);
        }

        //replace scientific notation with normal notation (2e-9 to 2*10^-9)
        $infix = preg_replace('/([\d])([eE])(-?\d)/', '$1*10^$3', $infix);

        $infix = self::replaceVars($infix, $vArray);

        return self::solvePF(self::in2post($infix));
    }

    /**
     * checkAdvancedInput
     *
     * Will take the input from `Parser::solveIF()` and solve all the advanced functions
     * that exist within it, returning it to the function when done for further
     * processing.
     *
     * @param string $input Check for advanced functions, recursively go through them.
     * @param array|int|null $vArray Variables from user-input
     * @return string The input with all advanced functions solved for.
     */
    protected static function checkAdvancedInput($input, $vArray)
    {
        $infix = $input;
        //Advanced/User-defined functions
        while((preg_match("/(". implode("|", array_keys(self::$AFNC)) . ")\(((?:[^()]|\((?2)\))*+)\)/", $infix, $match)) != 0) {
            $method = self::$AFNC[$match[1]];
            if(stripos($match[2], '(') !== false) {
                $match[2] = self::checkAdvancedInput($match[2], $vArray);
            }
            $ans = call_user_func($method, $match[2], $vArray);
            $infix = str_replace($match[0], "({$ans})", $infix);
        }
        return $infix;
    }

    /**
     * @param string $infix
     * @param array $vArray
     * @return string
     * @throws \Exception
     */
    protected static function replaceVars($infix, $vArray)
    {
        //Remove old '$' and '&' signis so the regex works properly.
        $infix = preg_replace('/[$&]/', "", $infix);
        //Find all the variables that were passed and replaces them
        while((preg_match('/([^a-zA-Z]){0,1}([a-zA-Z]+)([^a-zA-Z]){0,1}/', $infix, $match)) != 0) {

            //remove notices by defining if undefined.
            if(!isset($match[3])) {
                $match[3] = "";
            }

            // Ensure that the variable has an operator or something of that sort in front and back - if it doesn't, add an implied '*'
            if((!in_array($match[1], array_merge(self::$ST, self::$ST1, self::$ST2, self::$SEP['open'])) && $match[1] != "") || is_numeric($match[1])) //$this->SEP['close'] removed
                $front = "*";
            else
                $front = "";

            if((!in_array($match[3], array_merge(self::$ST, self::$ST1, self::$ST2, self::$SEP['close'])) && $match[3] != "") || is_numeric($match[3])) //$this->SEP['open'] removed
                $back = "*";
            else
                $back = "";

            //Make sure that the variable does have a replacement
            //First check for pi and e variables that wll automatically be replaced
            if(in_array(strtolower($match[2]), array('pi', 'e'))) {
                $t = (strtolower($match[2])=='pi') ? pi() : exp(1);
                $infix = str_replace($match[0], $match[1] . $front. $t. $back . $match[3], $infix);
            } elseif(!isset($vArray[$match[2]]) && (!is_array($vArray != "") && !is_numeric($vArray) && 0 !== $vArray)) {
                throw new \Exception("Variable replacement does not exist for '". $match[2] ."' in ". self::$inFix .".", Math::E_NO_VAR);
            } elseif(!isset($vArray[$match[2]]) && (!is_array($vArray != "") && is_numeric($vArray))) {
                $infix = str_replace($match[0], $match[1] . $front. $vArray. $back . $match[3], $infix);
            } elseif(isset($vArray[$match[2]])) {
                $infix = str_replace($match[0], $match[1] . $front. $vArray[$match[2]]. $back . $match[3], $infix);
            }
        }

        return $infix;
    }

    /**
     * Solve factorial (!)
     *
     * Will take an integer and solve for it's factorial. Eg.
     * `5!` will become `1*2*3*4*5` = `120`
     * DONE:
     *    Solve for non-integer factorials  2015/07/02
     *
     * @param float $num Non-negative real number to get factorial of
     * @throws \Exception if number is at or less than 0
     * @return float Solved factorial
     */
    protected static function factorial($num) {
        if($num < 0) {
            throw new \Exception("Factorial Error: Factorials don't exist for numbers < 0 in ". self::$inFix, Math::E_NAN);
        }
        //A non-integer!  Gamma that sucker up!
        if(intval($num) != $num) {
            return self::gamma($num + 1);
        }

        $tot = 1;
        for($i=1;$i<=$num;$i++) {
            $tot *= $i;
        }

        return $tot;
    }

    /**
     * Gamma Function
     *
     * Because we can. This function exists as a catch-all for different
     * numerical approx. of gamma if I decide to add any past Lanczos'.
     *
     * @param $z Number to compute gamma from
     * @return float The gamma (hopefully, I'll test it after writing the code)
     */
    public static function gamma($z)
    {
        return self::laGamma($z);
    }

    /**
     * Lanczos Approximation
     *
     * The Lanczos Approximation method of finding gamma values
     *
     * @link http://www.rskey.org/CMS/index.php/the-library/11
     * @link http://algolist.manual.ru/maths/count_fast/gamma_function.php
     * @link https://en.wikipedia.org/wiki/Lanczos_approximation
     * @param $z Number to obtain the gamma of
     * @return float Answer
     */
    protected static function laGamma($z)
    {
        // Set up coefficients
        $p = array(
            0 => 1.000000000190015,
            1 => 76.18009172947146,
            2 => -86.50532032941677,
            3 => 24.01409824083091,
            4 => -1.231739572450155,
            5 => 1.208650973866179E-3,
            6 => -5.395239384953E-6
        );

        // Formula:
        // ((sqrt(2pi)/z)(p[0]+sum(p[n]/(z+n), 1, 6)))(z+5.5)^(z+0.5)*e^(-(z+5.5))

        // Break it down now...
        $g1 = sqrt(2*pi())/$z;

        // Next comes our summation
        $g2 =0;
        for($n=1;$n<=6;$n++) {
            $g2 += $p[$n]/($z+$n);
        }

        // Don't forget to add p[0] to it...
        $g2 += $p[0];
        $g3 = pow($z+5.5, $z + .5);
        $g4 = exp(-($z+5.5));

        //now just multiply them all together
        $gamma = $g1 * $g2 * $g3 * $g4;

        return $gamma;
    }
}
