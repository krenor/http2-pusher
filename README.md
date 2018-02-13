## Cache aware HTTP/2 pushing for Laravel

[![Packagist][icon-version]][link-version]
[![Travis][icon-travis]][link-travis]
[![Quality][icon-code-quality]][link-code-quality]
[![Coverage][icon-code-coverage]][link-code-coverage]
[![Dependencies][icon-dependencies]][link-dependencies]
[![Downloads][icon-downloads]][link-downloads]
[![License][icon-license]][link-license]

HTTP/2 is a great advancement for the HTTP protocol, allowing multiple assets to be streamed over a single TCP connection. This reduces the need for “optimisation practices” such as domain sharding, image sprites, etc. There is one really cool feature of HTTP/2 however which can greatly speed up the render time of your website, and that is server push. Server push allows you to send your assets along with the HTML payload before the browser even knows it needs those assets. [(Source)](https://iwader.co.uk/post/using-http2-server-push)

Without *cache digests* there is no clear-cut performance win for HTTP/2 Server Push over HTTP/1 Asset Bundling. *Cache digest* is a [specification](https://github.com/httpwg/http-extensions#cache-digest) currently under discussion at the IETF HTTP Working Group. [(Source)](https://calendar.perfplanet.com/2016/cache-digests-http2-server-push)

This package aims to create a cache aware mechanism for HTTP/2 Server Push until *cache digest* is available. It helps to push exactly what is needed; no more, to waste bandwith and no less, which would result in round trip latency.

## Installation

You can install the package via composer:

```shell
composer require krenor/http2-pusher
```

Laravel 5.5 uses [Package Auto-Discovery](https://laravel-news.com/package-auto-discovery), so it doesn't require you to manually add the ServiceProvider to your providers array configuration.

## Configuration

You can configure three things
* `cookie`
  * `name` *(default: `h2_cache-digest`)*
  * `duration` Requires a valid `strtotime` value *(default: `60 days`)*
* `global_pushes` Assets you want to be pushed for **every** page load

## Usage

* When you route a request through the `ServerPush` middleware, the response is scanned (unless its a `RedirectResponse` or either a `json` or `ajax` request) for any assets that can be pushed.  
* Alternatively you can use the `pushes()` method on the `Response` class, which extends the default `\Illuminate\Http\Response`, provided by this package's ServiceProvider. 
* Using the `response()` helper works as fine, however hinting of the `pushes()` method will **not** be available.  

Both methods will add a `Link` header and a Cookie to the response with all the assets found and the configured via `global_pushes`. On subsequent requests the cookie will be scanned with its already pushed resources. If any new resources are available or if the pushed resources have changed the `Link` header and the Cookie will be extended to include these. 

**Note**: Only [these extensions](https://github.com/krenor/http2-pusher/blob/master/src/Builder.php#L30) are currently supported.

This isn't strictly "cache-aware" in the sense that the server knows for sure if the asset is cached on the client side, but the logic follows. If you don't have the luxury of being able to use a web server like [H2O](https://h2o.examp1e.net/configure/http2_directives.html#http2-casper) or the [H2PushDiarySize directive](https://httpd.apache.org/docs/2.4/mod/mod_http2.html#h2pushdiarysize) for Apache's `mod_http2` module, this solution may work well enough for your purposes.

## Contributing

Please see [CONTRIBUTING](https://github.com/krenor/http2-pusher/blob/master/CONTRIBUTING.md) for more information.

## Licence

The MIT License.  Please see [LICENSE](https://github.com/krenor/http2-pusher/blob/master/CONTRIBUTING.md) for more information.

[icon-version]: https://img.shields.io/packagist/v/krenor/http2-pusher.svg?style=flat-square
[icon-travis]: https://img.shields.io/travis/krenor/http2-pusher.svg?style=flat-square
[icon-code-quality]: https://img.shields.io/scrutinizer/g/krenor/http2-pusher.svg?style=flat-square
[icon-code-coverage]: https://img.shields.io/scrutinizer/coverage/g/krenor/http2-pusher.svg?style=flat-square
[icon-dependencies]: https://img.shields.io/gemnasium/krenor/http2-pusher.svg?style=flat-square
[icon-downloads]: https://img.shields.io/packagist/dt/krenor/http2-pusher.svg?style=flat-square
[icon-license]: https://img.shields.io/github/license/krenor/http2-pusher.svg?style=flat-square

[link-version]: https://packagist.org/packages/krenor/http2-pusher
[link-travis]: http://travis-ci.org/krenor/http2-pusher
[link-code-quality]: https://scrutinizer-ci.com/g/krenor/http2-pusher
[link-code-coverage]: https://scrutinizer-ci.com/g/krenor/http2-pusher
[link-dependencies]: https://gemnasium.com/krenor/http2-pusher
[link-downloads]: https://packagist.org/packages/krenor/http2-pusher
[link-license]: https://github.com/krenor/http2-pusher/blob/master/LICENSE.md
