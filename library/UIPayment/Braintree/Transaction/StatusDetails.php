<?php
/**
 * Status details from a transaction
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Creates an instance of StatusDetails, as part of a transaction response
 *
 * @package    Braintree
 * @copyright  2010 Braintree Payment Solutions
 * 
 * @property-read string $amount
 * @property-read string $status
 * @property-read string $timestamp
 * @property-read string $transactionSource
 * @property-read string $user
 * @uses UIPayment_Braintree_Instance inherits methods
 */
class UIPayment_Braintree_Transaction_StatusDetails extends UIPayment_Braintree_Instance
{
}
