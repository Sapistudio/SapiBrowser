<?php
namespace SapiStudio\Http;

/** fork after wa72/url*/

class Url
{
    const PATH_SEGMENT_SEPARATOR    = '/';
    const WRITE_FLAG_AS_IS          = 0;
    const WRITE_FLAG_OMIT_SCHEME    = 1;

    protected $original_url;
    protected $scheme;
    protected $user;
    protected $pass;
    protected $host;
    protected $port;
    protected $path;
    protected $query;
    protected $fragment;

    protected $query_array = [];

    /** Url::__construct()*/
    public function __construct($url)
    {
        $this->original_url = trim($url);
        if ($this->is_protocol_relative()) {
            $url = 'http:'.$url;
        }
        $urlo = parse_url($url);
        if (isset($urlo['scheme']) && !$this->is_protocol_relative()) {
            $this->scheme = strtolower($urlo['scheme']);
        }
        if (isset($urlo['user'])) $this->user = $urlo['user'];
        if (isset($urlo['pass'])) $this->pass = $urlo['pass'];
        if (isset($urlo['host'])) $this->host = strtolower($urlo['host']);
        if (isset($urlo['port'])) $this->port = intval($urlo['port']);
        if (isset($urlo['path'])) $this->path = static::normalizePath($urlo['path']);
        if (isset($urlo['query'])) $this->query = $urlo['query'];
        if ($this->query != '') parse_str($this->query, $this->query_array);
        if (isset($urlo['fragment'])) $this->fragment = $urlo['fragment'];
    }

    /** Url::is_url() */
    public function is_url()
    {
        return ($this->scheme == '' || $this->scheme == 'http' || $this->scheme == 'https' || $this->scheme == 'ftp' || $this->scheme == 'ftps' || $this->scheme == 'file');
    }

    /** Url::is_local()*/
    public function is_local()
    {
        return (substr($this->original_url, 0, 1) == '#');
    }

    /** Url::is_relative()*/
    public function is_relative()
    {
        return ($this->scheme == '' && $this->host == '' && substr($this->path, 0, 1) != '/');
    }

    /** Url::is_host_relative()*/
    public function is_host_relative()
    {
        return ($this->scheme == '' && $this->host == '' && substr($this->path, 0, 1) == '/');
    }

    /** Url::is_absolute() */
    public function is_absolute()
    {
        return ($this->scheme != '');
    }

    /** Url::is_protocol_relative()*/
    public function is_protocol_relative()
    {
        return (substr($this->original_url, 0, 2) == '//');
    }

    /** Url::__toString()*/
    public function __toString() {
        return $this->write();
    }

    /** Url::write() */
    public function write($write_flags = self::WRITE_FLAG_AS_IS)
    {
        $port = $this->getPort();
        $show_scheme = $this->scheme && (!($write_flags & self::WRITE_FLAG_OMIT_SCHEME));
        $url = ($show_scheme ? $this->scheme . ':' : '');
        $url .= '//';
        $url .= $this->host . ($port ? ':' . $port : '');
        $url .= ($this->path ? $this->path : '');
        $url .= ($this->query ? '?' . $this->query : '');
        $url .= ($this->fragment ? '#' . $this->fragment : '');
        return $url;
    }

