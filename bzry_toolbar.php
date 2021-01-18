<?php

declare(strict_types=1);

if (! defined('_PS_VERSION_')) {
    exit;
}

class Bzry_Toolbar extends Module
{
    const COOKIE_NAME = 'bzry_toolbar_urls';

    const PREFIX = 'bzry_toolbar_';

    /**
     * @var array
     */
    private $urls;

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

    protected function getAdminCookie(): Cookie
    {
        if ($this->cookie) {
            return $this->cookie;
        }

        return $this->cookie = defined('_PS_ADMIN_DIR_')
            ? $this->context->cookie
            : new Cookie('psAdmin');
    }

    protected function getAdminURLs(): array
    {
        if ($this->urls) {
            return $this->urls;
        }

        $cookie = $this->getAdminCookie()->{self::COOKIE_NAME} ?? false;

        if ($cookie) {
            return $this->urls = json_decode($cookie, true);
        }

        return $this->urls = [
            'dashboard' => $this->context->link->getAdminLink('AdminDashboard'),
            'orders'    => $this->context->link->getAdminLink('AdminOrders'),
            'customers' => $this->context->link->getAdminLink('AdminCustomers'),
        ];
    }

    protected function getEmployee(): Employee
    {
        if ($this->employee) {
            return $this->employee;
        }

        try {

            $employee = $this->getAdminCookie()->id_employee;

            if (!$employee) {
                throw new Exception();
            }

            $employee = new Employee($employee);

            if (!Validate::isLoadedObject($employee)) {
                throw new Exception();
            }

            $this->employee = $employee;
        } catch (Exception $e) {

            $this->employee = new Employee();
        }

        return $this->employee;
    }

    public function hookActionAdminLoginControllerLoginAfter(array $params): void
    {
        $this->storeAdminURLs();
    }

    public function hookDisplayAfterBodyOpeningTag(): ?string
    {
        if (! Validate::isLoadedObject($this->getEmployee())) {
            return null;
        }

        $this->smarty->assign([
            self::PREFIX . 'dashboard' => $this->getAdminURLs()['dashboard'],
            self::PREFIX . 'orders' => $this->getAdminURLs()['orders'],
            self::PREFIX . 'customers' => $this->getAdminURLs()['customers'],
        ]);

        return $this->display(__FILE__, 'bzry_toolbar.tpl');
    }

    public function install(): bool
    {
        $success = parent::install()
            && $this->registerHook('actionAdminLoginControllerLoginAfter')
            && $this->registerHook('displayAfterBodyOpeningTag');

        if (!$success) {
            return false;
        }

        $this->storeAdminURLs();

        return true;
    }

    protected function storeAdminURLs(): void
    {
        if (!defined('_PS_ADMIN_DIR_')) {
            return;
        }

        $this->getAdminCookie()->{self::COOKIE_NAME} = json_encode($this->getAdminURLs());
        $this->getAdminCookie()->write();
    }
}
