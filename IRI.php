<?php

/*
 * (c) Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ML\IRI;

//use ML\JsonLD\Exception\ParseException;

/**
 * IRI represents an IRI as per RFC3987.
 *
 * @author Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * @see http://tools.ietf.org/html/rfc3987 RFC3987
 */
class IRI
{
    /**
     * The scheme.
     *
     * @var string|null
     */
    private $scheme = null;

    /**
     * The user information.
     *
     * @var string|null
     */
    private $userinfo = null;

    /**
     * The host.
     *
     * @var string|null
     */
    private $host = null;

    /**
     * The port.
     *
     * @var string|null
     */
    private $port = null;

    /**
     * The path.
     *
     * @var string
     */
    private $path = '';

    /**
     * The query component.
     *
     * @var string|null
     */
    private $query = null;

    /**
     * The fragment identifier.
     *
     * @var string|null
     */
    private $fragment = null;


    /**
     * Constructor
     *
     * @param string $iri The IRI.
     *
     * @throws \InvalidArgumentException If an invalid IRI is passed.
     *
     * @api
     */
    public function __construct($iri)
    {
        $this->parse($iri);
    }

    /**
     * Get the scheme.
     *
     * @return string|null Returns the scheme or null if not set.
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Get the authority.
     *
     * @return string|null Returns the authority or null if not set.
     */
    public function getAuthority()
    {
        if ($this->host) {
            $authority = '//';
            if ($this->userinfo) {
                $authority .= $this->userinfo . '@';
            }
            $authority .= $this->host;
            if ($this->port) {
                $authority .= ':' . $this->port;
            }

            return $authority;
        }

        return null;
    }

    /**
     * Get the user information.
     *
     * @return string|null Returns the user information or null if not set.
     */
    public function getUserInfo()
    {
        return $this->userinfo;
    }

    /**
     * Get the host.
     *
     * @return string|null Returns the host or null if not set.
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get the port.
     *
     * @return string|null Returns the port or null if not set.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get the path.
     *
     * @return string Returns the path which might be empty.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get the query component.
     *
     * @return string|null Returns the query component or null if not set.
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get the fragment identifier.
     *
     * @return string|null Returns the fragment identifier or null if not set.
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Find out whether the IRI is absolute.
     *
     * @return bool Returns true if the IRI is absolute, false otherwise.
     *
     * @api
     */
    public function isAbsolute()
    {
        return (null !== $this->scheme);
    }

    /**
     * Check whether the passed IRI is equal.
     *
     * @param IRI|string $iri IRI to compare to this instance.
     *
     * @return bool Returns true if the two IRIs are equal, false otherwise.
     *
     * @api
     */
    public function equals($iri)
    {
        // Make sure both instances are strings
        return ($this->__toString() === (string)$iri);
    }

    /**
     * @see http://tools.ietf.org/html/rfc3986#section-5.2
     *
     * @param IRI|string $reference The (relative) reference that should be
     *                              resolved against this IRI.
     *
     * @return IRI The resolved IRI.
     *
     * @throws \InvalidArgumentException If an invalid IRI is passed.
     *
     * @api
     */
    public function resolve($reference)
    {
        if (is_string($reference)) {
            $reference = new IRI($reference);
        } elseif (false === ($reference instanceof IRI)) {
            throw new \InvalidArgumentException(sprintf(
                'Expecting a string or IRI, got %s',
                (is_object($reference) ? get_class($reference) : gettype($reference))
            ));
        }

        $scheme = null;
        $authority = null;
        $path = '';
        $query = null;
        $fragment = null;

        // The Transform References algorithm as specified by RFC3986
        // see: http://tools.ietf.org/html/rfc3986#section-5.2.2
        if ($reference->scheme) {
            $scheme = $reference->scheme;
            $authority = $reference->getAuthority();
            $path = self::removeDotSegments($reference->path);
            $query = $reference->query;
        } else {
            if ($reference->getAuthority()) {
                $authority = $reference->getAuthority();
                $path = self::removeDotSegments($reference->path);
                $query = $reference->query;
            } else {
                if (0 === strlen($reference->path)) {
                    $path = $this->path;
                    if ($reference->query) {
                        $query = $reference->query;
                    } else {
                        $query = $this->query;
                    }
                } else {
                    if ('/' === $reference->path[0]) {
                        $path = self::removeDotSegments($reference->path);
                    } else {
                        // T.path = merge(Base.path, R.path);
                        if ($this->getAuthority() && ('' === $this->path)) {
                            $path = '/' . $reference->path;
                        } else {
                            if (false !== ($end = strrpos($this->path, '/'))) {
                                $path = substr($this->path, 0, $end + 1);
                            }
                            $path .= $reference->path;
                        }
                        $path = self::removeDotSegments($path);
                    }
                    $query = $reference->query;
                }

                $authority = $this->getAuthority();
            }
            $scheme = $this->scheme;
        }

        $fragment = $reference->fragment;


        // The Component Recomposition algorithm as specified by RFC3986
        // see: http://tools.ietf.org/html/rfc3986#section-5.3
        $result = '';

        if ($scheme) {
            $result = $scheme . ':';
        }

        if ($authority) {
            $result .= '//' . $authority;
        }

        $result .= $path;

        if ($query) {
            $result .= '?' . $query;
        }

        if ($fragment) {
            $result .= '#' . $fragment;
        }

        return new IRI($result);
    }

