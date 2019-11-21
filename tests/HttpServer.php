<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Process\Tests;

class HttpServer extends \Swoole\Http\Server
{
    /**
     * @var int
     */
    protected $delay = 0;

    /**
     * @param int $value
     */
    public function setDelay($value)
    {
        $this->delay = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        if ($this->delay > 0) {
            sleep($this->delay);
        }
        return parent::start();
    }
}