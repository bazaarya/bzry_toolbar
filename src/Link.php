<?php

declare(strict_types=1);

namespace Bazaarya\Toolbar;

use Bazaarya\Toolbar\Exception\IsFrontException;
use Context;

class Link
{
    protected function create(string $controller, int $id = 0, string $action = ''): string
    {
        if (!defined('_PS_ADMIN_DIR_')) {
            throw new IsFrontException();
        }

        $action     = strtolower($action);
        $controller = strtolower($controller);
        $params     = [];

        if ($id) {
            $params["id_{$controller}"] = $id;
        }

        if ($action) {

            $mapping = [
                'edit'  => 'update',
                'index' => false,
            ];

            $action = $mapping[$action] ?? $action;

            if ($action) {
                $params["{$action}{$controller}"] = true;
            }
        }

        $controller = $controller == 'category' ? 'categories' : $controller;
        $controller = ucfirst($controller);
        $controller = "Admin{$controller}";

        return Context::getContext()
            ->link
            ->getAdminLink($controller, true, [], $params);
    }
}
