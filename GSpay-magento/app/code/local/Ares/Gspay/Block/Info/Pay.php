<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@idealiagroup.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category   Ares
 * @package    Ares_Gspay
 * @copyright  Copyright (c) 2014-2016 IDEALIAGroup srl (http://www.sharpmagento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     baohai <287767957@qq.com>
*/
 
class Ares_Gspay_Block_Info_Pay extends Mage_Payment_Block_Info
{
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $info = $this->getInfo();
        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
        /*$transport->addData(array(
            Mage::helper('payment')->__('Check No#') => $info->getCheckNo(),
            Mage::helper('payment')->__('Check Date') => $info->getCheckDate()
        ));*/
        return $transport;
    }
}
