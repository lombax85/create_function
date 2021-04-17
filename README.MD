# PHP 8 create_function replacement

This repository contains a replacement for the function `create_function` that has been removed from PHP 8

This is a first beta version with some limitations

## Installation

`composer require lombax85/create_function`

## Usage

```
// Simple function
$f = create_function('$a,$b', 'return $a+$b;');
echo $f(1,2); // prints 3

// pass by reference
$f = create_function('&$a', '$a++;');
$a = 1;
$f($a);
echo $a; // prints 2
```
