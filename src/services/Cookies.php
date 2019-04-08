<?php

namespace nilsenpaul\cookieconsent\services;

use nilsenpaul\cookieconsent\Plugin;

use Craft;
use craft\base\Component;

class Cookies extends Component
{
    public function set($value)
    {
        $settings = Plugin::$instance->getSettings();
        $name = $settings->cookieName;

        $value = json_encode($value);

        $domain = Craft::$app->getConfig()->getGeneral()->defaultCookieDomain;
        $expire = (new \DateTime())->modify('+' . $settings->rememberFor . ' seconds')->getTimestamp();
        $path = '/';
        setcookie($name, $value, $expire, $path, $domain);
        $_COOKIE[$name] = $value;

        return true;
    }

    public function get()
    {
        $settings = Plugin::$instance->getSettings();
        $name = $settings->cookieName;

        if (isset($_COOKIE[$name])) {
            return json_decode($_COOKIE[$name]);
        }

        return false;
    }
}
