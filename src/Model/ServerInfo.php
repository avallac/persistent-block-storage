<?php

namespace AVAllAC\PersistentBlockStorage\Model;

class ServerInfo
{
    protected $id;
    protected $deliveryUrl;
    protected $adminUrl;

    public function __construct(int $id, string $deliveryUrl, string $adminUrl)
    {
        $this->id = $id;
        $this->deliveryUrl = $deliveryUrl;
        $this->adminUrl = $adminUrl;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDeliveryUrl(): string
    {
        return $this->deliveryUrl;
    }

    /**
     * @return string
     */
    public function getAdminUrl(): string
    {
        return $this->adminUrl;
    }
}