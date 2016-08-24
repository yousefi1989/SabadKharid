<?php
/*****************************************************************************
 *                                                                           *
 * Zarinpal Payment Gateway                                                  *
 * Copyright (c) 2009 . All rights reserved.                                 *
 *                                                                           *
 *****************************************************************************/
	// ZARINPAL payment module

/**
 * @connect_module_class_name CZARINPALZG
 *
 */

class CZARINPALWG extends PaymentModule{
	
	function _initVars(){
		
		$this->title 		= CZARINPALWG_TTL;
		$this->description 	= CZARINPALWG_DSCR;
		$this->sort_order 	= 1;
		$this->Settings = array( 
			"CONF_PAYMENTMODULE_ZARINPAL_MERCHANT_ACCOUNT",
			"CONF_PAYMENTMODULE_ZARINPAL_RIAL_CURRENCY"
			);
	}

	function after_processing_html( $orderID ) 
	{
		
		$order = ordGetOrder( $orderID );
		if ( $this->_getSettingValue('CONF_PAYMENTMODULE_ZARINPAL_RIAL_CURRENCY') > 0 )
		{
			$PAcurr = currGetCurrencyByID ( $this->_getSettingValue('CONF_PAYMENTMODULE_ZARINPAL_RIAL_CURRENCY') );
			$PAcurr_rate = $PAcurr["currency_value"];
		}
		if (!isset($PAcurr) || !$PAcurr)
		{
			$PAcurr_rate = 1;
		}
		$order_amount = round(100*$order["order_amount"] * $PAcurr_rate)/100;
		$modID =  $this ->get_id();
		
		$soapclient = new SoapClient('https://de.zarinpal.com/pg/services/WebGate/wsdl');
		$amount = $order_amount;  // here is the posted amount

		$callbackUrl = CONF_FULL_SHOP_URL."?zarinpalwg&modID=$modID&pay=1";
		$pin = $this->_getSettingValue('CONF_PAYMENTMODULE_ZARINPAL_MERCHANT_ACCOUNT');

		$res = $soapclient->PaymentRequest(
		array(
					'MerchantID' 	=> $pin ,
					'Amount' 		=> $amount ,
					'Description' 	=> 'پرداخت سفارش شماره: '.$orderID ,
					'Email' 		=> '' ,
					'Mobile' 		=> '' ,
					'CallbackURL' 	=> $callbackUrl
					)
		
		 );

		if ( $res->Status == 100 ) {
		   // this is a succcessfull connection
			db_query( "update ".ORDERS_TABLE." set refnum='".$res->Authority."' where orderID='".$orderID."'");
			$parsURL = "https://www.zarinpal.com/pg/StartPay/" . $res->Authority . "/" ;
			header("Location:". $parsURL) ;
			exit() ;
			die() ;
			return;

		} else {
		   // this is unsucccessfull connection
			echo "<p align=center>
					err1<br />
					$res->Status <br />
					$orderID <br />
					UNSUCCSESSFUL!
					$pin <br />
					$amount <br />
					$callbackUrl <br />
				  </p>";

		}
	}

	function _initSettingFields(){
		
		$this->SettingsFields['CONF_PAYMENTMODULE_ZARINPAL_MERCHANT_ACCOUNT'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CZARINPALWG_CFG_MERCHANT_ACCOUNT_TTL, 
			'settings_description' 	=> CZARINPALWG_CFG_MERCHANT_ACCOUNT_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);

		$this->SettingsFields['CONF_PAYMENTMODULE_ZARINPAL_RIAL_CURRENCY'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> CZARINPALWG_CFG_RIAL_CURRENCY_TTL, 
			'settings_description' 	=> CZARINPALWG_CFG_RIAL_CURRENCY_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 1,
		);
	}
}
?>
