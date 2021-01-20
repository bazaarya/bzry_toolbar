<?php

declare(strict_types=1);

namespace Bazaarya\Toolbar;

use Bazaarya\Toolbar\Exception\ActionNotAllowedException;
use Bazaarya\Toolbar\Exception\IsFrontException;
use Context;
use Exception;

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

    /**
     * @var Biscuit
     */
    private $biscuit;

    public function __construct()
    {
        $this->biscuit = new Biscuit();
    }

    public function bootstrap(): void
    {
        if (!$this->isBackOffice()) {
            throw new IsFrontException();
        }

        $links = [
            'dashboard' => null,
            'categories'  => [
                'edit'  => null,
                'index' => null,
            ],
            'customers' => null,
            'orders'    => null,
        ];

        foreach ($links as $controller => &$actions) {

            if (!$actions) {
                $actions = $this->generate($controller, 'index');
                continue;
            }

            foreach ($actions as $action => &$link) {
                $link = $this->generate($controller, $action);
            }
        }

        $this->biscuit->store($links);
    }

    public function get(string $controller, int $id = 0, string $action = 'index'): string
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

        $link = $this->biscuit->retrieve();

        try {

            $link = $link[$controller] ?? false;

            if (!$link) {
                throw new Exception();
            }

            if (is_array($link)) {
                $link = $link[$action] ?? false;
            }

            if (!$link) {
                throw new Exception();
            }

            $link = preg_replace($pattern, $replace, $link, 1);
        } catch (Exception $e) {

            $link = '';
        }

        return $link;
    }

    protected function generate(string $controller, string $action): string
    {
        if (!$this->isBackOffice()) {
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

    protected function isBackOffice(): bool
    {
        if (!defined('_PS_ADMIN_DIR_')) {
            return false;
        }

        return true;
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
