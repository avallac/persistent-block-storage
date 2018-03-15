<?php

require_once __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$server = new \React\Http\Server(function (\Psr\Http\Message\ServerRequestInterface $request) use ($loop) {
    return new \React\Promise\Promise(function ($resolve, $reject) use ($request, $loop) {
        $client = new React\HttpClient\Client($loop);
        $request = $client->request('GET', 'http://test.rroe.ru');
        $request->on('response', function (\React\HttpClient\Response $response) use ($resolve) {
            $output = '';
            $response->on('data', function ($chunk) use (&$output) {
                $output .= $chunk;
            });
            $response->on('end', function () use ($resolve, &$output) {
                $response = new \React\Http\Response(200, ['Content-Type' => 'text/plain'], $output);
                $resolve($response);
            });
        });
        $request->on('error', function () use ($resolve) {
            $response = new \React\Http\Response(400, ['Content-Type' => 'text/plain'], 'error');
            $resolve($response);
        });

        $request->end();
    });
});

$socket = new React\Socket\Server('0.0.0.0:8080', $loop);
$server->listen($socket);
print 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
pcntl_fork();
$loop->run();
