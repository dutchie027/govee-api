<?php

namespace dutchie027\govee;

class Plugs
{
    protected $client;

    public function __construct(Connect $client)
    {
        $this->client = $client;
    }
}
