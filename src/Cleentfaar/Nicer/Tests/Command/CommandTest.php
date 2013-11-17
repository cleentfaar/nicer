<?php

/*
 * This file is part of the Nicer CLI package.
 *
 * (c) Cas Leentfaar <info@casleentfaar.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cleentfaar\Nicer\Tests\Command;

use \Cilex\Command;

class CommandMock extends Command\Command {}

/**
 * Command\Command test cases.
 */
class CommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Cilex\Command\Command */
    protected $fixture = null;

    /**
     * Sets up the test fixture.
     */
    public function setUp()
    {
        $this->fixture = new CommandMock('nicer:array');
    }

    /**
     * Tests the getContainer method.
     */
    public function testContainer()
    {
        $app = new \Cilex\Application('Test');
        $app->command($this->fixture);

        $this->assertSame($app, $this->fixture->getContainer());
    }

    /**
     * Tests whether the getService method correctly retrieves an element from
     * the container.
     */
    public function testGetService()
    {
        $app = new \Cilex\Application('Test');
        $app->command($this->fixture);

        $this->assertInstanceOf(
            '\Symfony\Component\Console\Application',
            $this->fixture->getService('console')
        );
    }
}
