<?php
declare(strict_types=1);

namespace Yiisoft\Data\Paginator;


interface PaginatorInterface
{
    public const DEFAULT_PAGE_SIZE = 10;

    public function read(): iterable;
    public function isOnLastPage(): bool;
    public function isOnFirstPage(): bool;
    public function getNextPageToken(): ?string;
    public function getPreviousPageToken(): ?string;
    public function withNextPageToken(?string $token): self;
    public function withPreviousPageToken(?string $token): self;
    public function withPageSize(int $limit): self;
}