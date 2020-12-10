# Govee PHP API

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

```php
// Instantiate with defaults
$lights = new dutchie027\govee\Lights("GOVEE-API-KEY");

// Instantiate without defaults, so all messages created
// will be sent from 'Cyril' and to the #accounting channel
// by default. Any names like @regan or #channel will also be linked.
$settings = [
	'log_dir' => '/tmp',
	'log_name' => 'govee-lights',
	'log_tag' => 'mylights',
	'log_level' => 'error'
];

$client = new dutchie027\govee\Lights("GOVEE-API-KEY", $settings);
```

#### Settings

The default settings are fine, however you might want to override the defaults or use your own.**NOTE: All settings are optional and you don't need to provide any**. 

Field | Type | Description | Default Value
----- | ---- | ----------- | -------------
`log_dir` | string | The directory where the log file is stored | [sys_get_temp_dir()](https://www.php.net/manual/en/function.sys-get-temp-dir.php)
`log_name` | string | The name of the log file that is created in `log_dir`. If you don't put .log at the end, it will append it | 6 random characters + [time()](https://www.php.net/manual/en/function.time.php) + .log 
`log_tag` | string | If you share this log file with other applications, this is the tag used in the log file | govee
`log_level` | string | The level of logging the application will do. This must be either `debug`, `info`, `notice`, `warning`, `critical` or `error`. If it is not one of those values it will fail to the default | `warning`

### Get Device Count

```php
print $lights->getDeviceCount();
```

### Get An Array of All Devices
```php
$array = $lights->getDeviceList();
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
$macArray = $lights->getDeviceMACArray();
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
$nameArray = $lights->getDeviceNameArray();
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
print $lights->getLogLocation();
```

#### Example Return String
```
/tmp/2Zo46b.1607566740.log
```

## Contributing

If you're having problems, spot a bug, or have a feature suggestion, [file an issue](https://github.com/dutchie027/govee-api/issues). If you want, feel free to fork the package and make a pull request. This is a work in progresss as I get more info and the Govee API grows.