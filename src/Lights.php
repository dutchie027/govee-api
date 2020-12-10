<?php

namespace dutchie027\govee;

use GuzzleHttp\Client as Guzzle;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Lights
{

    /**
     * Root of the API.
     *
     * @const string
     */
    private const API_URL = 'https://developer-api.govee.com';

    /**
     * Ping Endpoint
     *
     * @const string
     */
    private const PING_ENDPOINT = '/ping';

    /**
     * RAW Device Endpoint
     *
     * @const string
     */
    private const DEVICE_ENDPOINT = '/v1/devices';

    /**
     * Control Endpoint
     *
     * @const string
     */
    private const DEVICE_CONTROL = self::API_URL . self::DEVICE_ENDPOINT . '/control';

    /**
     * Device State Endpoint
     *
     * @const string
     */
    private const DEVICE_STATE = self::API_URL . self::DEVICE_ENDPOINT . '/state';

    /**
     * API Token
     *
     * @var string
     */
    private $p_token;

    /**
     * Log Directory
     *
     * @var string
     */
    private $p_log_location;

    /**
     * Log Name
     *
     * @var string
     */
    private $p_log_name;

    /**
     * Log File Tag
     *
     * @var string
     */
    private $p_log = "govee";

    /**
     * Log Types
     *
     * @var array
     */
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

    /**
     * getLogLocation
     * Alias to Get Log Path
     *
     *
     * @return string
     *
     */
    public function getLogLocation()
    {
        return $this->pGetLogPath();
    }

    /**
     * getDeviceList
     * Returns Full Device List Array
     *
     *
     * @return array
     *
     */
    public function getDeviceList()
    {
        $ha = $this->setHeaders();
        $url = self::API_URL . self::DEVICE_ENDPOINT;
        $response = $this->guzzle->request('GET', $url, $ha);
        $body_array = json_decode($response->getBody(), true);
        return $body_array['data']['devices'];
    }

    /**
     * getLimits
     * Returns Limit Headers
     *
     *
     * @return array
     *
     */
    public function getLimits()
    {
        $ha = $this->setHeaders();
        $url = self::API_URL . self::PING_ENDPOINT;
        $response = $this->guzzle->request('GET', $url, $ha);
        return $response;
    }

    /**
     * getDeviceCount
     * Returns total number of controllable devices
     *
     *
     * @return int
     *
     */
    public function getDeviceCount()
    {
        return count($this->getDeviceList());
    }

    /**
     * getDeviceMACArray
     * Returns array of controllable MAC addresses
     *
     *
     * @return array
     *
     */
    public function getDeviceMACArray()
    {
        $array = $this->getDeviceList();
        foreach ($array as $devices) {
            $dev[] = $devices['device'];
        }
        return $dev;
    }

    /**
     * getDeviceNameArray
     * Returns Array of Device Names
     *
     *
     * @return array
     *
     */
    public function getDeviceNameArray()
    {
        $array = $this->getDeviceList();
        foreach ($array as $devices) {
            $dev[] = $devices['deviceName'];
        }
        return $dev;
    }

    /**
     * getAPIToken
     * Returns the stored API Token
     *
     *
     * @return string
     *
     */
    private function getAPIToken()
    {
        return $this->p_token;
    }

    /**
     * pGetLogPath
     * Returns full path and name of the log file
     *
     *
     * @return string
     *
     */
    private function pGetLogPath()
    {
        return $this->p_log_location . '/' . $this->p_log_name;
    }

    /**
     * setHeaders
     * Sets the headers using the API Token
     *
     *
     * @return array
     *
     */
    private function setHeaders()
    {
        $array['headers'] = [
            'User-Agent' => 'testing/1.0',
            'Govee-API-Key'     => $this->getAPIToken()
        ];
        return $array;
    }

    /**
     * pGenRandomString
     * Generates a random string of $length
     *
     * @param int $length
     *
     * @return string
     *
     */
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
