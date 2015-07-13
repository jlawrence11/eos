# EOS

## Composer Usage
[![Latest Stable Version](https://poser.pugx.org/jlawrence/eos/v/stable)](https://packagist.org/packages/jlawrence/eos) [![Total Downloads](https://poser.pugx.org/jlawrence/eos/downloads)](https://packagist.org/packages/jlawrence/eos)  [![License](https://poser.pugx.org/jlawrence/eos/license)](https://packagist.org/packages/jlawrence/eos)

Add the dependency:

	"require": {
	    "jlawrence/eos": "3.*"
	}


`$ composer update` and you're done.

## Equation Operating System

### jlawrence\eos\

This class makes it incredibly easy to use and parse/solve equations in
your own applications. __NOTE__ ALL of the functions within
these classes are static. It is also important to note that these
classes throw exceptions if running in to errors, please read the beginning
of the `Math.php` file for the defines of the exceptions thrown. Exceptions
includes a descriptive message of the error encountered and within `Parser` will
also typically include the full equation used.

#### Parser

This class has one important function, `Parser::solve()` which does all the legwork,
so we'll start there and end with examples.

    use jlawrence\eos\Parser;

##### solve($infix, $variables)

To use this function:

    $value = Parser::solve($eq, $vars);

###### _$infix_

Is simply a standard equation with variable support.

Example Equations:

	2(4x)
    5+((1+2)*4)+3
    5+4(1+2)+3
    10*sin(x)
    10*cos(x)

The parser has good implied multiplication, for everything but allowed functions.
Allowed functions require an explicit operator on either/both sides to work properly,
I hope to change that in the next revision; but for now, note that it will not work
as you would expect.
For example:

    5sin(1.5707963267) = 51
    5*sin(1.5707963267) = 5
    sin(1.5707963267)5 = 15

The reason is because there is no implied multiplication being applied, the result
of `sin(1.5707963267) = 1` is being concatenated with the number 5, giving
incredibly odd results if you are not expecting it.

###### _$variables_

The variables are fairly simple to understand.  If it contains a scalar (ie
a non-array value) _every_ variable within the equation will be replaced with
that number.  If it contains an array, there will be a by-variable replacement -
note that the array MUST be in the format of `'variable' => value`  
Such as:

    array(
        'x' => 2,
        'y' => 3
    )

Given the equation:

    5x^y

If this is called by:

    Parser::solveIF('5x^y', 2)

It will equal '20', as every variable is replaced by 2.  However, if called like:

    Parser::solveIF('5x^y', array(
                                'x' => 2,
                                'y' => 3));

You will get the result of '40' as it would equate to `5*2^3`, as expected.

---
