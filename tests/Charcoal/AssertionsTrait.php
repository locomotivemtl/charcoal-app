<?php

namespace Charcoal\Tests;

use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * Utilities for advanced assertions.
 */
trait AssertionsTrait
{
    /**
     * Asserts that the given haystack contains the expected subsets.
     *
     * @param  array   $expected The expected haystack.
     * @param  array   $haystack The actual haystack.
     * @param  boolean $strict   Whether to check for object identity.
     * @param  string  $message  The error to report.
     * @return void
     */
    public function assertArraySubsets(
        array $expected,
        array $haystack,
        $strict = false,
        $message = ''
    ) {
        foreach ($expected as $key => $val) {
            $this->assertArraySubset([ $key => $val ], $haystack, $strict, $message);
        }
    }

    /**
     * Asserts that an array has a specified subset.
     *
     * Note: This is a poor-man's fallback for a method previously available
     * in PHPUnit, deprecated in PHPUnit 8, and removed in PHPUnit 9.
     *
     * @param  array|ArrayAccess|mixed[] $subset                 The expected subset.
     * @param  array|ArrayAccess|mixed[] $array                  The actual haystack.
     * @param  boolean                   $checkForObjectIdentity Unused.
     * @param  string                    $message                The error to report.
     * @throws InvalidArgumentException
     * @return void
     */
    public function assertArraySubset($subset, $array, $checkForObjectIdentity = false, $message = ''): void
    {
        if (!(is_array($subset) || $subset instanceof ArrayAccess)) {
            throw InvalidArgumentException::create(
                1,
                'array or ArrayAccess'
            );
        }

        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentException::create(
                2,
                'array or ArrayAccess'
            );
        }

        foreach ($subset as $key => $value) {
            $this->assertArrayHasKey($key, $array, $message);

            if (is_array($value) || $value instanceof ArrayAccess) {
                $this->assertArraySubset($value, $array[$key], $message);
            } else {
                $this->assertSame($value, $array[$key], $message);
            }
        }
    }
}
