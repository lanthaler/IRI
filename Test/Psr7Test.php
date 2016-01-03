<?php

/*
 * (c) Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ML\IRI\Test;

use ML\IRI\IRI;

/**
 * Tests the methods of the PSR-7 interface that aren't covered by other tests.
 *
 * @author Markus Lanthaler <mail@markus-lanthaler.com>
 */
class Psr7Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the {@code withScheme()} method.
     */
    public function testWithScheme()
    {
        $original = new IRI('http://example.com:1234/test?query#frag');
        $modified = $original->withScheme('https');

        $this->assertEquals('https', $modified->getScheme(), 'Scheme');
        $this->assertEquals('https://example.com:1234/test?query#frag', $modified->__toString(), 'Full IRI');
        $this->assertNotSame($original, $modified, 'Instances are immutable');
    }

    /**
     * Tests the {@code withUserInfo()} method.
     */
    public function testWithUserInfo()
    {
        $original = new IRI('http://example.com:1234/test?query#frag');
        $modified = $original->withUserInfo('user');

        $this->assertEquals('user', $modified->getUserInfo(), 'User info');
        $this->assertEquals('http://user@example.com:1234/test?query#frag', $modified->__toString(), 'Full IRI');
        $this->assertNotSame($original, $modified, 'Instances are immutable');

        $modified = $original->withUserInfo('user', 'pass');

        $this->assertEquals('user:pass', $modified->getUserInfo(), 'User info');
        $this->assertEquals('http://user:pass@example.com:1234/test?query#frag', $modified->__toString(), 'Full IRI');
        $this->assertNotSame($original, $modified, 'Instances are immutable');

    }

    /**
     * Tests the {@code withHost()} method.
     */
    public function testWithHost()
    {
        $original = new IRI('http://example.com:1234/test?query#frag');
        $modified = $original->withHost('example.org');

        $this->assertEquals('example.org', $modified->getHost(), 'Host');
        $this->assertEquals('http://example.org:1234/test?query#frag', $modified->__toString(), 'Full IRI');
        $this->assertNotSame($original, $modified, 'Instances are immutable');
    }

    /**
     * Tests the {@code withPort()} method.
     */
    public function testWithPort()
    {
        $original = new IRI('http://example.com:1234/test?query#frag');
        $modified = $original->withPort(80);

        $this->assertEquals('80', $modified->getPort(), 'Port');
        $this->assertEquals('http://example.com:80/test?query#frag', $modified->__toString(), 'Full IRI');
        $this->assertNotSame($original, $modified, 'Instances are immutable');
    }

    /**
     * Tests the {@code withPath()} method.
     */
    public function testWithPath()
    {
        $original = new IRI('http://example.com:1234/test?query#frag');
        $modified = $original->withPath('/new/path/');

        $this->assertEquals('/new/path/', $modified->getPath(), 'Path');
        $this->assertEquals('http://example.com:1234/new/path/?query#frag', $modified->__toString(), 'Full IRI');
        $this->assertNotSame($original, $modified, 'Instances are immutable');
    }

    /**
     * Tests the {@code withQuery()} method.
     */
    public function testWithQuery()
    {
        $original = new IRI('http://example.com:1234/test?query#frag');
        $modified = $original->withQuery('query=changed');

        $this->assertEquals('query=changed', $modified->getQuery(), 'Query');
        $this->assertEquals('http://example.com:1234/test?query=changed#frag', $modified->__toString(), 'Full IRI');
        $this->assertNotSame($original, $modified, 'Instances are immutable');
    }

    /**
     * Tests the {@code withFragment()} method.
     */
    public function testWithFragment()
    {
        $original = new IRI('http://example.com:1234/test?query#frag');
        $modified = $original->withFragment('new-fragment');

        $this->assertEquals('new-fragment', $modified->getFragment(), 'Fragment');
        $this->assertEquals('http://example.com:1234/test?query#new-fragment', $modified->__toString(), 'Full IRI');
        $this->assertNotSame($original, $modified, 'Instances are immutable');
    }
}
