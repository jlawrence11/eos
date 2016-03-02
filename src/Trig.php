<?php
/**
 * Created by: Jon Lawrence on 2015-07-02 3:10 PM.
 */
namespace jlawrence\eos;

/**
 * Class Trig.
 */
class Trig
{
    /**
     * @var bool Whether or not to convert to radians before calculation
     *           (Meaning input is in degree form)
     */
    public static $DEGREES = false;

    protected static function getRadDeg($x)
    {
        if (self::$DEGREES == true) {
            return deg2rad($x);
        }

        return $x;
    }

    public static function cos($x)
    {
        return cos(self::getRadDeg($x));
    }

    public static function sin($x)
    {
        return sin(self::getRadDeg($x));
    }

    public static function tan($x)
    {
        return tan(self::getRadDeg($x));
    }

    public static function sec($x)
    {
        $tmp = self::cos($x);
        if ($tmp == 0) {
            throw new \Exception("Division by 0 on: 'sec({$x}) = 1/cos({$x})' in ".Parser::$inFix, Math::E_DIV_ZERO);
        }

        return 1 / $tmp;
    }

    public static function csc($x)
    {
        $tmp = self::sin($x);
        if ($tmp == 0) {
            throw new \Exception("Division by 0 on: 'csc({$x})) = 1/sin({$x})' in ".Parser::$inFix, Math::E_DIV_ZERO);
        }

        return 1 / $tmp;
    }

    public static function cot($x)
    {
        $tmp = self::tan($x);
        if ($tmp == 0) {
            throw new \Exception("Division by 0 on: 'cot({$x})) = 1/tan({$x})' in ".Parser::$inFix, Math::E_DIV_ZERO);
        }

        return 1 / $tmp;
    }
}
