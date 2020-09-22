<?php

require_once __DIR__.'/../vendor/autoload.php';

use GuzzleHttp\Client;

$pimple = new Pimple\Container();
$pimple['config'] = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/../etc/config.yml');
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreRoutingProvider());

$client = new Client();
$url1 = $pimple['CoreUrlGenerator']->generate('upload');
for($i = 0; $i < 100000; $i++) {
    $data = random_bytes(1024 * 300);
    $url2 = $pimple['CoreUrlGenerator']->generate('storage', ['file' => md5($data), 'type' => 'jpg']);
    $request = $client->request('PUT', $url1, ['body' => $data]);
    $request = $client->request('GET', $url2);
    if (md5($request->getBody()->getContents()) !== md5($data)) {
        print "$i!";exit;
    }
}