    /**
     * Returns the string representation of the IRI object.
     *
     * @return A string representation of this IRI instance.
     *
     * @api
     */
    public function __toString()
    {
        $result = '';

        if ($this->scheme) {
            $result .= $this->scheme . ':';
        }

        if ($this->host) {
            $result .= '//';
            if ($this->userinfo) {
                $result .= $this->userinfo . '@';
            }
            $result .= $this->host;
            if ($this->port) {
                $result .= ':' . $this->port;
            }
        }

        $result .= $this->path;

        if ($this->query) {
            $result .= '?' . $this->query;
        }

        if ($this->fragment) {
            $result .= '#' . $this->fragment;
        }

        return $result;
    }

    /**
     * Parse an IRI into it's components.
     *
     * This is done according to
     * {@link http://tools.ietf.org/html/rfc3986#section-3.1 RFC3986}.
     *
     * @param string $iri The IRI to parse.
     *
     * @return IRI The parsed IRI.
     *
     * @throws \InvalidArgumentException If an invalid IRI is passed.
     */
    protected function parse($iri)
    {
        if (false === is_string($iri)) {
            throw new \InvalidArgumentException(sprintf(
                'Expecting a string, got %s',
                (is_object($iri) ? get_class($iri) : gettype($iri))
            ));
        }

        // Parse IRI by using the regular expression as specified by
        // http://tools.ietf.org/html/rfc3986#appendix-B
        // The scheme part was modified to only return valid schemes
        $regex = '|^((?P<scheme>[A-Za-z][A-Za-z0-9\.\+\-]*):)?' .
                    '(//(?P<authority>[^/?#]*))?(?P<path>[^?#]*)' .
                    '(\?(?P<query>[^#]*))?(#(?P<fragment>.*))?|';
        preg_match($regex, $iri, $match);

        // Extract scheme
        if (false === empty($match['scheme'])) {
            $this->scheme = $match['scheme'];
        }

        // Parse authority (http://tools.ietf.org/html/rfc3986#section-3.2)
        if (false === empty($match['authority'])) {
            $authority = $match['authority'];

            // Split authority into userinfo and host
            // (use last @ to ignore unescaped @ symbols)
            if (false !== ($pos = strrpos($authority, '@'))) {
                $this->userinfo = substr($authority, 0, $pos);
                $authority = substr($authority, $pos + 1);
            }

            // Split authority into host and port
            $hostEnd = 0;
            if (('[' === $authority[0]) && (false !== ($pos = strpos($authority, ']')))) {
                $hostEnd = $pos;
            }

            if ((false !== ($pos = strrpos($authority, ':'))) && ($pos > $hostEnd)) {
                $this->host = substr($authority, 0, $pos);
                $authority = substr($authority, $pos + 1);

                if (false === empty($authority)) {
                    $this->port = $authority;
                }
            } else
            {
                $this->host = $authority;
            }
        }

        // Extract path (http://tools.ietf.org/html/rfc3986#section-3.3)
        // The path is always present but might be empty
        $this->path = $match['path'];

        // Extract query (http://tools.ietf.org/html/rfc3986#section-3.4)
        if (false === empty($match['query'])) {
            $this->query = $match['query'];
        }

        // Extract fragment (http://tools.ietf.org/html/rfc3986#section-3.5)
        if (false === empty($match['fragment'])) {
            $this->fragment = $match['fragment'];
        }
    }

    /**
     * Remove dot-segments
     *
     * This method removes the special "." and ".." complete path segments
     * from an IRI.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-5.2.4
     *
     * @param string $input The IRI from which dot segments should be removed.
     *
     * @return string The IRI with all dot-segments removed.
     */
    private static function removeDotSegments($input)
    {
        $output = '';

        while (strlen($input) > 0) {
            if (('../' === substr($input, 0, 3)) || ('./' === substr($input, 0, 2))) {
                $input = substr($input, strpos($input, '/'));
            } elseif($input == '/.') {
                $input = '/';
            } elseif ('/./' === substr($input, 0, 3)) {
                $input = substr($input, 2);
            } elseif ('/.' === $input) {
                $input = '/';
            } elseif (('/../' === substr($input, 0, 4)) || ('/..' === $input)) {
                if($input == '/..') {
                    $input = '/';
                } else {
                    $input = substr($input, 3);
                }

                if(false !== ($end = strrpos($output, '/'))) {
                    $output = substr($output, 0, $end);
                } else {
                    $output = '';
                }
            } elseif (('..' === $input) || ('.' === $input)) {
                $input = '';
            } else {
                if(false === ($end = strpos($input, '/', 1))) {
                    $output .= $input;
                    $input = '';
                } else {
                    $output .= substr($input, 0, $end);
                    $input = substr($input, $end);
                }
            }
        }
        return $output;
    }
}
