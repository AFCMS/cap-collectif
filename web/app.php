<?php
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';

Request::setTrustedProxies(
    [
        // ClouFlare IPs (https://www.cloudflare.com/ips/)
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '104.16.0.0/12',
        '108.162.192.0/18',
        '131.0.72.0/22',
        '141.101.64.0/18',
        '162.158.0.0/15',
        '172.64.0.0/13',
        '173.245.48.0/20',
        '188.114.96.0/20',
        '190.93.240.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '172.17.0.0/16',
        '10.10.200.0/16',
        '127.0.0.1',
    ],
    Request::HEADER_X_FORWARDED_ALL
);

$kernel = new AppKernel('prod', false);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
Request::enableHttpMethodParameterOverride();

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
