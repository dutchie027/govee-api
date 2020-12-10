<?php

namespace dutchie027\govee;

class Lights
{
    protected $client;

    public function __construct(Connect $client)
    {
        $this->client = $client;
    }
}
