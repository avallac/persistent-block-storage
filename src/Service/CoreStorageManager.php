<?php

namespace AVAllAC\PersistentBlockStorage\Service;

class CoreStorageManager
{
    private $volumes;

    public function __construct(array $servers)
    {
        $this->volumes = [];
        foreach ($servers as $server) {
            $volumes = preg_replace_callback('/(\d+)-(\d+)/', function ($m) {
                return implode(',', range($m[1], $m[2]));
            }, $server['volumes']);
            foreach (explode(',', $volumes) as $item) {
                $this->volumes[$item] = [
                    'server' => $server['deliveryUrl']
                ];
            }
        }
    }

    public function getUrl(int $volume, int $seek, int $size) : string
    {
        $basePath = $this->volumes[$volume]['server'];
        return  $basePath . '/?volume=' . $volume . '&seek=' . $seek . '&size=' . $size;
    }
}
