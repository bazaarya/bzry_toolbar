<?php

declare(strict_types=1);

if (! defined('_PS_VERSION_')) {
    exit;
}

class Bzry_Toolbar extends Module
{
    public function __construct()
    {
        $this->author                   = 'Bazaarya';
        $this->author_uri               = 'https://bazaarya.io';
        $this->name                     = 'bzry_toolbar';
        $this->ps_versions_compliancy   = ['min' => '1.7.7.0', 'max' => _PS_VERSION_];
        $this->tab                      = 'front_office_features';
        $this->version                  = '0.0';

        parent::__construct();

        $this->displayName = $this->l('Bazaarya - Toolbar');
        $this->description = $this->l('Toolbar that speeds up the use of your shop.');
    }
}
