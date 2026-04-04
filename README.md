# activecollab/cookies

A thin PSR-7 cookie library built on top of [dflydev/fig-cookies](https://github.com/dflydev/fig-cookies). It adds automatic name prefixing, optional value encryption, URL-based configuration, and secure defaults so you can work with cookies without worrying about the low-level header plumbing.

## Installation

```bash
composer require activecollab/cookies
```

## Quick Start

```php
use ActiveCollab\Cookies\Cookies;

$cookies = new Cookies();

// Read a cookie from an incoming request.
$value = $cookies->get($request, 'session_id');

// Set a cookie on both the request and the response.
[$request, $response] = $cookies->set($request, $response, 'session_id', 'abc123');

// Remove a cookie.
[$request, $response] = $cookies->remove($request, $response, 'session_id');
```

Every method accepts and returns standard PSR-7 message objects (`ServerRequestInterface`, `ResponseInterface`), so the library slots into any middleware stack.

## How It Works

The library revolves around a single entry point, the `Cookies` class (implementing `CookiesInterface`). It is responsible for three operations:

**Getting** a cookie reads the `Cookie` header from the incoming request and returns the value for the given name (after optional decryption and prefix resolution).

**Setting** a cookie applies the value to both the request object (so downstream middleware can see it immediately) and the response object (by adding a `Set-Cookie` header with all configured attributes like domain, path, expiry, security flags, etc.).

**Removing** a cookie works like setting operation, but with an empty value and an expiry date in the past, so the browser discards it.

## Configuration

All configuration is done through a fluent interface on the `Cookies` instance. Every setter returns `$this`, so calls can be chained.

### Settings Overview

| Setting     | Getter            | Setter                          | Default             | Description                            |
|-------------|-------------------|---------------------------------|---------------------|----------------------------------------|
| Domain      | `getDomain()`     | `domain(string)`                | `''` (empty)        | Cookie domain attribute.               |
| Path        | `getPath()`       | `path(string)`                  | `'/'`               | Cookie path attribute.                 |
| Secure      | `getSecure()`     | `secure(bool)`                  | `true`              | Send cookie only over HTTPS.           |
| HttpOnly    | `getHttpOnly()`   | `httpOnly(bool)`                | `true`              | Hide cookie from JavaScript.           |
| SameSite    | `getSameSite()`   | `sameSite(SameSite)`            | `Lax`               | Cross-site request policy.             |
| Default TTL | `getDefaultTtl()` | `defaultTtl(int)`               | `1209600` (14 days) | Time-to-live in seconds.               |
| Prefix      | `getPrefix()`     | `prefix(string)`                | `''` (empty)        | String prepended to every cookie name. |
| Encryptor   | `getEncryptor()`  | `encryptor(EncryptorInterface)` | `null`              | Encrypt/decrypt cookie values.         |

### Secure, HttpOnly, and the SameSite

The library ships with modern security defaults out of the box:

```php
$cookies = new Cookies();

$cookies->getSecure();   // true — cookies are HTTPS-only
$cookies->getHttpOnly(); // true — cookies are hidden from document.cookie
$cookies->getSameSite(); // Lax — protects against CSRF while allowing top-level navigations
```

Override any of them when the use case requires it:

```php
use Dflydev\FigCookies\Modifier\SameSite;

// A cookie that JavaScript needs to read (e.g., a theme preference).
$cookies->httpOnly(false);

// A cookie that must be sent on cross-origin requests (e.g., an OAuth flow).
// SameSite=None requires Secure=true — the library does not enforce this
// automatically, so make sure both are set.
$cookies->sameSite(SameSite::none());
$cookies->secure(true);

// A cookie with the strictest same-site policy.
$cookies->sameSite(SameSite::strict());
```

### Per-Cookie Settings

The `set()` method accepts an optional `$settings` array as its fifth argument. These options override global defaults for that single cookie only:

```php
[$request, $response] = $cookies->set(
    $request,
    $response,
    'name',
    'value',
    $settings,
);
```

| Key         | Type       | Default                              | Description                                                                                                                                  |
|-------------|------------|--------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------|
| `ttl`       | `int`      | Value of `getDefaultTtl()` (14 days) | Time-to-live in seconds. The cookie's `Expires` attribute is computed as current timestamp plus this value.                                  |
| `http_only` | `bool`     | Value of `getHttpOnly()` (`true`)    | Whether the cookie should be hidden from JavaScript. Set to `false` for cookies that client-side code needs to read.                         |
| `same_site` | `SameSite` | Value of `getSameSite()` (`Lax`)     | Cross-site request policy for this cookie. Accepts `SameSite::lax()`, `SameSite::strict()`, or `SameSite::none()`.                           |
| `encrypt`   | `bool`     | `true`                               | Whether to encrypt the value before writing. Only relevant when an encryptor is configured. Set to `false` to store the value in plain text. |

All other cookie attributes (domain, path, and secure) are always taken from the global `Cookies` configuration and cannot be overridden per cookie.

Example — a short-lived, JS-accessible, unencrypted preference cookie:

```php
[$request, $response] = $cookies->set(
    $request,
    $response,
    'theme',
    'dark',
    [
        'ttl' => 31536000,     // 1 year
        'http_only' => false,  // readable by document.cookie
        'encrypt' => false,    // store as plain text
    ],
);
```

Example — a CSRF token cookie with a stricter same-site policy than the global default:

```php
use Dflydev\FigCookies\Modifier\SameSite;

[$request, $response] = $cookies->set(
    $request,
    $response,
    'csrf_token',
    $token,
    ['same_site' => SameSite::strict()],
);
```

### Configure From URL

The most convenient way to configure domain, path, secure, and prefix all at once is from your application's URL:

```php
$cookies->configureFromUrl('https://app.example.com/projects');

$cookies->getDomain();  // 'app.example.com'
$cookies->getPath();    // '/projects'
$cookies->getSecure();  // true (scheme is https)
$cookies->getPrefix();  // md5('https://app.example.com/projects')
```

An `http://` URL will set `secure` to `false`. The prefix is only auto-generated when one has not already been set, so calling `prefix()` before `configureFromUrl()` preserves your choice.

### Cookie Name Prefixing

Prefixing avoids collisions when multiple applications share the same domain:

```php
$cookies = (new Cookies())->prefix('myapp_');

// Internally stores and reads a cookie named "myapp_session_id".
[$request, $response] = $cookies->set($request, $response, 'session_id', 'abc');
$value = $cookies->get($request, 'session_id'); // reads "myapp_session_id"
```

### Encryption

When an encryptor is provided, cookie values are automatically encrypted on `set()` and decrypted on `get()`. The encryptor must implement `ActiveCollab\Encryptor\EncryptorInterface` from the `activecollab/utils` package.

```php
use ActiveCollab\Encryptor\Encryptor;

$cookies = (new Cookies())->encryptor(
    new Encryptor('your-32-character-secret-key-here'),
);

// Value is encrypted before being written to the Set-Cookie header.
[$request, $response] = $cookies->set($request, $response, 'token', 'sensitive-value');

// Value is decrypted transparently when read.
$cookies->get($request, 'token'); // 'sensitive-value'
```

To skip encryption for a specific cookie, pass `'encrypt' => false` in settings:

```php
[$request, $response] = $cookies->set(
    $request,
    $response,
    'public_pref',
    'dark-mode',
    ['encrypt' => false],
);

// Read without decryption.
$cookies->get($request, 'public_pref', null, decrypt: false);
```

### TTL and Expiry

The default time-to-live is 14 days. Change it globally or per cookie:

```php
// Global: all cookies expire in 1 hour.
$cookies->defaultTtl(3600);

// Per cookie: this one expires in 30 minutes.
[$request, $response] = $cookies->set(
    $request,
    $response,
    'short_lived',
    'value',
    ['ttl' => 1800],
);
```
