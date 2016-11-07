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
 
class Ares_Gspay_Block_Start extends Mage_Core_Block_Abstract
{
	protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('gspay/start.phtml');
    }
	
	protected function _toHtml()
	{
		
		$alipay = Mage::getModel('gspay/pay');
        $form = new Varien_Data_Form();
        $form->setAction($alipay->getAlipayUrl())
				->setId('gspaysubmit')
				->setName('gspaysubmit')
				->setMethod('POST')
				->setUseContainer(true);
        
        $alipay->setOrder($this->getOrder());
		$paramFields = $alipay->getAlipayParamFields();

        foreach ( $paramFields as $field => $value )
		{
            $form->addField( 
				$field, 'hidden',
				array(
					'name' => $field,
					'value' => htmlspecialchars($value)
				)
			);
        }		

        $formHTML = $form->toHtml();

        $html = '<div class="gspay-submit">';
		/* ----- Start From ----- */
        //$html.= '<h1>'.$this->__('System is being processed...').'</h1>';
		$html.= '<div class="form-content">';
        $html.= $formHTML;
		$html.= '<script type="text/javascript">document.forms["gspaysubmit"].submit();</script>';
		$html.= '</div>';
		/* ----- End From ----- */
        $html.= '<div class="clear"></div></div>';
		
        return $html;
    }
	
}
