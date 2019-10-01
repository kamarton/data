<?php
declare(strict_types=1);

namespace Yiisoft\Data\Paginator;


use Yiisoft\Cache\Cache;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\TokenableDataInterface;

class TokenPaginator implements PaginatorInterface
{
    /**
     * @var DataReaderInterface
     */
    private $dateReader;
    /**
     * @var PaginatorInterface
     */
    private $paginator;
    /**
     * @var int|null
     */
    private $ttl;
    /**
     * @var string|null
     */
    private $nextToken;
    /**
     * @var string|null
     */
    private $previousToken;
    /**
     * @var int
     */
    private $pageSize = self::DEFAULT_PAGE_SIZE;
    /**
     * @var mixed
     */
    private $currentNextToken;
    /**
     * @var mixed
     */
    private $currentPreviousToken;
    /**
     * @var Cache|null
     */
    private $tokenStorage;
    /**
     * @var array|null
     */
    private $readCache;

    public function __construct(DataReaderInterface $dataReader)
    {
        $this->dateReader = $dataReader;
    }

    /**
     * @param PaginatorInterface|null $paginator
     * @return TokenPaginator
     */
    public function withPaginator(?PaginatorInterface $paginator)
    {
        $new = clone $this;
        $new->paginator = $paginator;
        return $new;
    }

    /**
     * @param Cache|null $cache
     * @return TokenPaginator
     */
    public function withTokenCache(?Cache $cache)
    {
        $new = clone $this;
        $new->tokenStorage = $cache;
        return $new;
    }

    public function read(): iterable
    {
        if ($this->readCache !== null) {
            return $this->readCache;
        }
        if ($this->paginator !== null) {
            $paginator = $this->paginator
                ->withPreviousPageToken($this->decodeToken($this->previousToken))
                ->withNextPageToken($this->decodeToken($this->nextToken))
                ->withPageSize($this->pageSize);
            return $paginator->read();
        }
        $reader = $this->dateReader->withLimit($this->pageSize);

        if ($reader instanceof TokenableDataInterface) {
            $this->currentNextToken = $reader->getNextToken();
            $this->currentPreviousToken = $reader->getPreviousToken();
        }
    }

    public function isOnLastPage(): bool
    {
        $this->initialize();
    }

    public function isOnFirstPage(): bool
    {
        $this->initialize();
    }

    public function getNextPageToken(): ?string
    {
        $this->initialize();
        return $this->encodeToken($this->currentNextToken);
    }

    public function getPreviousPageToken(): ?string
    {
        $this->initialize();
        return $this->encodeToken($this->currentPreviousToken);
    }

    /**
     * @return static
     */
    public function withNextPageToken(?string $token)
    {
        $new = clone $this;
        $new->nextToken = $token;
        if ($token !== null) {
            $new->previousToken = null;
        }
        return $new;
    }

    /**
     * @return static
     */
    public function withPreviousPageToken(?string $token)
    {
        $new = clone $this;
        $new->previousToken = $token;
        if ($token !== null) {
            $new->nextToken = null;
        }
        return $new;
    }

    /**
     * @return static
     */
    public function withPageSize(int $limit)
    {
        $new = clone $this;
        $new->pageSize = $limit;
        return $new;
    }

    protected function getTokenStorageData(string $token)
    {
        return $this->tokenStorage->get($token);
    }

    protected function setTokenStorageData(string $token, $data)
    {
        return $this->tokenStorage->set($token, $data);
    }

    protected function encodeToken($data)
    {
        return (string)$data;
    }

    protected function decodeToken(?string $token)
    {
        return $token;
    }

    public function getCurrentPageSize(): int
    {
        $this->initialize();
        return count($this->readCache);
    }

    protected function initialize()
    {
        if ($this->readCache !== null) {
            return;
        }
        foreach ($this->read() as $void) ;
    }

    public function __clone()
    {
        $this->readCache = null;
        $this->currentPreviousToken = null;
        $this->currentNextToken = null;
    }
}