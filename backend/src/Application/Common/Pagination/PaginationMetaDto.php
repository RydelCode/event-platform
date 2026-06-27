<?php

declare(strict_types=1);

namespace App\Application\Common\Pagination;

final readonly class PaginationMetaDto
{
    public int $pages;

    public function __construct(
        public int $page,
        public int $limit,
        public int $total,
    ) {
        $this->pages = (int) ceil($total / $limit);
    }
}
