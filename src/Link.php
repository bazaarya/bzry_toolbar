<?php

declare(strict_types=1);

namespace Bazaarya\Toolbar;

use Bazaarya\Toolbar\Exception\ActionNotAllowedException;
use Bazaarya\Toolbar\Exception\IsFrontException;
use Context;
use Emarketing\Action\Products;

class Link
{
    const ACTIONS = [
        'edit'  => 'update',
        'index' => false,
    ];

    const ONLY_VIEW = [
        'customers',
        'orders',
    ];

    public function bootstrap(): void
    {
    }

    public function create(string $controller, int $id, string $action): string
    {
        $controller = strtolower($controller);
        $id         = abs($id);

        $pattern = [
            "@{$controller}/0/@i",
            "@{$controller}/0\?@i",
        ];

        $replace = [
            "{$controller}/{$id}/",
            "{$controller}/{$id}?",
        ];

        /**
         * @todo Obtener de la Cookie.
         */
        $link = $this->generate($controller, $action);

        $link = preg_replace($pattern, $replace, $link, 1);

        return $link;
    }

    protected function generate(string $controller, string $action): string
    {
        if (!defined('_PS_ADMIN_DIR_')) {
            throw new IsFrontException();
        }

        $params     = $this->generateParams($controller, $action);
        $controller = $this->generateController($controller);

        return Context::getContext()
            ->link
            ->getAdminLink($controller, true, [], $params);
    }

    protected function generateParams(string $controller, string $action): array
    {
        $action     = strtolower($action);
        $controller = strtolower($controller);
        $params     = [];

        if (!isset(self::ACTIONS[$action])) {
            throw new ActionNotAllowedException();
        }

        $action = self::ACTIONS[$action];

        if (!in_array($controller, self::ONLY_VIEW)) {

            $params["id_{$this->toSingle($controller)}"] = 0;

            if ($action) {
                $params["{$action}{$this->toSingle($controller)}"] = true;
            }
        }

        return $params;
    }

    protected function generateController(string $controller): string
    {
        $controller = strtolower($controller);
        $controller = ucfirst($controller);
        $controller = "Admin{$controller}";

        return $controller;
    }

    protected function toSingle(string $object): string
    {
        return preg_replace(
            [
                '@ies$@i',
                '@s$@i',
            ],
            [
                'y',
                '',
            ],
            $object
        );
    }
}
