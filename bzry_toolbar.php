<?php

declare(strict_types=1);

use Bazaarya\Toolbar\Biscuit;
use Bazaarya\Toolbar\Exception\NoEmployeeException;
use Bazaarya\Toolbar\Link;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Bzry_Toolbar extends Module
{
    /**
     * @var Biscuit
     */
    private $biscuit;

    /**
     * @var Employee
     */
    private $employee;

    /**
     * @var Link
     */
    private $link;

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

        // Composer
        require_once __DIR__ . '/vendor/autoload.php';
    }

    protected function getEmployee(): Employee
    {
        if ($this->employee) {
            return $this->employee;
        }

        try {

            $employee = $this->getBiscuit()->get()->id_employee;

            if (!$employee) {
                throw new NoEmployeeException();
            }

            $employee = new Employee($employee);

            if (!Validate::isLoadedObject($employee)) {
                throw new NoEmployeeException();
            }

            $this->employee = $employee;
        } catch (NoEmployeeException $e) {

            $this->employee = new Employee();
        }

        return $this->employee;
    }

    protected function getBiscuit(): Biscuit
    {
        if ($this->biscuit instanceof Biscuit) {
            return $this->biscuit;
        }

        return $this->biscuit = new Biscuit();
    }

    protected function getLink(): Link
    {
        if ($this->link instanceof Link) {
            return $this->link;
        }

        return $this->link = new Link();
    }

    public function hookActionAdminLoginControllerLoginAfter(array $params): void
    {
        $this->getLink()->bootstrap();
    }

    public function hookDisplayAfterBodyOpeningTag(): ?string
    {
        if (!Validate::isLoadedObject($this->getEmployee())) {
            return null;
        }

        $links = [
            'dashboard' => null,
            'orders'    => null,
            'customers' => null,
        ];

        foreach ($links as $controller => &$link) {
            $link = $this->getLink()->get($controller);
        }

        $this->smarty->assign($this->name, $links);

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

        $this->getLink()->bootstrap();

        return true;
    }
}