    /**  Url::setFragment()*/
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;
        return $this;
    }

    /** Url::getFragment()*/
    public function getFragment()
    {
        return $this->fragment;
    }

    /** Url::setHost()*/
    public function setHost($host)
    {
        $this->host = strtolower($host);
        return $this;
    }

    /** Url::getHost()*/
    public function getHost()
    {
        return $this->host;
    }

    /** Url::setPass() */
    public function setPass($pass)
    {
        $this->pass = $pass;
        return $this;
    }

    /** Url::getPass()*/
    public function getPass()
    {
        return $this->pass;
    }

    /** Url::setPath()*/
    public function setPath($path)
    {
        $this->path = static::normalizePath($path);
        return $this;
    }

    /** Url::getPath()*/
    public function getPath()
    {
        return $this->path;
    }

    /** Url::setPort()*/
    public function setPort($port)
    {
        $this->port = ($port) ? intval($port) : null;
    }

    /** Url::getPort()*/
    public function getPort()
    {
        $port = $this->port;
        $default_ports = ['http' => 80,'https' => 443,'ftp' => 21];
        foreach ($default_ports as $scheme => $dp) {
            if ($this->scheme == $scheme && $port == $dp) {
                $port = null;
            }
        }
        return $port;
    }

    /** Url::setQuery()*/
    public function setQuery($query)
    {
        $this->query = $query;
        parse_str($this->query, $this->query_array);
        return $this;
    }

    /** Url::getQuery() */
    public function getQuery()
    {
        return $this->query;
    }

    /** Url::setScheme() */
    public function setScheme($scheme)
    {
        $this->scheme = strtolower($scheme);
        return $this;
    }

    /** Url::getScheme()*/
    public function getScheme()
    {
        return $this->scheme;
    }

    /** Url::setUser() */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /** Url::getUser() */
    public function getUser()
    {
        return $this->user;
    }

    /** Url::getFilename() */
    public function getFilename()
    {
        return static::filename($this->path);
    }

    /** Url::getDirname() */
    public function getDirname()
    {
        return static::dirname($this->path);
    }

    /** Url::appendPathSegment()*/
    public function appendPathSegment($segment)
    {
        if (substr($this->path, -1) != static::PATH_SEGMENT_SEPARATOR) $this->path .= static::PATH_SEGMENT_SEPARATOR;
        if (substr($segment, 0, 1) == static::PATH_SEGMENT_SEPARATOR) $segment = substr($segment, 1);
        $this->path .= $segment;
        return $this;
    }

    /** Url::hasQueryParameter()*/
    public function hasQueryParameter($name)
    {
        return isset($this->query_array[$name]);
    }

    /** Url::getQueryParameter()*/
    public function getQueryParameter($name)
    {
        return (isset($this->query_array[$name])) ? $this->query_array[$name] : null;
    }

    /** Url::setQueryParameter()*/
    public function setQueryParameter($name, $value)
    {
        $this->query_array[$name] = $value;
        $this->query = http_build_query($this->query_array);
        return $this;
    }

    /** Url::setQueryFromArray()*/
    public function setQueryFromArray(array $query_array)
    {
        $this->query_array = $query_array;
        $this->query = http_build_query($this->query_array);
        return $this;
    }

    /** Url::getQueryArray()*/
    public function getQueryArray()
    {
        return $this->query_array;
    }

    /** Url::makeAbsolute()*/
    public function makeAbsolute($baseurl = null) {
        if (!$baseurl) return $this;
        if (!$baseurl instanceof Url) $baseurl = new static($baseurl);
        if ($this->is_url() && ($this->is_relative() || $this->is_host_relative() || $this->is_protocol_relative()) && $baseurl instanceof Url) {
            if (!$this->host) $this->host = $baseurl->getHost();
            $this->scheme = $baseurl->getScheme();
            $this->user = $baseurl->getUser();
            $this->pass = $baseurl->getPass();
            $this->port = $baseurl->getPort();
            $this->path = static::buildAbsolutePath($this->path, $baseurl->getPath());
        }
        return $this;
    }

    /** Url::buildAbsolutePath() */
    static public function buildAbsolutePath($relative_path, $basepath) {
        if (strpos($relative_path, static::PATH_SEGMENT_SEPARATOR) === 0) {
            return static::normalizePath($relative_path);
        }
        $basedir = static::dirname($basepath);
        if ($basedir == '.' || $basedir == static::PATH_SEGMENT_SEPARATOR || $basedir == '\\' || $basedir == DIRECTORY_SEPARATOR) $basedir = '';
        return static::normalizePath($basedir . self::PATH_SEGMENT_SEPARATOR . $relative_path);
    }

    /** Url::normalizePath()*/
    static public function normalizePath($path)
    {
        $path = preg_replace('|^\./|', '', preg_replace('|/\./|', '/', $path));    // entferne ./ am Anfang
        $i = 0;
        while (preg_match('|[^/]+/\.{2}/|', $path) && $i < 10) {
            $path = preg_replace_callback('|([^/]+)(/\.{2}/)|', function($matches){
                return ($matches[1] == '..' ? $matches[0] : '');
            }, $path);
            $i++;
        }
        return $path;
    }

    /** Url::filename()*/
    static public function filename($path)
    {
        return (substr($path, -1) == self::PATH_SEGMENT_SEPARATOR) ? '' : basename($path);
    }

    /** Url::dirname() */
    static public function dirname($path)
    {
        if (substr($path, -1) == self::PATH_SEGMENT_SEPARATOR) 
            return substr($path, 0, -1);
        else {
            $d = dirname($path);
            if ($d == DIRECTORY_SEPARATOR) $d = self::PATH_SEGMENT_SEPARATOR;
            return $d;
        }
    }

    /** Url::parse() */
    static public function parse($url)
    {
        return new static($url);
    }
}