<?php

declare(strict_types=1);

if (! defined('_PS_VERSION_')) {
    exit;
}

class Bzry_Toolbar extends Module
{
    const COOKIE_NAME = 'bzry_toolbar_admin_url';

    /**
     * @var string
     */
    private $admin_url;

    /**
     * @var Cookie
     */
    private $cookie;

    /**
     * @var Employee
     */
    private $employee;

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

    public function install(): bool
    {
        $success = parent::install()
            && $this->registerHook('actionAdminLoginControllerLoginAfter')
            && $this->registerHook('displayAfterBodyOpeningTag');

        if (!$success) {
            return false;
        }

        $this->storeAdminURL();

        return true;
    }

    protected function getAdminCookie(): Cookie
    {
        if ($this->cookie) {
            return $this->cookie;
        }

        return $this->cookie = new Cookie('psAdmin');
    }

    protected function getAdminURL(): string
    {
        if ($this->admin_url) {
            return $this->admin_url;
        }

        $cookie = $this->getAdminCookie()->{self::COOKIE_NAME} ?? false;

        if ($cookie) {
            return $this->admin_url = $cookie;
        }

        $this->admin_url = Context::getContext()->shop->getBaseURL(true);

        if (defined('_PS_ADMIN_DIR_')) {
            $this->admin_url .= basename(_PS_ADMIN_DIR_);
        }

        return $this->admin_url;
    }

    public function hookActionAdminLoginControllerLoginAfter(array $params)
    {
        $this->storeAdminURL();
    }

    protected function storeAdminURL(): void
    {
        if (!defined('_PS_ADMIN_DIR_')) {
            return;
        }

        $this->getAdminCookie()->{self::COOKIE_NAME} = $this->getAdminURL();
        $this->getAdminCookie()->write();
    }

    public function hookDisplayAfterBodyOpeningTag(): ?string
    {
        if (! Validate::isLoadedObject($this->getEmployee())) {
            return null;
        }

        return $this->display(__FILE__, 'bzry_toolbar.tpl');
    }

    protected function getEmployee(): Employee
    {
        if ($this->employee) {
            return $this->employee;
        }

        try {

            $employee = $this->getAdminCookie()->id_employee;

            if (! $employee) {
                throw new Exception();
            }

            $employee = new Employee($employee);

            if (! Validate::isLoadedObject($employee)) {
                throw new Exception();
            }

            $this->employee = $employee;

        } catch (Exception $e) {

            $this->employee = new Employee();

        }

        return $this->employee;
    }
}
