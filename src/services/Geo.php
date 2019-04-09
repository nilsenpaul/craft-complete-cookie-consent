<?php

namespace nilsenpaul\cookieconsent\services;

use nilsenpaul\cookieconsent\Plugin;

use Craft;
use craft\base\Component;

class Geo extends Component
{
    protected $ipApiUrl = 'http://api.ipapi.com/';

    public function isEuropeanCountry()
    {
        $data = $this->getInfo();

        if (isset($data['location']['is_eu']) && $data['location']['is_eu']) {
            return true;
        }

        return false;
    }

    public function getInfo($doCache = true)
    {
        $settings = Plugin::$instance->getSettings();

        $data = [];

        $devMode = Craft::$app->config->general->devMode;

        $ip = Craft::$app->getRequest()->remoteIp;

        if (!$settings['useIpApi'] || $settings['ipApiKey'] == '' || in_array($ip, Plugin::$instance->localIps) || $devMode) {
            // Return an empty array
            return [];
        }

        $ip = $this->anonymizeIp($ip);
        $cachedData = Craft::$app->getCache()->get('ccc.geo.' . $ip);

        if ($cachedData) {
            $cached = json_decode($cachedData, true);
            $cached['cached'] = true;
            return $cached;
        }

        if ($settings->ipApiKey !== '') {
            $data = $this->getIpApiData($ip);
        }

        if ($doCache) {
            Craft::$app->getCache()->add('ccc.geo.' . $ip, json_encode($data), 86400);
        }

        return $data;
    }

    private function getIpApiData($ip)
    {
        $settings = Plugin::$instance->getSettings();

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('GET', $this->ipApiUrl . $ip . '?access_key=' . $settings->ipApiKey);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            // TODO: handle error
            return [];
        }
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
}
