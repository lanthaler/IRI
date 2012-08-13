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
 * The IRI test suite.
 *
 * @author Markus Lanthaler <mail@markus-lanthaler.com>
 */
class JsonLDTestSuiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parsing
     *
     * This test checks whether decomposing IRIs in their subcomponents works.
     *
     * @param string $iri           The IRI to decompose.
     * @param string|null $scheme   The scheme.
     * @param string|null $userinfo The user information.
     * @param string|null $host     The host.
     * @param string|null $port     The port.
     * @param string|null $path     The path.
     * @param string|null $query    The query component.
     * @param string|null $fragment The fragment identifier.
     *
     * @dataProvider decompositionProvider
     */
    public function testDecomposition($iri, $scheme, $userinfo, $host, $port, $path, $query, $fragment)
    {
        $test = new IRI($iri);
        $this->assertEquals($scheme, $test->getScheme(), 'Scheme of ' . $iri);
        $this->assertEquals($userinfo, $test->getUserInfo(), 'User info of ' . $iri);
        $this->assertEquals($host, $test->getHost(), 'Host of ' . $iri);
        $this->assertEquals($port, $test->getPort(), 'Port of ' . $iri);
        $this->assertEquals($path, $test->getPath(), 'Path of ' . $iri);
        $this->assertEquals($query, $test->getQuery(), 'Query component of ' . $iri);
        $this->assertEquals($fragment, $test->getFragment(), 'Fragment of ' . $iri);
        $this->assertEquals($iri, $test->__toString(), 'Recomposition of ' . $iri);
        $this->assertTrue($test->equals($iri), 'Test equality of parsed ' . $iri);
    }

    /**
     * Decomposition test cases.
     *
     * These test cases were taken from the
     * {@link http://tools.ietf.org/html/rfc3986#section-1.1.2 URI specification}
     * and from {@link http://www.ninebynine.org/Software/HaskellUtils/Network/URITestDescriptions.html}.
     *
     * @return array The decomposition test cases.
     */
    public function decompositionProvider()
    {
        return array(  //$iri, $scheme, $userinfo, $host, $port, $path, $query, $fragment
            // http://tools.ietf.org/html/rfc3986#section-1.1.2
            array('ftp://ftp.is.co.za/rfc/rfc1808.txt', 'ftp', null, 'ftp.is.co.za', null, '/rfc/rfc1808.txt', null, null),
            array('http://www.ietf.org/rfc/rfc2396.txt#frag', 'http', null, 'www.ietf.org', null, '/rfc/rfc2396.txt', null, 'frag'),
            array('ldap://[2001:db8::7]/c=GB?objectClass?one', 'ldap', null, '[2001:db8::7]', null, '/c=GB', 'objectClass?one', null),
            array('mailto:John.Doe@example.com', 'mailto', null, null, null, 'John.Doe@example.com', null, null),
            array('news:comp.infosystems.www.servers.unix', 'news', null, null, null, 'comp.infosystems.www.servers.unix', null, null),
            array('tel:+1-816-555-1212', 'tel', null, null, null, '+1-816-555-1212', null, null),
            array('telnet://192.0.2.16:80/', 'telnet', null, '192.0.2.16', '80', '/', null, null),
            array('urn:oasis:names:specification:docbook:dtd:xml:4.1.2', 'urn', null, null, null, 'oasis:names:specification:docbook:dtd:xml:4.1.2', null, null),
            // http://www.ninebynine.org/Software/HaskellUtils/Network/URITestDescriptions.html
            array('http://user:pass@example.org:99/aaa/bbb?qqq#fff', 'http', 'user:pass', 'example.org', '99', '/aaa/bbb' , 'qqq', 'fff'),
            // INVALID IRI array('http://user:pass@example.org:99aaa/bbb'),
            array('http://user:pass@example.org:99?aaa/bbb', 'http', 'user:pass', 'example.org', '99', '', 'aaa/bbb', null),
            array('http://user:pass@example.org:99#aaa/bbb', 'http', 'user:pass', 'example.org', '99' , '', null, 'aaa/bbb')
        );
    }

    /**
     * Test whether an IRI is an absolute IRI or a relative one.
     *
     * @param string $iri        The IRI to test.
     * @param bool   $isAbsolute True if the IRI is absolute, false otherwise.
     *
     * @dataProvider isAbsoluteProvider
     */
    public function testIsAbsolute($iri, $isAbsolute)
    {
        $iri = new IRI($iri);
        $this->assertEquals($isAbsolute, $iri->isAbsolute());
    }


    /**
     * Absolute/relative IRI test cases.
     *
     * These tests were taken from the
     * {@link http://tools.ietf.org/html/rfc3986#section-5.4 URI specification} and from
     * {@link http://www.ninebynine.org/Software/HaskellUtils/Network/URITestDescriptions.html}.
     *
     * @return array The absolute/relative IRI test cases.
     */
    public function isAbsoluteProvider()
    {
        return array(
            // http://tools.ietf.org/html/rfc3986#section-5.4
            array('http:g', true),
            array('g:h', true),
            array('g', false),
            array('./g', false),
            array('g/', false),
            array('/g', false),
            array('//g', false),
            array('?y', false),
            array('g?y', false),
            array('#s', false),
            array('g#s', false),
            array('g?y#s', false),
            array(';x', false),
            array('g;x', false),
            array('g;x?y#s', false),
            array('', false),
            array('.', false),
            array('./', false),
            array('..', false),
            array('../', false),
            array('../g', false),
            array('../..', false),
            array('../../', false),
            array('../../g', false),
            array('../../../g', false),
            array('../../../../g', false),
            array('/./g', false),
            array('/../g', false),
            array('g.', false),
            array('.g', false),
            array('g..', false),
            array('..g', false),
            array('./../g', false),
            array('./g/.', false),
            array('g/./h', false),
            array('g/../h', false),
            array('g;x=1/./y', false),
            array('g;x=1/../y', false),
            array('g?y/./x', false),
            array('g?y/../x', false),
            array('g#s/./x', false),
            array('g#s/../x', false),
            // http://www.ninebynine.org/Software/HaskellUtils/Network/URITestDescriptions.html
            array('http://example.org/aaa/bbb#ccc', true),
            array('mailto:local@domain.org', true),
            array('mailto:local@domain.org#frag', true),
            array('HTTP://EXAMPLE.ORG/AAA/BBB#CCC', true),
            array('http://example.org/aaa%2fbbb#ccc', true),
            array('http://example.org/aaa%2Fbbb#ccc', true),
            array('http://example.org:80/aaa/bbb#ccc', true),
            array('http://example.org:/aaa/bbb#ccc', true),
            array('http://example.org./aaa/bbb#ccc', true),
            array('http://example.123./aaa/bbb#ccc', true),
            array('http://example.org', true),
            array('http://[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:80/index.html', true),
            array('http://[1080:0:0:0:8:800:200C:417A]/index.html', true),
            array('http://[3ffe:2a00:100:7031::1]', true),
            array('http://[1080::8:800:200C:417A]/foo', true),
            array('http://[::192.9.5.5]/ipng', true),
            array('http://[::FFFF:129.144.52.38]:80/index.html', true),
            array('http://[2010:836B:4179::836B:4179]', true),
            array('http://example/Andr&#567;', true),
            array('file:///C:/DEV/Haskell/lib/HXmlToolbox-3.01/examples/', true),
            array('http://46229EFFE16A9BD60B9F1BE88B2DB047ADDED785/demo.mp3', true),
            array('//example.org/aaa/bbb#ccc', false),
            array('/aaa/bbb#ccc', false),
            array('bbb#ccc', false),
            array('#ccc', false),
            array('#', false),
            array('/', false),
            array('%2F', false),
            array('aaa%2Fbbb', false),
            array('//[2010:836B:4179::836B:4179]', false),
            array("A'C", false),
            array('A$C', false),
            array('A@C', false),
            array('"A,C"', false)
        );
    }

    /**
     * Test relative reference resolution.
     *
     * @param string $base      The base IRI.
     * @param string $reference The reference to resolve.
     * @param string $expected  The expected absolute IRI.
     *
     * @dataProvider referenceResolutionProvider
     */
    public function testReferenceResolution($base, $reference, $absolute)
    {
        $base = new IRI($base);
        $this->assertEquals($absolute, $base->resolve($reference)->__toString());
    }

    /**
     * Reference resolution test cases.
     *
     * These test cases were taken from the
     * {@link http://tools.ietf.org/html/rfc3986#section-5.4 URI specification},
     * from {@link http://www.w3.org/2004/04/uri-rel-test.html},
     * {@link http://dig.csail.mit.edu/2005/ajar/ajaw/test/uri-test-doc.html},
     * and {@link http://www.ninebynine.org/Software/HaskellUtils/Network/URITestDescriptions.html}.
     *
     * @return array The reference resolution test cases.
     */
    public function referenceResolutionProvider()
    {
        return array(  // $base, $relative, $absolute
            // http://tools.ietf.org/html/rfc3986#section-5.4
            array('http://a/b/c/d;p?q', 'g:h', 'g:h'),
            array('http://a/b/c/d;p?q', 'g', 'http://a/b/c/g'),
            array('http://a/b/c/d;p?q', './g', 'http://a/b/c/g'),
            array('http://a/b/c/d;p?q', 'g/', 'http://a/b/c/g/'),
            array('http://a/b/c/d;p?q', '/g', 'http://a/g'),
            array('http://a/b/c/d;p?q', '//g', 'http://g'),
            array('http://a/b/c/d;p?q', '?y', 'http://a/b/c/d;p?y'),
            array('http://a/b/c/d;p?q', 'g?y', 'http://a/b/c/g?y'),
            array('http://a/b/c/d;p?q', '#s', 'http://a/b/c/d;p?q#s'),
            array('http://a/b/c/d;p?q', 'g#s', 'http://a/b/c/g#s'),
            array('http://a/b/c/d;p?q', 'g?y#s', 'http://a/b/c/g?y#s'),
            array('http://a/b/c/d;p?q', ';x', 'http://a/b/c/;x'),
            array('http://a/b/c/d;p?q', 'g;x', 'http://a/b/c/g;x'),
            array('http://a/b/c/d;p?q', 'g;x?y#s', 'http://a/b/c/g;x?y#s'),
            array('http://a/b/c/d;p?q', '', 'http://a/b/c/d;p?q'),
            array('http://a/b/c/d;p?q', '.', 'http://a/b/c/'),
            array('http://a/b/c/d;p?q', './', 'http://a/b/c/'),
            array('http://a/b/c/d;p?q', '..', 'http://a/b/'),
            array('http://a/b/c/d;p?q', '../', 'http://a/b/'),
            array('http://a/b/c/d;p?q', '../g', 'http://a/b/g'),
            array('http://a/b/c/d;p?q', '../..', 'http://a/'),
            array('http://a/b/c/d;p?q', '../../', 'http://a/'),
            array('http://a/b/c/d;p?q', '../../g', 'http://a/g'),
            array('http://a/b/c/d;p?q', '../../../g', 'http://a/g'),
            array('http://a/b/c/d;p?q', '../../../../g', 'http://a/g'),
            array('http://a/b/c/d;p?q', '/./g', 'http://a/g'),
            array('http://a/b/c/d;p?q', '/../g', 'http://a/g'),
            array('http://a/b/c/d;p?q', 'g.', 'http://a/b/c/g.'),
            array('http://a/b/c/d;p?q', '.g', 'http://a/b/c/.g'),
            array('http://a/b/c/d;p?q', 'g..', 'http://a/b/c/g..'),
            array('http://a/b/c/d;p?q', '..g', 'http://a/b/c/..g'),
            array('http://a/b/c/d;p?q', './../g', 'http://a/b/g'),
            array('http://a/b/c/d;p?q', './g/.', 'http://a/b/c/g/'),
            array('http://a/b/c/d;p?q', 'g/./h', 'http://a/b/c/g/h'),
            array('http://a/b/c/d;p?q', 'g/../h', 'http://a/b/c/h'),
            array('http://a/b/c/d;p?q', 'g;x=1/./y', 'http://a/b/c/g;x=1/y'),
            array('http://a/b/c/d;p?q', 'g;x=1/../y', 'http://a/b/c/y'),
            array('http://a/b/c/d;p?q', 'g?y/./x', 'http://a/b/c/g?y/./x'),
            array('http://a/b/c/d;p?q', 'g?y/../x', 'http://a/b/c/g?y/../x'),
            array('http://a/b/c/d;p?q', 'g#s/./x', 'http://a/b/c/g#s/./x'),
            array('http://a/b/c/d;p?q', 'g#s/../x', 'http://a/b/c/g#s/../x'),
            array('http://a/b/c/d;p?q', 'http:g', 'http:g'),
            // http://www.w3.org/2004/04/uri-rel-test.html
            array('http://a/b/c/d;p?q', './g:h', 'http://a/b/c/g:h'),
            // http://dig.csail.mit.edu/2005/ajar/ajaw/test/uri-test-doc.html},
            // http://www.ninebynine.org/Software/HaskellUtils/Network/URITestDescriptions.html}.
            array('foo:xyz', 'bar:abc', 'bar:abc'),
            array('http://example/x/y/z', '../abc', 'http://example/x/abc'),
            array('http://example2/x/y/z', '//example/x/abc', 'http://example/x/abc'),
            array('http://example2/x/y/z', 'http://example/x/abc', 'http://example/x/abc'),
            array('http://ex/x/y/z', '../r', 'http://ex/x/r'),
            array('http://ex/x/y/z', '/r', 'http://ex/r'),
            array('http://ex/x/y/z', 'q/r', 'http://ex/x/y/q/r'),
            array('http://ex/x/y', 'q/r#s', 'http://ex/x/q/r#s'),
            array('http://ex/x/y', 'q/r#s/t', 'http://ex/x/q/r#s/t'),
            array('http://ex/x/y', 'ftp://ex/x/q/r', 'ftp://ex/x/q/r'),
            array('http://ex/x/y', '', 'http://ex/x/y'),
            array('http://ex/x/y/', '', 'http://ex/x/y/'),
            array('http://ex/x/y/pdq', '', 'http://ex/x/y/pdq'),
            array('http://ex/x/y/', 'z/', 'http://ex/x/y/z/'),
            array('file:/swap/test/animal.rdf', '#Animal', 'file:/swap/test/animal.rdf#Animal'),
            array('file:/e/x/y/z', '../abc', 'file:/e/x/abc'),
            array('file:/example2/x/y/z', '/example/x/abc', 'file:/example/x/abc'),
            array('file:/ex/x/y/z', '../r', 'file:/ex/x/r'),
            array('file:/ex/x/y/z', '/r', 'file:/r'),
            array('file:/ex/x/y', 'q/r', 'file:/ex/x/q/r'),
            array('file:/ex/x/y', 'q/r#s', 'file:/ex/x/q/r#s'),
            array('file:/ex/x/y', 'q/r#', 'file:/ex/x/q/r'),   // Removed trailing #
            array('file:/ex/x/y', 'q/r#s/t', 'file:/ex/x/q/r#s/t'),
            array('file:/ex/x/y', 'ftp://ex/x/q/r', 'ftp://ex/x/q/r'),
            array('file:/ex/x/y', '', 'file:/ex/x/y'),
            array('file:/ex/x/y/', '', 'file:/ex/x/y/'),
            array('file:/ex/x/y/pdq', '', 'file:/ex/x/y/pdq'),
            array('file:/ex/x/y/', 'z/', 'file:/ex/x/y/z/'),
            array('file:/devel/WWW/2000/10/swap/test/reluri-1.n3', '//meetings.example.com/cal#m1', 'file://meetings.example.com/cal#m1'),
            array('file:/home/connolly/w3ccvs/WWW/2000/10/swap/test/reluri-1.n3', '//meetings.example.com/cal#m1', 'file://meetings.example.com/cal#m1'),
            array('file:/some/dir/foo', './#blort', 'file:/some/dir/#blort'),
            array('file:/some/dir/foo', './#', 'file:/some/dir/'),   // Removed trailing #
            array('http://ex/x/y', './q:r', 'http://ex/x/q:r'),
            array('http://ex/x/y', './p=q:r', 'http://ex/x/p=q:r'),
            array('http://ex/x/y?pp/qq', '?pp/rr', 'http://ex/x/y?pp/rr'),
            array('http://ex/x/y?pp/qq', 'y/z', 'http://ex/x/y/z'),
            array('mailto:local', 'local/qual@domain.org#frag', 'mailto:local/qual@domain.org#frag'),
            array('mailto:local/qual1@domain1.org', 'more/qual2@domain2.org#frag', 'mailto:local/more/qual2@domain2.org#frag'),
            array('http://ex/x/z?q', 'y?q', 'http://ex/x/y?q'),
            array('http://ex?p', '/x/y?q', 'http://ex/x/y?q'),
            array('foo:a/b', 'c/d', 'foo:a/c/d'),
            array('foo:a/b', '/c/d', 'foo:/c/d'),
            array('foo:a/b?c#d', '', 'foo:a/b?c'),
            array('foo:a', 'b/c', 'foo:b/c'),
            array('foo:/a/y/z', '../b/c', 'foo:/a/b/c'),
// TODO Check this test            array('foo:a', './b/c', 'foo:b/c'),
            array('foo:a', '/./b/c', 'foo:/b/c'),
            array('foo://a//b/c', '../../d', 'foo://a/d'),
            array('foo:a', '.', 'foo:'),
            array('foo:a', '..', 'foo:'),
            array('http://example/x/y%2Fz', 'abc', 'http://example/x/abc'),
            array('http://example/a/x/y/z', '../../x%2Fabc', 'http://example/a/x%2Fabc'),
            array('http://example/a/x/y%2Fz', '../x%2Fabc', 'http://example/a/x%2Fabc'),
            array('http://example/x%2Fy/z', 'abc', 'http://example/x%2Fy/abc'),
            array('http://ex/x/y', 'q%3Ar', 'http://ex/x/q%3Ar'),
            array('http://example/x/y%2Fz', '/x%2Fabc', 'http://example/x%2Fabc'),
            array('http://example/x/y/z', '/x%2Fabc', 'http://example/x%2Fabc'),
            array('http://example/x/y%2Fz', '/x%2Fabc', 'http://example/x%2Fabc'),
            array('ftp://example/x/y', 'http://example/a/b/../../c', 'http://example/c'),
            array('ftp://example/x/y', 'http://example/a/b/c/../../', 'http://example/a/'),
            array('ftp://example/x/y', 'http://example/a/b/c/./', 'http://example/a/b/c/'),
            array('ftp://example/x/y', 'http://example/a/b/c/.././', 'http://example/a/b/'),
            array('ftp://example/x/y', 'http://example/a/b/c/d/../../../../e', 'http://example/e'),
            array('ftp://example/x/y', 'http://example/a/b/c/d/../.././../../e', 'http://example/e'),
            array('mailto:local1@domain1?query1', 'local2@domain2', 'mailto:local2@domain2'),
            array('mailto:local1@domain1', 'local2@domain2?query2', 'mailto:local2@domain2?query2'),
            array('mailto:local1@domain1?query1', 'local2@domain2?query2', 'mailto:local2@domain2?query2'),
            array('mailto:local@domain?query1', '?query2', 'mailto:local@domain?query2'),
            array('mailto:?query1', 'local@domain?query2', 'mailto:local@domain?query2'),
            array('mailto:local@domain?query1', '?query2', 'mailto:local@domain?query2'),
            array('foo:bar', 'http://example/a/b?c/../d', 'http://example/a/b?c/../d'),
            array('foo:bar', 'http://example/a/b#c/../d', 'http://example/a/b#c/../d'),
            array('http://example.org/base/uri', 'this', 'http://example.org/base/this'),  // Fixed absolute from http:this
            array('http://example.org/base/uri', 'http:this', 'http:this'),
            array('http:base', 'http:this', 'http:this'),
            array('f:/a', './/g', 'f://g'),
            array('f://example.org/base/a', 'b/c//d/e', 'f://example.org/base/b/c//d/e'),
            array('mid:m@example.ord/c@example.org', 'm2@example.ord/c2@example.org', 'mid:m@example.ord/m2@example.ord/c2@example.org'),
// TODO Check this test            array('file:///C:/DEV/Haskell/lib/HXmlToolbox-3.01/examples/', 'mini1.xml', 'file:///C:/DEV/Haskell/lib/HXmlToolbox-3.01/examples/mini1.xml'),
            array('foo:a/y/z', '../b/c', 'foo:a/b/c'),
            array('http://ex', '/x/y?q', 'http://ex/x/y?q'),
            array('http://ex', 'x/y?q', 'http://ex/x/y?q'),
            array('http://ex?p', '/x/y?q', 'http://ex/x/y?q'),
            array('http://ex?p', 'x/y?q', 'http://ex/x/y?q'),
            array('http://ex#f', '/x/y?q', 'http://ex/x/y?q'),
            array('http://ex#f', 'x/y?q', 'http://ex/x/y?q'),
            array('http://ex?p', '/x/y#g', 'http://ex/x/y#g'),
            array('http://ex?p', 'x/y#g', 'http://ex/x/y#g'),
            array('http://ex', '/', 'http://ex/'),
            array('http://ex', './', 'http://ex/'),
            array('http://ex', '/a/b', 'http://ex/a/b'),
            array('http://ex/a/b', './', 'http://ex/a/'),
            array('mailto:local/option@domain.org?notaquery#frag', 'more@domain', 'mailto:local/more@domain'),
            array('mailto:local/option@domain.org?notaquery#frag', '#newfrag', 'mailto:local/option@domain.org?notaquery#newfrag'),
            array('mailto:local/option@domain.org?notaquery#frag', 'l1/q1@domain', 'mailto:local/l1/q1@domain'),
            array('mailto:local1@domain1?query1', 'mailto:local2@domain2', 'mailto:local2@domain2'),
            array('mailto:local1@domain1', 'mailto:local2@domain2?query2', 'mailto:local2@domain2?query2'),
            array('mailto:local1@domain1?query1', 'mailto:local2@domain2?query2', 'mailto:local2@domain2?query2'),
            array('mailto:local@domain?query1', 'mailto:local@domain?query2', 'mailto:local@domain?query2'),
            array('mailto:?query1', 'mailto:local@domain?query2', 'mailto:local@domain?query2'),
            array('mailto:local@domain?query1', '?query2', 'mailto:local@domain?query2'),
            array('info:name/1234/../567', 'name/9876/../543', 'info:name/name/543'),
            array('info:/name/1234/../567', 'name/9876/../543', 'info:/name/name/543'),
            array('http://ex/x/y', 'q/r', 'http://ex/x/q/r'),
            array('file:/devel/WWW/2000/10/swap/test/reluri-1.n3', 'file://meetings.example.com/cal#m1', 'file://meetings.example.com/cal#m1'),
            array('file:/home/connolly/w3ccvs/WWW/2000/10/swap/test/reluri-1.n3', 'file://meetings.example.com/cal#m1', 'file://meetings.example.com/cal#m1'),
            array('http://example/x/abc.efg', './', 'http://example/x/'),
            array('http://www.w3.org/People/Berners-Lee/card.rdf', '../../2002/01/tr-automation/tr.rdf', 'http://www.w3.org/2002/01/tr-automation/tr.rdf'),
            array('http://example.com/', '.', 'http://example.com/'),
            array('http://example.com/.meta.n3', '.meta.n3', 'http://example.com/.meta.n3')
        );
    }
}
