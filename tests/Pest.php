<?php

/*
|--------------------------------------------------------------------------
| Uses
|--------------------------------------------------------------------------
|
| Here you may define the classes or traits that should be used by your tests.
|
 */
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

uses(WebTestCase::class)->in('Controller');

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend(name: 'toBeOne', extend: function () {
    return $this->toBe(expected: 1);
});


expect()->extend(name: 'toMatchJson', extend: function (array $expected) {
    $this->value = json_decode($this->value, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
    return $this->toMatchArray($expected);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
