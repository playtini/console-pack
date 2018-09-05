<?php

namespace Playtini\ConsolePack\Info;

use Playtini\ConsolePack\Io\TsvFileReader;

class IsoCountry
{
    /**
     * @param int $countryNumericCode
     * @return string
     * @throws \Exception
     */
    public static function getCountryAlpha2ByNumeric(int $countryNumericCode): string
    {
        static $cache = [];
        if (!$cache) {
            $filename = dirname(__DIR__, 2) . '/data/iso3166-1.txt';
            if (!is_file($filename)) {
                throw new \Exception(sprintf('Cannot find ISO3166-1 file "%s"', $filename));
            }

            $reader = new TsvFileReader($filename);
            foreach ($reader->items() as $item) {
                $cache[(int)$item['Numeric']] = $item['Alpha-2 code'];
            }
            unset($reader);
        }

        return $cache[$countryNumericCode] ?? '';
    }
}