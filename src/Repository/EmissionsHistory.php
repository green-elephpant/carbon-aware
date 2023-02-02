<?php

namespace GreenElePHPant\CarbonAware\Repository;

use GreenElePHPant\CarbonAware\Emissions\Emissions;
use GreenElePHPant\CarbonAware\Location\Location;
use PDO;

class EmissionsHistory
{
    public function __construct(
        private readonly PDO $pdo
    )
    {
    }

    public function storeEmissions(Emissions $emissions)
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO emissions (co2e, location) VALUES (?, ?)'
        );

        $statement->execute(
            array_values($emissions->dehydrate())
        );
    }

    public function getAveragOverHours(Location $location, int $hours): int
    {
        $statement = $this->pdo->prepare(
            'SELECT AVG(co2e) AS averageCo2e FROM emissions ' .
            'WHERE created_at >= (CURRENT_DATE - INTERVAL ? HOUR) AND location = ?'
        );

        $statement->execute([ $hours, $location->getCountryCode() ]);

        $row = $statement->fetch();

        return $row['averageCo2e'];
    }
}
