<?php
/***************************************************************************
 *   copyright				: (C) 2008 WeBid
 *   site					: http://www.webidsupport.com/
 ***************************************************************************/

/***************************************************************************
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version. Although none of the code may be
 *   sold. If you have been sold this script, get a refund.
 ***************************************************************************/

include 'includes/common.inc.php';

// If user is not logged in redirect to login page
if (!$user->logged_in)
{
	header('location: user_login.php');
	exit;
}

$query = "SELECT * FROM " . $DBPrefix . "gateways LIMIT 1";
$res = mysql_query($query);
$system->check_mysql($res, $query, __LINE__, __FILE__);
$gayeway_data = mysql_fetch_assoc($res);

switch($_GET['a'])
{
	case 1: // add to account balance
		$pp_paytoemail = $gayeway_data['paypal_address'];
		$payvalue = $system->input_money($_POST['pfval']);
		$custoncode = $user->user_data['id'] . 'WEBID1';
		$message = sprintf($MSG['582'], $payvalue);
		$title = '';
		break;
	case 2:
		$query = "SELECT w.id, a.title, a.shipping_cost, w.bid, u.paypal_email FROM " . $DBPrefix . "auctions a
				LEFT JOIN " . $DBPrefix . "winners w ON (a.id = w.auction)
				LEFT JOIN " . $DBPrefix . "users u ON (u.id = w.seller)
				WHERE a.id = " . intval($_POST['pfval']);
		$res = mysql_query($query);
		$system->check_mysql($res, $query, __LINE__, __FILE__);

		// check its real
		if (mysql_num_rows($res) < 1)
		{
			header('location: outstanding.php');
			exit;
		}

		$data = mysql_fetch_assoc($res);
		$pp_paytoemail = $data['paypal_email'];
		$payvalue = $data['shipping_cost'] + $data['bid'];
		$custoncode = $data['id'] . 'WEBID2';
		$message = sprintf($MSG['581'], $payvalue);
		$title = '';
		break;
}

$template->assign_vars(array(
		'TOP_MESSAGE' => $message,
		'B_ENPAYPAL' => $gayeway_data['paypal_active'],
		'PP_PAYTOEMAIL' => $pp_paytoemail,
		'PAY_VAL' => $payvalue,
		'CURRENCY' => $system->SETTINGS['currency'],
		'TITLE' => $title,
		'CUSTOM_CODE' => $custoncode
		));

include 'header.php';
$template->set_filenames(array(
		'body' => 'pay.tpl'
		));
$template->display('body');
include 'footer.php';
?>