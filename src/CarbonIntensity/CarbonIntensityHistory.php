<?php

namespace GreenElePHPant\CarbonAware\CarbonIntensity;

use GreenElePHPant\CarbonAware\Location\Location;
use PDO;

class CarbonIntensityHistory
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function store(CarbonIntensity $carbonIntensity): void
    {
        $statement = $this->pdo->prepare(
            'REPLACE INTO emissions (co2e, location, created_at) VALUES (?, ?, ?)'
        );

        $statement->execute(
            array_values($carbonIntensity->dehydrate())
        );
    }

    public function storeEmissionsResult(string $location, int $co2e)
    {
        $statement = $this->pdo->prepare(
            'REPLACE INTO emissions_result (emissions_day, emissions_hour, location, co2e) ' .
            'VALUES (?, ?, ?, ?)'
        );

        // TODO dehydrate function
        $statement->execute([
            date('Ymd'),
            date('H'),
            $location,
            $co2e
        ]);
    }

    public function getAveragOverHours(Location $location, int $hours): int
    {
        $statement = $this->pdo->prepare(
            'SELECT AVG(co2e) AS averageCo2e FROM emissions ' .
            'WHERE created_at >= (CURRENT_DATE - INTERVAL ? HOUR) AND location = ?'
        );

        $statement->execute([ $hours, $location->getCountryCode() ]);

        $row = $statement->fetch();

        if ($row === false) {
            return false;
        }

        return (int) $row['averageCo2e'];
    }

    public function getEmissions(Location $location, \DateTime $startDateTime, \DateTime $endDateTime): array
    {
        $statement = $this->pdo->prepare(
            'SELECT co2e, created_at FROM emissions ' .
            'WHERE location = ? AND created_at BETWEEN ? AND ?'
        );

        $statement->execute([
            $location->getCountryCode(),
            $startDateTime->format('Y-m-d H:i:s'),
            $endDateTime->format('Y-m-d H:i:s'),
        ]);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($rows as $row) {
            $timestamp = strtotime(date('Y-m-d H:00:00', strtotime($row['created_at'])));
            $result[$timestamp] = new CarbonIntensity($row['co2e'], $location, $row['created_at']);
        }

        return $result;
    }

    public function getEmissionsByLocationAndDate(Location $location, int $year, int $month, int $day, int $hour): array
    {
        $statement = $this->pdo->prepare(
            'SELECT co2e FROM emissions ' .
            'WHERE location = ? AND emissions_day = ? AND emissions_hour = ?'
        );

        $statement->execute([
            $location->getCountryCode(),
            $year . $month . $day,
            $hour
        ]);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($rows as $row) {
            $result[] = new CarbonIntensity($row['co2e'], $location);
        }

        return $result;
    }

    public function getEmissionsByLocation(Location $location): array
    {
        $statement = $this->pdo->prepare(
            'SELECT co2e FROM emissions ' .
            'WHERE location = ?'
        );

        $statement->execute([
            $location->getCountryCode(),
        ]);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($rows as $row) {
            $result[] = new CarbonIntensity($row['co2e'], $location);
        }

        return $result;
    }
}
