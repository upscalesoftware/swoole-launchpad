Swoole Launchpad [![Build Status](https://github.com/upscalesoftware/swoole-launchpad/workflows/Tests/badge.svg?branch=master)](https://github.com/upscalesoftware/swoole-launchpad/actions?query=workflow%3ATests+branch%3Amaster)
================

This library extends the process management capabilities of [Swoole](https://www.swoole.co.uk/) framework.

**Features:**
- Swoole server launch in child process
- Swoole server process termination
- [PHPUnit](https://phpunit.de/) testing framework compatibility

## Installation

The library is to be installed via [Composer](https://getcomposer.org/) as a dependency:
```bash
composer require upscale/swoole-launchpad
```
## Usage

### PHPUnit Tests

The library is particularly useful in PHPUnit-based automated tests:
```bash
vendor/bin/phpunit --process-isolation
```
```php
class HttpServerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Swoole\Http\Server
     */
    protected $server;

    /**
     * @var \Upscale\Swoole\Launchpad\ProcessManager
     */
    protected $processManager;

    /**
     * @var int
     */
    protected $pid;

    protected function setUp()
    {
        $this->server = new \Swoole\Http\Server('127.0.0.1', 8080);
        $this->server->set([
            'log_file' => '/dev/null',
            'log_level' => 4,
            'worker_num' => 1,
        ]);
        
        $this->processManager = new \Upscale\Swoole\Launchpad\ProcessManager();
    }

    protected function tearDown()
    {
        $this->processManager->kill($this->pid);
    }

    public function testResponseStatus()
    {
        $this->server->on('request', function ($request, $response) {
            $response->status(404);
            $response->end();
        });
        $this->pid = $this->processManager->spawn($this->server);

        $result = `curl http://127.0.0.1:8080/ -s -i`;
        $this->assertStringStartsWith('HTTP/1.1 404 Not Found', $result);
    }
    
    public function testResponseBody()
    {
        $this->server->on('request', function ($request, $response) {
            $response->end('Success');
        });
        $this->pid = $this->processManager->spawn($this->server);

        $result = `curl http://127.0.0.1:8080/ -s -i`;
        $this->assertStringStartsWith('HTTP/1.1 200 OK', $result);
        $this->assertStringEndsWith('Success', $result);
    }
}
```

More compact version:
```php
class HttpServerTest extends \Upscale\Swoole\Launchpad\Tests\TestCase
{
    protected function setUp()
    {
        parent::setUp();
    
        $this->server = new \Swoole\Http\Server('127.0.0.1', 8080);
        $this->server->set([
            'log_file' => '/dev/null',
            'log_level' => 4,
            'worker_num' => 1,
        ]);
    }

    public function testResponseStatus()
    {
        $this->server->on('request', function ($request, $response) {
            $response->status(404);
            $response->end();
        });
        $this->spawn($this->server);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith('HTTP/1.1 404 Not Found', $result);
    }
    
    public function testResponseBody()
    {
        $this->server->on('request', function ($request, $response) {
            $response->end('Success');
        });
        $this->spawn($this->server);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith('HTTP/1.1 200 OK', $result);
        $this->assertStringEndsWith('Success', $result);
    }
}
```

Make sure to autoload the test classes in your `composer.json`:
```json
{
    "require-dev": {
        "phpunit/phpunit": "^7.0",
        "upscale/swoole-launchpad": "^1.0"
    },
    "autoload-dev": {
        "psr-4": {
            "Upscale\\Swoole\\Launchpad\\Tests\\": "vendor/upscale/swoole-launchpad/tests/"
        }
    }
}
```

## Contributing

Pull Requests with fixes and improvements are welcome!

## License

Copyright © Upscale Software. All rights reserved.

Licensed under the [Apache License, Version 2.0](https://github.com/upscalesoftware/swoole-launchpad/blob/master/LICENSE.txt).