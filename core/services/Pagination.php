<?php

declare(strict_types=1);

namespace app\core\services;

final class Pagination
{

    public int $page;

    public function __construct(int $page, public int $pageSize, public int $total)
    {
        $this->page = max(0, $page);
    }

    public function getPageCount(): int
    {
        return (int) ceil($this->total / $this->pageSize);
    }

    public function getBegin(): int
    {
        return $this->page * $this->pageSize + 1;
    }

    public function getEnd(): int
    {
        return min($this->total, ($this->page + 1) * $this->pageSize);
    }

}