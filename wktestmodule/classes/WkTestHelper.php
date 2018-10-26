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

class WkTestHelper extends ObjectModel
{
    public $id_customer;
    public $id_address;
    public $test_input;

    /**
     * define the table column
     *
     * @var array
     */
    public static $definition = array(
        'table' => 'wk_test_table',
        'primary' => 'id',
        'fields' => array(
            'id_address' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'test_input' => array('type' => self::TYPE_STRING),
        ),
    );

    /**
     * test function to get the test field record
     *
     * @param int $idAddress
     * @param boolean $idCustomer
     * @return void
     */
    public function getTestFieldValue($idAddress, $idCustomer = false)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('wk_test_table');
        $sql->where('id_address = '.(int)$idAddress);
        if ($idCustomer) {
            $sql->where('id_customer = '.(int)$idCustomer);
        }

        return Db::getInstance()->getRow($sql);
    }
}
