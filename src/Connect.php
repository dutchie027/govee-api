<?php

namespace dutchie027\govee;

use GuzzleHttp\Client as Guzzle;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Connect
{

    /**
     * Root of the API.
     *
     * @const string
     */
    public const API_URL = 'https://developer-api.govee.com';

    /**
     * Ping Endpoint
     *
     * @const string
     */
    public const PING_ENDPOINT = '/ping';

    /**
     * RAW Device Endpoint
     *
     * @const string
     */
    public const DEVICE_ENDPOINT = '/v1/devices';

    /**
     * Control Endpoint
     *
     * @const string
     */
    public const DEVICE_CONTROL = self::API_URL . self::DEVICE_ENDPOINT . '/control';

    /**
     * Device State Endpoint
     *
     * @const string
     */
    public const DEVICE_STATE = self::API_URL . self::DEVICE_ENDPOINT . '/state';

    /**
     * API Token
     *
     * @var string
     */
    private $p_token;

    /**
     * Remaining Times To Call the API
     *
     * @var string
     */
    public $rate_remain;

    /**
     * EPOCH when rate resets
     *
     * @var string
     */
    public $rate_reset;

    /**
     * Total Rate Limit
     *
     * @var string
     */
    public $rate_total;

    /**
     * Log Directory
     *
     * @var string
     */
    private $p_log_location;

    /**
     * Log Reference
     *
     * @var string
     */
    protected $p_log;

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
    private $p_log_tag = "govee";

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
    public $client;

    /**
     * Default constructor
     */
    public function __construct($token, array $attributes = [], Guzzle $client = null)
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
            $this->p_log = new Logger($this->p_log_tag);
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
        $this->client = $client ? : new Guzzle();
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
     * setRateVars
     * Takes a header array and sets the rate variables
     *
     * @param array $header
     *
     * @return void
     *
     */
    public function setRateVars($header)
    {
        $this->rate_remain = $header['Rate-Limit-Remaining'];
        $this->rate_reset = $header['Rate-Limit-Reset'];
        $this->rate_total = $header['Rate-Limit-Total'];
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
        $data['headers'] = $this->setHeaders();
        $url = self::API_URL . self::DEVICE_ENDPOINT;
        $response = $this->client->request('GET', $url, $data);
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
        $data['headers'] = $this->setHeaders();
        $url = self::API_URL . self::PING_ENDPOINT;
        $response = $this->client->request('GET', $url, $data);
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
    protected function getAPIToken()
    {
        return $this->p_token;
    }

    /**
     * getLogPointer
     * Returns a referencd to the logger
     *
     *
     * @return reference
     *
     */
    public function getLogPointer()
    {
        return $this->p_log;
    }

    /**
     * pGetLogPath
     * Returns full path and name of the log file
     *
     *
     * @return string
     *
     */
    protected function pGetLogPath()
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
    public function setHeaders()
    {
        $array = [
            'User-Agent' => 'testing/1.0',
            'Content-Type' => 'application/json',
            'Govee-API-Key' => $this->getAPIToken()
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


    public function checkPing()
    {
        $url = self::API_URL . self::PING_ENDPOINT;
        $response = $this->client->request('GET', $url);
        if (preg_match("/pong/", $response->getBody())) {
            return true;
        } else {
            die("API Seems Offline or you have connectivity issues at present.");
        }
    }
}
