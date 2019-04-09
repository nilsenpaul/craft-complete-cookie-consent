<?php

namespace nilsenpaul\cookieconsent\services;

use nilsenpaul\cookieconsent\Plugin;

use Craft;
use craft\base\Component;

class Cookies extends Component
{
    CONST BASE_COOKIE_NAME = 'ccc-visit';

    public function setFirstVisitCookie()
    {
        $settings = Plugin::$instance->getSettings();
        $name = self::BASE_COOKIE_NAME;
        $expire = (new \DateTime())->modify('+' . $settings->rememberFor . ' seconds')->getTimestamp();

        $this->set($name, 1, $expire);

        return true;
    }

    public function isFirstVisit()
    {
        $visitCookie = $this->get(self::BASE_COOKIE_NAME);

        if (!$visitCookie) {
            return true;
        }

        return false;
    }

    public function setConsentCookie($value)
    {
        $settings = Plugin::$instance->getSettings();
        $name = $settings->cookieName;
        $value = json_encode($value);
        $expire = (new \DateTime())->modify('+' . $settings->rememberFor . ' seconds')->getTimestamp();

        $this->set($name, $value, $expire);

        return true;
    }

    public function getConsentCookie()
    {
        $settings = Plugin::$instance->getSettings();
        $name = $settings->cookieName;

        return $this->get($name);
    }

    protected function set($name, $value, $expire)
    {
        $domain = Craft::$app->getConfig()->getGeneral()->defaultCookieDomain;
        $path = '/';
        setcookie($name, $value, $expire, $path, $domain);
        $_COOKIE[$name] = $value;

        return true;
    }

    protected function get($name)
    {
        if (isset($_COOKIE[$name])) {
            return json_decode($_COOKIE[$name]);
        }

        return false;
    }

}
