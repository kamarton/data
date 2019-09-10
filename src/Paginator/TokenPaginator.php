<?php
declare(strict_types=1);

namespace Yiisoft\Data\Paginator;


use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\FilterableDataInterface;
use Yiisoft\Data\Reader\TokenableDataInterface;

class TokenPaginator
{
    private $dateReader;
    private $paginator;
    private $token;
    private $ttl;

    public function __construct(DataReaderInterface $dataReader)
    {
        $this->dateReader = $dataReader;
    }

    public function withPaginator($paginator): self
    {
        $new = clone $this;
        $new->paginator = $paginator;
        return $new;
    }

    public function withToken(string $token): self
    {
        $new = clone $this;
        $new->token = $token;
        return $new;
    }

    public function getNextToken(): string {
        if($this->dateReader instanceof TokenableDataInterface) {
            return $this->dateReader->getNextToken();
        }

        // @TODO generate
        return '';
    }
}