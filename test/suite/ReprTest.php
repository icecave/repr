<?php
namespace Icecave\Repr;

use PHPUnit_Framework_TestCase;

class ReprTest extends PHPUnit_Framework_TestCase
{
    public function testFacade()
    {
        $this->assertSame('"foo"', Repr::repr('foo'));
    }
}
