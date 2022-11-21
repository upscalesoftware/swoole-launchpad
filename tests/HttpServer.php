<?php
declare(strict_types=1);
/**
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Upscale\Swoole\Launchpad\Tests;

class HttpServer extends \Swoole\Http\Server
{
    protected int $startupDelay = 0;

    protected int $shutdownDelay = 0;

    /**
     * Assign server startup delay in seconds
     */
    public function setStartupDelay(int $delay): void
    {
        $this->startupDelay = $delay;
    }

    /**
     * Assign server shutdown delay in seconds
     */
    public function setShutdownDelay(int $delay): void
    {
        $this->shutdownDelay = $delay;
    }

    /**
     * Delay server startup
     */
    public function start(): bool
    {
        if ($this->startupDelay > 0) {
            sleep($this->startupDelay);
        }
        return parent::start();
    }
    
    /**
     * Delay server shutdown
     */
    public function shutdown(): bool
    {
        if ($this->shutdownDelay > 0) {
            sleep($this->shutdownDelay);
        }
        return parent::shutdown();
    }
}