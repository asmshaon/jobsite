<?php
/**
 * Address details from a transaction
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Creates an instance of AddressDetails as returned from a transaction
 *
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2010 Braintree Payment Solutions
 * 
 * @property-read string $firstName
 * @property-read string $lastName
 * @property-read string $company
 * @property-read string $streetAddress
 * @property-read string $extendedAddress
 * @property-read string $locality
 * @property-read string $region
 * @property-read string $postalCode
 * @property-read string $countryName
 * @uses UIPayment_Braintree_Instance inherits methods
 */
class UIPayment_Braintree_Transaction_AddressDetails extends UIPayment_Braintree_Instance
{
    protected $_attributes = array();
}
