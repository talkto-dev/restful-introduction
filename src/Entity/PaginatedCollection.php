<?php

namespace App\Entity;


class PaginatedCollection
{
    private $items;
    private $total;
    private $count;
    private $links;

    public function __construct(array $items, $totalItems)
    {
        $this->items = $items;
        $this->links = [];
        $this->total = $totalItems;
        $this->count = count($items);
    }

    public function addLink($ref, $url)
    {
        $this->links[$ref] = $url;
    }

    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }
}
