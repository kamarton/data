<?php


namespace Yiisoft\Data\Reader;


interface TokenableDataInterface
{
    public function withToken();
    public function getNextToken();
    public function getPreviousToken();
}