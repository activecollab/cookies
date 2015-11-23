<?php

namespace ActiveCollab\Cookies;

use ActiveCollab\Cookies\Adapter\AdapterInterface;
use ActiveCollab\Cookies\Encryptor\EncryptorInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @package ActiveCollab\Cookies
 */
class Cookies implements CookiesInterface
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(ServerRequestInterface $request, $name)
    {
        return $this->adapter->exists($request, $this->getPrefixedName($name));
    }

    /**
     * {@inheritdoc}
     */
    public function get(ServerRequestInterface $request, $name, $default = null)
    {
        $value = $this->adapter->get($request, $this->getPrefixedName($name), $default);

        if ($this->encryptor) {
            $value = $this->encryptor->decrypt($value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function set(ServerRequestInterface $request, ResponseInterface $response, $name, $value, array $settings = [], $ttl = null, $http_only = true)
    {
        $settings['domain'] = $this->getDomain();
        $settings['path'] = $this->getPath();
        $settings['secure'] = $this->getSecure();

        if (empty($settings['ttl'])) {
            $settings['ttl'] = time();
        } else {
            $settings['ttl'] = time() + $settings['ttl'];
        }

        if (empty($settings['http_only'])) {
            $settings['http_only'] = false;
        }

        if ($this->encryptor) {
            $value = $this->encryptor->encrypt($value);
        }

        return $this->adapter->set($request, $response, $this->getPrefixedName($name), $value, $settings);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ServerRequestInterface $request, ResponseInterface $response, $name)
    {
        return $this->adapter->remove($request, $response, $this->getPrefixedName($name));
    }

    /**
     * {@inheritdoc}
     */
    private function getPrefixedName($name)
    {
        return $this->getPrefix() . $name;
    }

    // ---------------------------------------------------
    //  Configuration
    // ---------------------------------------------------

    /**
     * Default TTL (14 days)
     *
     * @var integer
     */
    private $default_ttl = 1209600;

    /**
     * @return integer
     */
    public function getDefaultTtl()
    {
        return $this->default_ttl;
    }

    /**
     * Set default cookie TTL (time to live)
     *
     * @param  integer $value
     * @return $this
     */
    public function &defaultTtl($value)
    {
        $this->default_ttl = $value;

        return $this;
    }

    /**
     * @var string
     */
    private $domain;

    /**
     * {@inheritdoc}
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * {@inheritdoc}
     */
    public function &domain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @var string
     */
    private $path = '/';

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function &path($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @var bool
     */
    private $secure = true;

    /**
     * {@inheritdoc}
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * {@inheritdoc}
     */
    public function &secure($secure)
    {
        $this->secure = (boolean) $secure;

        return $this;
    }

    /**
     * @var string
     */
    private $prefix;

    /**
     * {@inheritdoc}
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function &prefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function &configureFromUrl($url)
    {
        $parts = parse_url($url);

        if (!empty($parts['scheme'])) {
            $this->secure(strtolower($parts['scheme']) === 'https');
        }

        $this->domain($parts['host']);

        if (empty($parts['path'])) {
            if ($this->getPath() != '/') {
                $this->path('/');
            }
        } else {
            $this->path('/' . trim($parts['path'], '/'));
        }

        if (empty($this->getPrefix())) {
            $this->prefix(md5($url));
        }

        return $this;
    }

    /**
     * @var EncryptorInterface|null
     */
    private $encryptor;

    /**
     * @return EncryptorInterface|null
     */
    public function getEncryptor()
    {
        return $this->encryptor;
    }

    /**
     * {@inheritdoc}
     */
    public function &encryptor(EncryptorInterface $encryptor = null)
    {
        $this->encryptor = $encryptor;

        return $this;
    }
}