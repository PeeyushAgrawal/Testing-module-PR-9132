<?php
/**
* 2010-2018 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright 2010-2018 Webkul IN
*  @license   https://store.webkul.com/license.html
*/

if (!defined('_PS_VERSION_')) {
    exit;
}
include_once dirname(__FILE__).'/classes/WkTestHelper.php';
class WkTestModule extends Module
{
    const _INSTALL_SQL_FILE_ = 'install.sql';
    public function __construct()
    {
        $this->name = 'wktestmodule';
        $this->tab = 'front_office_features';
        $this->version = '4.0.0';
        $this->author = 'Webkul';
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Test Module');
        $this->description = $this->l('This module is for testing purpose only.');
        $this->confirmUninstall = $this->l('Are you sure?');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * To add additional field in customer address form
     * The additionalCustoerAddressFields hook
     * need to add in class "CustomerAddressFormatter.php"
     *
     * @param array $params
     * @return void
     */
    public function hookAdditionalCustomerAddressFields($params)
    {
        $fieldValue = '';
        $idCustomer = $this->context->customer->id;
        if (Tools::getValue('id_address') && $idCustomer) {
            $objTestHelper = new WkTestHelper();
            $customFieldValue = $objTestHelper->getTestFieldValue(Tools::getValue('id_address'), $idCustomer);
            if ($customFieldValue) {
                $fieldValue = $customFieldValue['test_input'];
            }
        }
        $format = array();
        $format['test_input'] = (new FormField())
        ->setName('test_input')
        ->setType('text')
        ->setValue($fieldValue)
        ->setMaxLength(250)
        ->setRequired(false)
        ->setLabel($this->l('Test Field'));

        return $format;
    }

    /**
     * This for validate the additional added field
     * for this need to add a function validateByModules
     * and a hook validateAddressFormFields in class "CustomerAddressForm.php"
     *
     * @param array $params
     * @return void
     */
    public function hookValidateAddressFormFields($params)
    {
        foreach ($params['fields'] as $formField) {
            if ($formField->getValue()) {
                if (!Validate::isName($formField->getValue())) {
                    $formField->addError($this->l('Invalid input.'));
                }
            } else {
                if ($formField->isRequired()) {
                    $formField->addError($this->l('Test Field is required.'));
                }
            }
        }
    }

    /**
     * Here we are saving the additionally added filed in our database
     *
     * @param array $params
     * @return void
     */
    public function hookActionObjectAddressAddAfter($params)
    {
        if (isset($params['object']->test_input) && $params['object']->test_input) {
            $objTestHelper = new WkTestHelper();
            $this->saveFieldValue($objTestHelper, $params['object']);
        }
    }

    /**
     * Here we are updating the additionally added filed in our database
     *
     * @param array $params
     * @return void
     */
    public function hookActionObjectAddressUpdateAfter($params)
    {
        if ($addressId = $params['object']->id) {
            $objTestHelper = new WkTestHelper();
            $detail = $objTestHelper->getTestFieldValue($addressId);
            if ($detail) {
                $objTestHelper = new WkTestHelper($detail['id']);
            }
            if (isset($params['object']->test_input)) {
                $this->saveFieldValue($objTestHelper, $params['object']);
            }
        }
    }

    /**
     * save the field values
     *
     * @param object $objTestHelper
     * @param object $objAddress
     * @return void
     */
    public function saveFieldValue($objTestHelper, $objAddress)
    {
        $objTestHelper->id_customer = (int) $objAddress->id_customer;
        $objTestHelper->id_address = (int) $objAddress->id;
        $objTestHelper->test_input = $objAddress->test_input;
        $objTestHelper->save();
    }

    /**
     * Register the module hook for this module.
     *
     * @return void
     */
    public function registerModuleHook()
    {
        return $this->registerHook(
            array(
                'additionalCustomerAddressFields',
                'validateAddressFormFields',
                'actionObjectAddressAddAfter',
                'actionObjectAddressUpdateAfter'
            )
        );
    }

     /**
     * create corresponding database tables
     * register hook used in this module
     *
     * @return bool if install properly return true else false
     */
    public function install()
    {
        if (!parent::install()
            || !$this->registerModuleHook()
            || !$this->createTable()
        ) {
            return false;
        }

        return true;
    }

     /**
     * Create table create a table for this modules as define in sql file.
     *
     * @return bool if table created properly return true else false
     */
    public function createTable()
    {
        if (!file_exists(dirname(__FILE__).'/'.self::_INSTALL_SQL_FILE_)) {
            return false;
        } elseif (!$sql = Tools::file_get_contents(dirname(__FILE__).'/'.self::_INSTALL_SQL_FILE_)) {
            return false;
        }
        $sql = str_replace(array('PREFIX_',  'ENGINE_TYPE'), array(_DB_PREFIX_, _MYSQL_ENGINE_), $sql);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);
        foreach ($sql as $query) {
            if ($query) {
                if (!Db::getInstance()->execute(trim($query))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Drop all the table created during module installation.
     *
     * @return bool
     */
    public function uninstall()
    {
        if (!parent::uninstall()
            || !$this->deleteTables()
        ) {
            return false;
        }
        return true;
    }

    /**
     * Delete the tables on uninstall the module.
     *
     * @return void
     */
    public function deleteTables()
    {
        return Db::getInstance()->execute(
            'DROP TABLE IF EXISTS
            `'._DB_PREFIX_.'wk_shopping_list`'
        );
    }
}
