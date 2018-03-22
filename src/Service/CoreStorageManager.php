<?php

namespace AVAllAC\PersistentBlockStorage\Service;

class CoreStorageManager
{
    private $volumes;
    private $servers;

    public function __construct(array $servers)
    {
        $this->servers = $servers;
        $this->volumes = [];
        foreach ($servers as $sid => $server) {
            $volumes = preg_replace_callback('/(\d+)-(\d+)/', function ($m) {
                return implode(',', range($m[1], $m[2]));
            }, $server['volumes']);
            foreach (explode(',', $volumes) as $item) {
                $this->volumes[$item] = [
                    'server' => [
                        'id' => $sid,
                        'deliveryUrl' => $server['deliveryUrl'],
                        'adminUrl' => $server['adminUrl']
                    ]
                ];
            }
        }
    }

    public function getUrl(int $volume, int $seek, int $size) : string
    {
        $basePath = $this->volumes[$volume]['server']['deliveryUrl'];
        return  $basePath . '/?volume=' . $volume . '&seek=' . $seek . '&size=' . $size;
    }

    public function getServers()
    {
        return $this->servers;
    }

    public function getVolumes()
    {
        return $this->volumes;
    }
}
