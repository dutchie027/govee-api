# Govee PHP API

[![Latest Stable Version](https://poser.pugx.org/dutchie027/govee/v)](//packagist.org/packages/dutchie027/govee)
[![Total Downloads](https://poser.pugx.org/dutchie027/govee/downloads)](//packagist.org/packages/dutchie027/govee)
[![License](https://poser.pugx.org/dutchie027/govee/license)](//packagist.org/packages/dutchie027/govee)

A simple PHP package that allows you to control [Govee Smart Lights](https://www.govee.com/) using their [API](https://govee-public.s3.amazonaws.com/developer-docs/GoveeAPIReference.pdf).

## Requirements

* PHP >7.2

## Installation

You can install the package using the [Composer](https://getcomposer.org/) package manager. You can install it by running this command in your project root:

```sh
composer require dutchie027/govee
```

## Basic Usage

### Instantiate the client

To use any of the Govee API functions, you first need a connection reference. The connection refrence can then be fed to either the Lights library or the Plugs library, or even both if you have both Govee Lights and Plugs.

```php
// Ensure we have the composer libraries
require_once ('vendor/autoload.php');

// Instantiate with defaults
$govee = new dutchie027\govee\Connect("GOVEE-API-KEY");

// Instantiate without defaults, this allows you to change things
// like log location, directory, the tag and possible future settings.
$settings = [
	'log_dir' => '/tmp',
	'log_name' => 'govee-api',
	'log_tag' => 'mylights',
	'log_level' => 'error'
];

$govee = new dutchie027\govee\Connect("GOVEE-API-KEY", $settings);
```

#### Settings

The default settings are fine, however you might want to override the defaults or use your own.**NOTE: All settings are optional and you don't need to provide any**. 

Field | Type | Description | Default Value
----- | ---- | ----------- | -------------
`log_dir` | string | The directory where the log file is stored | [sys_get_temp_dir()](https://www.php.net/manual/en/function.sys-get-temp-dir.php)
`log_name` | string | The name of the log file that is created in `log_dir`. If you don't put .log at the end, it will append it | 6 random characters + [time()](https://www.php.net/manual/en/function.time.php) + .log 
`log_tag` | string | If you share this log file with other applications, this is the tag used in the log file | govee
`log_level` | string | The level of logging the application will do. This must be either `debug`, `info`, `notice`, `warning`, `critical` or `error`. If it is not one of those values it will fail to the default | `warning`

## Connect (Core) Functions

### Get Device Count

```php
print $govee->getDeviceCount();
```

### Get An Array of All Devices
```php
$array = $govee->getDeviceList();
```

#### Example Return Array
```
Array
(
    [0] => Array
        (
            [device] => 46:F1:CC:F6:FC:65:FF:AA
            [model] => H6159
            [deviceName] => Office-Color
            [controllable] => 1
            [retrievable] => 1
            [supportCmds] => Array
                (
                    [0] => turn
                    [1] => brightness
                    [2] => color
                    [3] => colorTem
                )

        )

)
```

### Get An Array of All Callable MAC Addresses 

```php
$macArray = $govee->getDeviceMACArray();
```

#### Example Return Array
```
Array
(
    [0] => A9:E9:0A:04:AD:CD:12:34
    [1] => FA:8F:50:B2:AD:A7:00:12
    [2] => E0:94:41:AC:62:13:56:78
)
```

### Get An Array of All Device Names
```php
$nameArray = $govee->getDeviceNameArray();
```

#### Example Return Array
```
Array
(
    [0] => My-Living-Room
    [1] => Hallway
    [2] => Fire-House
)
```

### Get the location of the log file
```php
print $govee->getLogLocation();
```

#### Example Return String
```
/tmp/2Zo46b.1607566740.log
```

## Lights Functions

### Controlling Lights

To control lights, you first need to make a connection and then reference the connection

```php
// Ensure we have the composer libraries
require_once ('vendor/autoload.php');

// Instantiate with defaults
$govee = new dutchie027\govee\Connect("GOVEE-API-KEY");

// Now lets connect to the lights
$lights = new dutchie027\govee\Lights($govee);
```

Once you've got a reference to the lights, it will preload all of the MAC Address(es) and name(s) of the devices.

#### Turning A Light ON

To turn a light on, simply feed it the MAC address or the name of the light.
```php
$lights->turnOn("AC:14:A3:D5:E6:C4:3D:AE");

or

$lights->turnOn("Office-Wall");
```

#### Turning A Light OFF

Like turning a light on, to turn a light off, simply feed the MAC address or the name of the light.
```php
$lights->turnOff("AC:14:A3:D5:E6:C4:3D:AE");

or

$lights->turnOff("Office-Wall");
```

#### Adjusting BRIGHTNESS of A Light

To adjust the brigthness, simply give the name or MAC and the brightness, an INT between 0 and 100.
```php
$lights->setBrightness("AC:14:A3:D5:E6:C4:3D:AE", 75);

or

$lights->setBrightness("Office-Wall", 75);
```

#### Changing the COLOR of A Light

To adjust the color, simply give the name or MAC and the brightness and then feed the R, G, B colors you'd like the device to set itself to. *NOTE* the values for Red, Green and Blue must be between 0 and 255.

```php
$lights->setColor("AC:14:A3:D5:E6:C4:3D:AE", 255, 255, 0);

or

$lights->setBrightness("Office-Wall", 255, 0, 0);
```

#### Changing the TEMPERATURE of A Light

To adjust the temperature, simply give the name or MAC and the name and then feed the temperature. *NOTE* Temperature must be an INT between 2000 and 9000.
```php
$lights->setTemp("AC:14:A3:D5:E6:C4:3D:AE", 5000);

or

$lights->setTemp("Office-Wall", 5000);
```

#### Get the STATE of A Light
To get all of the details about a light, simply feed getDeviceState the name or the MAC address. You'll get a JSON return you can then either read or feed to `json_decode` and turn in to an array to use/read.

```php
$lights->getDeviceState("AC:14:A3:D5:E6:C4:3D:AE");

or

$lights->getDeviceState("Office-Wall");
```

```
{
  "data": {
    "device": "AC:14:A3:D5:E6:C4:3D:AE",
    "model": "Office-Wall",
    "properties": [
      {
        "online": true
      },
      {
        "powerState": "on"
      },
      {
        "brightness": 100
      },
      {
        "color": {
          "r": 255,
          "b": 0,
          "g": 255
        }
      }
    ]
  }
}
```

## Plugs Functions

### Placeholder for Plugs
```php
// Don't forget to first make a "Connect" connection and then reference it
$plugs = new dutchie027\govee\Plugs($govee);
```

## Contributing

If you're having problems, spot a bug, or have a feature suggestion, [file an issue](https://github.com/dutchie027/govee-api/issues). If you want, feel free to fork the package and make a pull request. This is a work in progresss as I get more info and the Govee API grows.
