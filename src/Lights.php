<?php

namespace dutchie027\govee;

use GuzzleHttp\Client as Guzzle;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Lights
{
    // api url
    private const API_URL = 'https://developer-api.govee.com';

    private const PING_ENDPOINT = '/ping';

    private const DEVICE_ENDPOINT = '/v1/devices';

    private const DEVICE_CONTROL = self::API_URL . self::DEVICE_ENDPOINT . '/control';

    private const DEVICE_STATE = self::API_URL . self::DEVICE_ENDPOINT . '/state';

    private $p_token;
    private $p_log_location;
    private $p_log_name;
    private $p_log = "govee";
    private $log_literals = [ "debug",
        "info",
        "notice",
        "warning",
        "critical",
        "error"
    ];
    /**
     * The Guzzle HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $guzzle;

    /**
     * Default constructor
     */
    public function __construct($token, array $attributes = [], Guzzle $guzzle = null)
    {
        $this->p_token = $token;
        if (isset($attributes['log_dir']) && is_dir($attributes['log_dir'])) {
            $this->p_log_location = $attributes['log_dir'];
        } else {
            $this->p_log_location = sys_get_temp_dir();
        }

        if (isset($attributes['log_name'])) {
            $this->p_log_name = $attributes['log_name'];
            if (!preg_match("/\.log$/", $this->p_log_name)) {
                $this->p_log_name .= ".log";
            }
        } else {
            $this->p_log_name = $this->pGenRandomString() . "." . time() . ".log";
        }
        if (isset($attributes['log_tag'])) {
            $this->p_log = new Logger($attributes['log_tag']);
        } else {
            $this->p_log = new Logger($this->p_log);
        }

        if (isset($attributes['log_level']) && in_array($attributes['log_level'], $this->log_literals)) {
            if ($attributes['log_level'] == "debug") {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::DEBUG));
            } elseif ($attributes['log_level'] == "info") {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::INFO));
            } elseif ($attributes['log_level'] == "notice") {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::NOTICE));
            } elseif ($attributes['log_level'] == "warning") {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::WARNING));
            } elseif ($attributes['log_level'] == "error") {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::ERROR));
            } elseif ($attributes['log_level'] == "critical") {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::CRITICAL));
            } else {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::WARNING));
            }
        } else {
            $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::INFO));
        }
        $this->guzzle = $guzzle ? : new Guzzle();
    }

    public function getLogLocation()
    {
        return $this->pGetLogPath();
    }

    public function getDeviceList()
    {
        $ha = $this->setHeaders();
        $url = self::API_URL . self::DEVICE_ENDPOINT;
        $response = $this->guzzle->request('GET', $url, $ha);
        $body_array = json_decode($response->getBody(), true);
        return $body_array['data']['devices'];
    }

    public function getLimits()
    {
        $ha = $this->setHeaders();
        $url = self::API_URL . self::PING_ENDPOINT;
        $response = $this->guzzle->request('GET', $url, $ha);
        return $response;
    }

    public function getDeviceCount()
    {
        return count($this->getDeviceList());
    }

    public function getDeviceMACArray()
    {
        $array = $this->getDeviceList();
        foreach ($array as $devices) {
            $dev[] = $devices['device'];
        }
        return $dev;
    }

    public function getDeviceNameArray()
    {
        $array = $this->getDeviceList();
        foreach ($array as $devices) {
            $dev[] = $devices['deviceName'];
        }
        return $dev;
    }

    private function getAPIToken()
    {
        return $this->p_token;
    }

    private function pGetLogPath()
    {
        return $this->p_log_location . '/' . $this->p_log_name;
    }

    private function setHeaders()
    {
        $array['headers'] = [
            'User-Agent' => 'testing/1.0',
            'Govee-API-Key'     => $this->getAPIToken()
        ];
        return $array;
    }

    private function pGenRandomString($length = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
