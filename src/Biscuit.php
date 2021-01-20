<?php

declare(strict_types=1);

namespace Bazaarya\Toolbar;

use Cookie;

class Biscuit
{
    const COOKIE_NAME = 'bzry_toolbar';

    /**
     * @var Cookie
     */
    private $cookie;

    public function get(): Cookie
    {
        if ($this->cookie instanceof Cookie) {
            return $this->cookie;
        }

        return $this->cookie = new Cookie('psAdmin');
    }

    public function retrieve(): array
    {
        $links = (string) $this->get()->{self::COOKIE_NAME};
        $links = json_decode($links, true);

        return (array) $links;
    }

    public function store(array $links): void
    {
        $this->get()->{self::COOKIE_NAME} = json_encode($links);
        $this->get()->write();
    }
}
