<?php



if (!function_exists('create_function')) {

    function create_function($args, $code) {
        return create_function_php8($args, $code);
    }

    define('LOMBAX_PHP8_CREATE_FUNCTION_REPLACEMENT_ENABLED', true);
} else {
    define('LOMBAX_PHP8_CREATE_FUNCTION_REPLACEMENT_ENABLED', false);
}

function create_function_php8($args, $code) {

    // try to infer "byref"
    $byref = false;
    if (strpos($args, "&") !== false) {
        $byref = true;
    }

    if ($byref === true) {
        $func = function (&...$runtimeArgs) use ($args, $code, $byref) {
            return lombax_create_function_closure($args, $code, $runtimeArgs, $byref);
        };
    } else {
        $func = function (...$runtimeArgs) use ($args, $code, $byref) {
            return lombax_create_function_closure($args, $code, $runtimeArgs, $byref);
        };
    }

    return $func;
}

function lombax_create_function_closure($args, $code, $runtimeArgs, $byref = false) {
    $args = str_replace(" ", "", $args);
    $args = explode(",", $args);

    // declare the args with variable variables
    $i = 0;
    foreach ($args as $singleArg) {
        $newArg = $args[$i];
        // simple variable
        if (substr($singleArg, 0, 1) == "$") {
            $newArg = str_replace("$", "", $newArg);
            $$newArg = $runtimeArgs[$i];
            // variable passed by reference
        } else if (substr($singleArg, 0, 1) == "&") {
            if ($byref === true) {
                $newArg = str_replace("&$", "", $newArg);
                $$newArg = &$runtimeArgs[$i];
            } else {
                throw new Exception("Cannot pass variables by reference, use create_function_php8_byref instead");
            }
        } else {
            throw new Exception("create_function replacement, not managed case");
        }

        $i++;
    }

    $res = eval($code);
    return $res;
}

function is_autoload_working() {
  return true;
}
