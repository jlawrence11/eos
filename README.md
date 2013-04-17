# Classes
---

## eos.class.php

### Equation Operating System

This class makes it incredibly easy to use and parse/solve equations in
your own applications. It includes a graph generator to turn an equation
with a variable in to a `y=x` graph, with the capability to calculate
the upper and lower `y-bounds`.  __NOTE__ NONE of the functions within
these two classes are static, any example that looks like a static function
call is representational of the class being used, but should be initialized
and assigned to a variable first.

#### eqEOS

This class has one important function, `eqEOS::solveIF()` which does all the legwork,
so we'll start there and end with examples.  
To initialize this class, use:

    $eos = new eqEOS();

##### solveIF($infix, $variables)

To use this function:

    $value = $eos->solveIF($eq, $vars);

###### _$infix_

Is simply a standard equation with variable support. Variables
have two forms, one is native to PHP programmers already, prefixed with '$'.
The other way to declare a variable is with '&amp;' and is included for
backward compatibility for with the initial version from 2005.  
Example Equations:

    2(4$x)
    2(4&x)
    5+ ((1+2)*4) +3
    5+4(1+2)+3
    10*sin($x)
    10*cos($x)

The first two pairs shown are exactly the same.  The parser has good implied
multiplication, for everything but allowed functions.  Allowed functions require
an implicit operator on either/both sides to work properly, I hope to change
that in the next revision; but for now, note that it will not work as you would
expect.  
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

    5$x^$y

If this is called by:

    eqEOS::solveIF('5$x^$y', 2)

It will equal '20', as every variable is replaced by 2.  However, if called like:

    eqEOS::solveIF('5$x^$y', array(
                                'x' => 2,
                                'y' => 3);

You will get the result of '40' as it would equate to '5*2^3', as expected.

---