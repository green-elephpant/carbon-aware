<p align="center">
	<img src="./docs/images/green-elephpant-logo.svg" alt="Green ElePHPant" width="400">
</p>

<h1 align="center">Green ElePHPant - CarbonAware</h1>

The **Green ElePHPant** wants to help you to reduce the carbon emissions of your software.

One way of doing so is to utilize energy when it is the "greenest" - i.e., when it most of it comes from renewable 
energies rather burning fossil fuels.

`GreenElephpant\CarbonAware` is a simple wrapper for APIs that provide information about the current or future carbon 
intensity at many regions worldwide. Currently, we support [CO2Signal](https://www.co2signal.com/) and 
[Electricity Maps](https://www.electricitymaps.com/). Hopefully more to come!

## Key concept

Building carbon aware applications means that certain activities or jobs will not, or at least in lower frequency, be 
carried out if the carbon intensity is too high.

For example, if the carbon intensity is low, we can update a dashboard more often than if it is not:

```php
if ($carbonAwareService->isLow()) {
    $schedule
        ->command('dashboard:update')
        ->everyFiveMinutes();
} else {
    $schedule
        ->command('dashboard:update')
        ->everyTenMinutes();
}
```

## Installation

Simply use composer to add `GreenElephpant\CarbonAware` to your project:

`composer require green-elephpant/carbon-aware`

## Configuration

`GreenElephpant\CarbonAware` was designed to require as few external dependencies as possible so that it can fit in 
almost every project. It uses several PSR for e.g. HTTP messages (PSR-17) and clients (PSR-18), so it won't work out 
of the box but require a bit (but not a lot) of hand wiring.

```php
use Buzz\Client\Curl;
use GreenElephpant\CarbonAware\DataProvider\EnergyCharts\Connector\EnergyChartsConnector;
use GreenElephpant\CarbonAware\DataProvider\EnergyCharts\EnergyCharts;
use GreenElephpant\CarbonAware\Service\CarbonAwareService;
use Nyholm\Psr7\Factory\Psr17Factory;

// Create HTTP client with HTTP message factory
// E.g. nyholm/psr7 and kriswallsmith/buzz
$psr17Factory = new Psr17Factory();
$psr18Client = new Curl($psr17Factory);

// Create Connector (here: for energy-charts.info)
$energyChartsConnector = new EnergyChartsConnector(
    $psr18Client,
    $psr17Factory,
);

// Setup location (the region of the world you want to get the information for)
$location = new GreenElephpant\CarbonAware\Location\Location('DE');

// Finally create the DataProvider instance, using the connector and the location
$dataProvider = new EnergyCharts(
    $co2SignalConnector,
    $location
);

$carbonAwareService = new CarbonAwareService($connector
```

## Internals

`GreenElephpant\CarbonAware` uses 3rd party APIs to receive carbon intensity data. To ensure future-proof and easy 
maintenance,

* `Connector`: the actual API wrapper. Provides easy access to the relevant API endpoints (note: only those endpoints that are relevant for this project are supported.
* `DataProvider`: the link between
* `CarbonAwareService`: provides uniform API

