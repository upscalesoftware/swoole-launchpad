<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Upscale\Swoole\Launchpad\Tests;

class HttpServer extends \Swoole\Http\Server
{
    /**
     * @var int
     */
    protected $startupDelay = 0;

    /**
     * @var int 
     */
    protected $shutdownDelay = 0;

    /**
     * Assign server startup delay in seconds
     * 
     * @param int $delay
     */
    public function setStartupDelay($delay)
    {
        $this->startupDelay = $delay;
    }

    /**
     * Assign server shutdown delay in seconds
     * 
     * @param int $delay
     */
    public function setShutdownDelay($delay)
    {
        $this->shutdownDelay = $delay;
    }

    /**
     * Delay server startup
     */
    public function start()
    {
        if ($this->startupDelay > 0) {
            sleep($this->startupDelay);
        }
        return parent::start();
    }
    
    /**
     * Delay server shutdown
     */
    public function shutdown()
    {
        if ($this->shutdownDelay > 0) {
            sleep($this->shutdownDelay);
        }
        return parent::shutdown();
    }
}