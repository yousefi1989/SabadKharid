<?php 
/*****************************************************************************
 *                                                                           *
 * www.sabadkharid.com                                                       *
 * Copyright (c) 2008 . All rights reserved.                                 *
 *                                                                           *
 *****************************************************************************/

	// show aux page

	if ( isset($_GET['zarinpalzg'] ))
	{

		if (isset($_GET['modID']) && isset($_GET['Authority'])) {
			
			$modID = $_GET['modID'];
			$authority = $_GET['Authority'];
			$orderID = ordGetordIDbyRefnum($authority);
			$q = db_query("SELECT * FROM ".SETTINGS_TABLE." WHERE settings_constant_name='CONF_PAYMENTMODULE_ZARINPAL_MERCHANT_ACCOUNT_$modID'");
			$res = db_fetch_row($q);
			$comStatID = _getSettingOptionValue('CONF_COMPLETED_ORDER_STATUS');
				
			
			if (!empty($res['settings_value'])) {
				$mid = $res['settings_value'];
			} else {
				Redirect( "index.php" );
			}
		

			$order =_getOrderById($orderID);
			
			if($order['StatusID'] != $comStatID){
			
				
				if ($orderID && $_GET['Status'] == "OK") {
					$order['order_amount'] = round($order['order_amount']);

					$soapclient = new SoapClient('https://de.zarinpal.com/pg/services/WebGate/wsdl');

						  $result = $soapclient->PaymentVerification(
						  array(
								'MerchantID'	 => $mid ,
								'Authority' 	 => $authority ,
								'Amount'		 => $order['order_amount']
								)
						  
						  );
						  if ($result->Status == 100 ) {
								// this is a succcessfull payment

								$pininfo = ostSetOrderStatusToOrder($orderID, $comStatID, 'Your Online Payment with ZARINPAL gateway accepted', 1);
								
								$body =  STR_SHETAB_THANKS.'<br>';
								$body .= STR_SHETAB_REFNUM.': '.$authority.'<br>';
								$body .= $pininfo;
	//							echo 'SUCSSESSFULL STATUS IS====>'.$status;
								
								
						  } else {
	//					  	echo 'HERE IS ELSE';
							   // this is a UNsucccessfull payment
								ostSetOrderStatusToOrder($orderID, 1);
								$body =	ERROR_SHETAB_19;
								echo 'ERR STATUS====>'.$res->Status ;
						  }

				}
			    else {
				$body =	ERROR_SHETAB_19;
//				echo 'NO ORDERID';
			    }
					
				
			
			}else {
				if ($orderID) {
				ostSetOrderStatusToOrder($orderID, 1);
				}
				$body =	ERROR_SHETAB_19;
//				echo 'URL RS NOT 0';
//				echo 'ORDER IS COMPLETED';
			
			}
		
		
			$smarty->assign("page_body", $body );
			$smarty->assign("main_content_template", "zarinpal.tpl.html" );
		}
		else
		{
			$smarty->assign("main_content_template", "page_not_found.tpl.html" );
		}
}

?>
