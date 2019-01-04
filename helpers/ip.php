<?php
namespace Helpers;

class IpHelper
{
    public function getLongIp($ip)
    {
        return sprintf("%u", ip2long($ip));
    }

    public function getIpByLongIp($long)
    {
        return long2ip($long);
    }

    public function getClientIp()
    {
        /* CloudFlare */
        $ip = "";
        if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        } elseif (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"])[0];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public function getClientLongIp()
    {
        $client_ip = $this->getClientIp();
        return $this->getLongIp($client_ip);
    }
}
