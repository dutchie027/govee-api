<?php

namespace dutchie027\govee;

class Plugs
{
    private $name_array = [];
    private $mac_array = [];
    protected $client;

    /**
     * Default constructor
     */
    public function __construct(Connect $client)
    {
        $this->client = $client;
        $this->getPlugs();
    }

    /**
     * turnOn
     * Turns A Plug On based on MAC or Name ($device)
     *
     * @param string $device
     *
     * @return string
     */
    public function turnOn($device)
    {
        $mac = $this->getDeviceMAC($device);
        $data['headers'] = $this->client->setHeaders();
        $body['device'] = $mac;
        $body['model'] = $this->model_array[$mac];
        $body['cmd']['name'] = 'turn';
        $body['cmd']['value'] = 'on';
        $data['body'] = json_encode($body);

        $response = $this->client->client->request('PUT', $this->client::DEVICE_CONTROL, $data);
        $this->client->setRateVars($response->getHeaders());

        return $response->getBody();
    }

    /**
     * turnOff
     * Turns A Plug Off based on MAC or Name ($device)
     *
     * @param string $device
     *
     * @return string
     */
    public function turnOff($device)
    {
        $mac = $this->getDeviceMAC($device);
        $data['headers'] = $this->client->setHeaders();
        $body['device'] = $mac;
        $body['model'] = $this->model_array[$mac];
        $body['cmd']['name'] = 'turn';
        $body['cmd']['value'] = 'off';
        $data['body'] = json_encode($body);

        $response = $this->client->client->request('PUT', $this->client::DEVICE_CONTROL, $data);
        $this->client->setRateVars($response->getHeaders());

        return $response->getBody();
    }

    /**
     * getDeviceMAC
     * Takes $device and returns the associated MAC address
     *
     * @param int $device
     *
     * @return string
     */
    private function getDeviceMAC($device)
    {
        if (preg_match('/^([a-fA-F0-9]{2}\:){7}[a-fA-F0-9]{2}$/', $device)) {
            return $device;
        }

        if (in_array($device, $this->mac_array, true)) {
            return $this->name_array[$device];
        }

        die('Device Not Found');
    }

    /**
     * getPlugs
     * Called by the constructor. Pre-Loads arrays/hashes to reference
     * a plug by either MAC address or name
     *
     * @return void
     */
    private function getPlugs()
    {
        $all_devices = $this->client->getDeviceList();

        foreach ($all_devices as $device) {
            if (!in_array('color', $device['supportCmds'], true)) {
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
     * getDeviceState
     * Gets the State of a Single Device and returns a JSON Payload
     *
     * @param string $device
     *
     * @return string
     */
    public function getDeviceState($device)
    {
        $mac = $this->getDeviceMAC($device);
        $url = $this->client::DEVICE_STATE . '?device=' . $mac . '&model=' . $this->model_array[$mac];
        $response = $this->client->makeAPICall('GET', $url);

        return $response->getBody();
    }
}
