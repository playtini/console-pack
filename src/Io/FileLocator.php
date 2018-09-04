<?php

namespace Playtini\ConsolePack\Io;

class FileLocator
{
    public static function getAppPath(): string
    {
        return dirname(__DIR__, 4);
    }

    public static function getCachePath(string $filename = null): string
    {
        return self::getAppPath() . '/var/cache' . self::getSuffix($filename);
    }

    public static function getDataPath(string $filename = null): string
    {
        return self::getAppPath() . '/var/data' . self::getSuffix($filename);
    }

    public static function getInfoPath(string $filename = null): string
    {
        return self::getAppPath() . '/var/info' . self::getSuffix($filename);
    }

    /**
     * @param string|null $filename
     * @return string
     * @throws \Exception
     */
    public static function findFileSomewhere(string $filename = null): string
    {
        // process absolute path
        if (
            substr($filename, 0, 1) === '/' ||
            strpos($filename, ':\\') !== false ||
            strpos($filename, ':/') !== false
        ) {
            if (is_file($filename)) {
                return $filename;
            }
        }

        $filenames = [
            self::getDataPath($filename),
            self::getInfoPath($filename),
        ];
        foreach ($filenames as $s) {
            if (is_file($s)) {
                return $s;
            }
        }

        throw new \Exception(sprintf('Cannot find file "%s"', $filename));
    }

    private static function getSuffix(string $filename): string
    {
        if ($filename === null) {
            return '';
        }

        $filename = preg_replace('#\.\.+/+#', '/', $filename);

        return '/' . ltrim($filename, '/');
    }
}