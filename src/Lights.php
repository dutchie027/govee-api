<?php

namespace dutchie027\govee;

class Lights
{
    private $name_array = array();
    private $mac_array = array();
    protected $client;

    /**
     * turnOn
     * Turns A Light On based on MAC or Name ($device)
     *
     * @param string $device
     *
     * @return string
     *
     */
    public function turnOn($device)
    {
        $mac = $this->getDeviceMAC($device);
        $data['headers'] = $this->client->setHeaders();
        $body['device'] = $mac;
        $body['model'] = $this->model_array[$mac];
        $body['cmd']['name'] = "turn";
        $body['cmd']['value'] = "on";
        $data['body'] = json_encode($body);

        $response = $this->client->client->request('PUT', $this->client::DEVICE_CONTROL, $data);
        return $response->getBody();
    }

    /**
     * Default constructor
     */
    public function __construct(Connect $client)
    {
        $this->client = $client;
        $this->getLights();
    }

    /**
     * getDeviceMAC
     * Takes $device and returns the associated MAC address
     *
     * @param int $device
     *
     * @return string
     *
     */
    private function getDeviceMAC($device)
    {
        if (preg_match('/^([a-fA-F0-9]{2}\:){7}[a-fA-F0-9]{2}$/', $device)) {
            return $device;
        } else {
            if (in_array($device, $this->mac_array)) {
                return $this->name_array[$device];
            }
            else {
                die("Device Not Found");
            }
        }
    }

    /**
     * getLights
     * Called by the constructor. Pre-Loads arrays/hashes to reference
     * lights by either MAC address or name
     *
     *
     * @return void
     *
     */
    private function getLights()
    {
        $all_devices = $this->client->getDeviceList();
        foreach ($all_devices as $device) {
            if (in_array("color", $device['supportCmds'])) {
                $name = $device['deviceName'];
                $mac = $device['device'];
                $model = $device['model'];
                $this->name_array[$name] = $mac;
                $this->mac_array[$mac] = $name;
                $this->model_array[$mac] = $model;
            }
        }
    }

    /**
     * turnOff
     * Turns A Light Off based on MAC or Name ($device)
     *
     * @param string $device
     *
     * @return string
     *
     */
    public function turnOff($device)
    {
        $mac = $this->getDeviceMAC($device);
        $data['headers'] = $this->client->setHeaders();
        $body['device'] = $mac;
        $body['model'] = $this->model_array[$mac];
        $body['cmd']['name'] = "turn";
        $body['cmd']['value'] = "off";
        $data['body'] = json_encode($body);

        $response = $this->client->client->request('PUT', $this->client::DEVICE_CONTROL, $data);
        return $response->getBody();
    }

    /**
     * setBrightness
     * Sets brightness based on MAC or Name ($device) and Brigthness Level ($bl)
     *
     * @param string $device
     * @param int $bl
     *
     * @return string
     *
     */
    public function setBrightness($device, $bl)
    {
        if (!is_numeric($bl) || $bl > 100 || $bl < 0) {
            die("Brigthness must be numeric between 0 and 100");
        }
        $mac = $this->getDeviceMAC($device);
        $data['headers'] = $this->client->setHeaders();
        $body['device'] = $mac;
        $body['model'] = $this->model_array[$mac];
        $body['cmd']['name'] = "brightness";
        $body['cmd']['value'] = $bl;
        $data['body'] = json_encode($body);

        $response = $this->client->client->request('PUT', $this->client::DEVICE_CONTROL, $data);
        return $response->getBody();
    }

    /**
     * setTemp
     * Sets light temperature based on MAC or Name ($device) and temperature level ($tl)
     *
     * @param string $device
     * @param int $tl
     *
     * @return string
     *
     */
    public function setTemp($device, $tl)
    {
        if (!is_numeric($tl) || $tl > 9000 || $tl < 2000) {
            die("Brigthness must be numeric between 2000 and 9000");
        }
        $mac = $this->getDeviceMAC($device);
        $data['headers'] = $this->client->setHeaders();
        $body['device'] = $mac;
        $body['model'] = $this->model_array[$mac];
        $body['cmd']['name'] = "colorTem";
        $body['cmd']['value'] = $tl;
        $data['body'] = json_encode($body);

        $response = $this->client->client->request('PUT', $this->client::DEVICE_CONTROL, $data);
        return $response->getBody();
    }
    
    /**
     * setColor
     * Sets the RGB Color of a strand of lights based on MAC or Name ($device) and RGB
     *
     * @param string $device
     * @param int $r
     * @param int $g
     * @param int $b
     *
     * @return string
     *
     */
    public function setColor($device, $r, $g, $b)
    {
        $mac = $this->getDeviceMAC($device);
        $data['headers'] = $this->client->setHeaders();
        $body['device'] = $mac;
        $body['model'] = $this->model_array[$mac];
        $body['cmd']['name'] = "color";
        $body['cmd']['value']['r'] = $r;
        $body['cmd']['value']['g'] = $g;
        $body['cmd']['value']['b'] = $b;
        $data['body'] = json_encode($body);

        $response = $this->client->client->request('PUT', $this->client::DEVICE_CONTROL, $data);
        return $response->getBody();
    }

    /**
     * getDeviceState
     * Gets the State of a Single Device and returns a JSON Payload
     *
     * @param string $device
     *
     * @return string
     *
     */
    public function getDeviceState($device)
    {
        $mac = $this->getDeviceMAC($device);
        $data['headers'] = $this->client->setHeaders();
        $url = $this->client::DEVICE_STATE . "?device=" . $mac . "&model=" . $this->model_array[$mac];
        $response = $this->client->client->request('GET', $url, $data);
        return $response->getBody();
    }

}
