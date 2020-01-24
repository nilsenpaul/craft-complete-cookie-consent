<?php

namespace nilsenpaul\cookieconsent\services;

use nilsenpaul\cookieconsent\Plugin;

use Craft;
use craft\base\Component;

use GeoIp2\Database\Reader;

class Geo extends Component
{
    CONST GEOIPLITE_DB_FOLDER = 'geoip-db';
    CONST GEOIPLITE_DB_FILENAME = 'GeoLite2-Country.mmdb';
    CONST GEOIPLITE_DB_ARCHIVE = 'GeoLite2-Country.tar.gz';
    CONST GEOIPLITE_URL = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.tar.gz';
 
    CONST IPAPI_URL = 'http://api.ipapi.com/';
    CONST IPSTACK_URL = 'http://api.ipstack.com/';

    public function isEuropeanCountry()
    {
        $data = $this->getInfo(false);

        return isset($data['is_eu']) && $data['is_eu'];
    }

    public function getInfo($doCache = true)
    {
        $settings = Plugin::$instance->getSettings();
        $devMode = Craft::$app->config->general->devMode;

        $data = [];
        $ip = Craft::$app->getRequest()->remoteIp;

        // Skip this part on local IPs or devMode
        if (in_array($ip, Plugin::$instance->localIps) || $devMode || $settings->geolocationMethod == 'none') {
            // Return an empty array
            return [];
        }

        $ip = $this->anonymizeIp($ip);
        $cachedData = Craft::$app->getCache()->get('ccc.geo.' . $settings->geolocationMethod . '.' . $ip);

        if ($cachedData && $doCache) {
            $cached = json_decode($cachedData, true);
            $cached['cached'] = true;
            return $cached;
        }

        if ($settings->geolocationMethod == 'ipApi' && $settings->ipApiKey != '') {
            $data = $this->getIpApiData($ip);
        } elseif ($settings->geolocationMethod == 'ipstack' && $settings->ipstackKey != '') {
            $data = $this->getIpstackData($ip);
        }

        if ($doCache) {
            Craft::$app->getCache()->add('ccc.geo.' . $settings->geolocationMethod . '.' . $ip, json_encode($data), 86400);
        }

        return $data;
    }

    private function getIpApiData($ip)
    {
        $settings = Plugin::$instance->getSettings();

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('GET', self::IPAPI_URL . $ip . '?access_key=' . $settings->ipApiKey);

            $data = json_decode($response->getBody(), true);

            return [
                'ip' => $ip,
                'isoCode' => $data['country_code'],
                'is_eu' => $data['location']['is_eu'],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getIpstackData($ip)
    {
        $settings = Plugin::$instance->getSettings();

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('GET', self::IPSTACK_URL . $ip . '?access_key=' . $settings->ipstackKey);

            $data = json_decode($response->getBody(), true);

            return [
                'ip' => $ip,
                'isoCode' => $data['country_code'],
                'is_eu' => $data['location']['is_eu'],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }


    private function getGeoIpLiteData($ip)
    {
        $settings = Plugin::$instance->getSettings();

        $storagePath = Craft::$app->getPath()->getStoragePath() . '/' . self::GEOIPLITE_DB_FOLDER;
        $filePath = $storagePath . '/' . self::GEOIPLITE_DB_FILENAME;

        // Update DB if no DB exists
        if (!file_exists($filePath)) {
            $this->updateGeoIpLiteDatabase();
        }

        // Update DB once a week
        $now = date('U');
        $diff = $now - filemtime($filePath);
        $maxDiff = 24 * 3600 * 7; // DB max 7 days old
        if ($diff >= $maxDiff) {
            $this->updateGeoIpLiteDatabase();
        }

        $dbReader = new Reader($filePath);
        $record = $dbReader->country($ip);

        if ($record) {
            return [
                'ip' => $ip,
                'isoCode' => $record->country->isoCode,
                'is_eu' => $record->country->isInEuropeanUnion,
            ];
        }

        return [];
    }


    private function anonymizeIp($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $segments = explode('.', $ip);

            switch (strlen($segments[3])) {
                case 1:
                case 2:
                    $segments[3] = 0;
                    break;

                case 3:
                    $ending = substr($segments[3], 0, 1);
                    if (substr($segments[3], 1) == '00') {
                        $ending .= rand(0, 9);
                        $ending .= rand(0, 9);
                    } else {
                        $ending .= '00';
                    }
                    $segments[3] = $ending;
                    break;
            }

            $anonymizedIp = implode('.', $segments);
            return $anonymizedIp;
        }

        return $ip;
    }

    public function updateGeoIpLiteDatabase()
    {
        $storagePath = Craft::$app->getPath()->getStoragePath() . '/' . self::GEOIPLITE_DB_FOLDER;
        $archivePath = $storagePath . '/' . self::GEOIPLITE_DB_ARCHIVE;
        $filePath = $storagePath . '/' . self::GEOIPLITE_DB_FILENAME;

        if (!file_exists($storagePath)) {
            mkdir($storagePath);
        }

        // Open file for writing
        $filePointer = fopen($archivePath, 'w');
        $client = new \GuzzleHttp\Client();

        $response = $client->get(self::GEOIPLITE_URL, ['save_to' => $filePointer]);

        if ($response->getStatusCode() == 200) {
            // Unarchive from tar
            $tarPath = str_replace('.gz', '', $archivePath);

            // Remove old tar, if any
            if (file_exists($tarPath)) {
                unlink($tarPath);
            }

            $archive = new \PharData($archivePath);
            $archive->decompress();

            $archive = new \PharData($tarPath);

            $folderName = '';
            foreach ($archive as $file) {
                $folderName = $file->getFileName();
            }

            $archive->extractTo($storagePath, $folderName . '/' . self::GEOIPLITE_DB_FILENAME, true);

            return rename(
                $storagePath . '/' . $folderName . '/' . self::GEOIPLITE_DB_FILENAME,
                $filePath
            );
        }
    }
}
