<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once './src/helper.php';

final class Test extends TestCase
{
    public function testAutoloadWorking() : void
    {
        $this->assertTrue(is_autoload_working());
    }

    public function testIsReplacementCreateFunctionEnabled() : void
    {
        $this->assertTrue(LOMBAX_PHP8_CREATE_FUNCTION_REPLACEMENT_ENABLED);
    }

    // tests from https://www.php.net/manual/en/function.create-function.php

    public function testExampleOne() : void
    {
        $newfunc = create_function('$a,$b', 'return "ln($a) + ln($b) = " . log($a * $b);');
        $res = $newfunc(2, M_E) . "\n";

        $this->assertEquals("ln(2) + ln(2.718281828459) = 1.6931471805599\n", $res);
    }

    public function testExampleTwo() : void
    {

        $echo = "";

        function process($var1, $var2, $farr, &$echo)
        {
            foreach ($farr as $f) {
                $echo .= $f($var1, $var2) . "\n";
            }
        }

        // create a bunch of math functions
        $f1 = 'if ($a >=0) {return "b*a^2 = ".$b*sqrt($a);} else {return false;}';
        $f2 = "return \"min(b^2+a, a^2,b) = \".min(\$a*\$a+\$b,\$b*\$b+\$a);";
        $f3 = 'if ($a > 0 && $b != 0) {return "ln(a)/b = ".log($a)/$b; } else { return false; }';
        $farr = array(
            create_function('$x,$y', 'return "some trig: ".(sin($x) + $x*cos($y));'),
            create_function('$x,$y', 'return "a hypotenuse: ".sqrt($x*$x + $y*$y);'),
            create_function('$a,$b', $f1),
            create_function('$a,$b', $f2),
            create_function('$a,$b', $f3)
        );

        $echo .= "\nUsing the first array of anonymous functions\n";
        $echo .= "parameters: 2.3445, M_PI\n";
        process(2.3445, M_PI, $farr, $echo);

        // now make a bunch of string processing functions
        $garr = array(
            create_function('$b,$a', 'if (strncmp($a, $b, 3) == 0) return "** \"$a\" '.
                'and \"$b\"\n** Look the same to me! (looking at the first 3 chars)";'),
            create_function('$a,$b', '; return "CRCs: " . crc32($a) . ", ".crc32($b);'),
            create_function('$a,$b', '; return "similar(a,b) = " . similar_text($a, $b, $p) . "($p%)";')
        );
        $echo .= "\nUsing the second array of anonymous functions\n";
        process("Twas brilling and the slithy toves", "Twas the night", $garr, $echo);

        $expected = '
Using the first array of anonymous functions
parameters: 2.3445, M_PI
some trig: -1.6291725057799
a hypotenuse: 3.9199852871011
b*a^2 = 4.8103313314525
min(b^2+a, a^2,b) = 8.6382729035898
ln(a)/b = 0.27122299212594

Using the second array of anonymous functions
** "Twas the night" and "Twas brilling and the slithy toves"
** Look the same to me! (looking at the first 3 chars)
CRCs: 3569586014, 342550513
similar(a,b) = 11(45.833333333333%)
';

        $this->assertEquals($echo, $expected);
    }


    public function testExampleThree() : void
    {

        // suppress warnings
        $prev = error_reporting(E_ALL & ~E_WARNING);

        $av = array("the ", "a ", "that ", "this ");
        $f = create_function('&$v,&$k', '$v = $v . "mango";');
        array_walk($av, $f);

        $compare = [
            'the mango',
            'a mango',
            'that mango',
            'this mango'
        ];

        $this->assertEquals($compare, $av);

        error_reporting($prev);
    }



}
