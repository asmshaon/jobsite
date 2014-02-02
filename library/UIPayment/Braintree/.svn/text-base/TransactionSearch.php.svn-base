<?php
class UIPayment_Braintree_TransactionSearch
{
	static function billingCompany()             { return new UIPayment_Braintree_TextNode('billing_company'); }
	static function billingCountryName()         { return new UIPayment_Braintree_TextNode('billing_country_name'); }
	static function billingExtendedAddress()     { return new UIPayment_Braintree_TextNode('billing_extended_address'); }
	static function billingFirstName()           { return new UIPayment_Braintree_TextNode('billing_first_name'); }
	static function billingLastName()            { return new UIPayment_Braintree_TextNode('billing_last_name'); }
	static function billingLocality()            { return new UIPayment_Braintree_TextNode('billing_locality'); }
	static function billingPostalCode()          { return new UIPayment_Braintree_TextNode('billing_postal_code'); }
	static function billingRegion()              { return new UIPayment_Braintree_TextNode('billing_region'); }
	static function billingStreetAddress()       { return new UIPayment_Braintree_TextNode('billing_street_address'); }
	static function creditCardCardholderName()   { return new UIPayment_Braintree_TextNode('credit_card_cardholderName'); }
	static function customerCompany()            { return new UIPayment_Braintree_TextNode('customer_company'); }
	static function customerEmail()              { return new UIPayment_Braintree_TextNode('customer_email'); }
	static function customerFax()                { return new UIPayment_Braintree_TextNode('customer_fax'); }
	static function customerFirstName()          { return new UIPayment_Braintree_TextNode('customer_first_name'); }
	static function customerId()                 { return new UIPayment_Braintree_TextNode('customer_id'); }
	static function customerLastName()           { return new UIPayment_Braintree_TextNode('customer_last_name'); }
	static function customerPhone()              { return new UIPayment_Braintree_TextNode('customer_phone'); }
	static function customerWebsite()            { return new UIPayment_Braintree_TextNode('customer_website'); }
	static function id()                         { return new UIPayment_Braintree_TextNode('id'); }
	static function ids()                        { return new UIPayment_Braintree_MultipleValueNode('ids'); }
	static function orderId()                    { return new UIPayment_Braintree_TextNode('order_id'); }
	static function paymentMethodToken()         { return new UIPayment_Braintree_TextNode('payment_method_token'); }
	static function processorAuthorizationCode() { return new UIPayment_Braintree_TextNode('processor_authorization_code'); }
	static function shippingCompany()            { return new UIPayment_Braintree_TextNode('shipping_company'); }
	static function shippingCountryName()        { return new UIPayment_Braintree_TextNode('shipping_country_name'); }
	static function shippingExtendedAddress()    { return new UIPayment_Braintree_TextNode('shipping_extended_address'); }
	static function shippingFirstName()          { return new UIPayment_Braintree_TextNode('shipping_first_name'); }
	static function shippingLastName()           { return new UIPayment_Braintree_TextNode('shipping_last_name'); }
	static function shippingLocality()           { return new UIPayment_Braintree_TextNode('shipping_locality'); }
	static function shippingPostalCode()         { return new UIPayment_Braintree_TextNode('shipping_postal_code'); }
	static function shippingRegion()             { return new UIPayment_Braintree_TextNode('shipping_region'); }
	static function shippingStreetAddress()      { return new UIPayment_Braintree_TextNode('shipping_street_address'); }

	static function creditCardExpirationDate()   { return new UIPayment_Braintree_EqualityNode('credit_card_expiration_date'); }

	static function creditCardNumber()           { return new UIPayment_Braintree_PartialMatchNode('credit_card_number'); }

	static function refund()                     { return new UIPayment_Braintree_KeyValueNode("refund"); }

	static function amount()                     { return new UIPayment_Braintree_RangeValueNode("amount"); }
	static function createdAt()                  { return new UIPayment_Braintree_RangeValueNode("createdAt"); }

    static function merchantAccountId()          { return new UIPayment_Braintree_MultipleValueNode("merchant_account_id"); }

    static function createdUsing()
    {
        return new UIPayment_Braintree_MultipleValueNode("created_using", array(
            UIPayment_Braintree_Transaction::FULL_INFORMATION,
            UIPayment_Braintree_Transaction::TOKEN
        ));
    }

    static function creditCardCardType()
    {
        return new UIPayment_Braintree_MultipleValueNode("credit_card_card_type", array(
            UIPayment_Braintree_CreditCard::AMEX,
            UIPayment_Braintree_CreditCard::CARTE_BLANCHE,
            UIPayment_Braintree_CreditCard::CHINA_UNION_PAY,
            UIPayment_Braintree_CreditCard::DINERS_CLUB_INTERNATIONAL,
            UIPayment_Braintree_CreditCard::DISCOVER,
            UIPayment_Braintree_CreditCard::JCB,
            UIPayment_Braintree_CreditCard::LASER,
            UIPayment_Braintree_CreditCard::MAESTRO,
            UIPayment_Braintree_CreditCard::MASTER_CARD,
            UIPayment_Braintree_CreditCard::SOLO,
            UIPayment_Braintree_CreditCard::SWITCH_TYPE,
            UIPayment_Braintree_CreditCard::VISA,
            UIPayment_Braintree_CreditCard::UNKNOWN
        ));
    }

    static function creditCardCustomerLocation()
    {
        return new UIPayment_Braintree_MultipleValueNode("credit_card_customer_location", array(
            UIPayment_Braintree_CreditCard::INTERNATIONAL,
            UIPayment_Braintree_CreditCard::US
        ));
    }

    static function source()
    {
        return new UIPayment_Braintree_MultipleValueNode("source", array(
            UIPayment_Braintree_Transaction::API,
            UIPayment_Braintree_Transaction::CONTROL_PANEL,
            UIPayment_Braintree_Transaction::RECURRING,
        ));
    }

    static function status()
    {
        return new UIPayment_Braintree_MultipleValueNode("status", array(
            UIPayment_Braintree_Transaction::AUTHORIZING,
            UIPayment_Braintree_Transaction::AUTHORIZED,
            UIPayment_Braintree_Transaction::GATEWAY_REJECTED,
            UIPayment_Braintree_Transaction::FAILED,
            UIPayment_Braintree_Transaction::PROCESSOR_DECLINED,
            UIPayment_Braintree_Transaction::SETTLED,
            UIPayment_Braintree_Transaction::SETTLEMENT_FAILED,
            UIPayment_Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT,
            UIPayment_Braintree_Transaction::UNKNOWN,
            UIPayment_Braintree_Transaction::VOIDED
        ));
    }

    static function type()
    {
        return new UIPayment_Braintree_MultipleValueNode("type", array(
            UIPayment_Braintree_Transaction::SALE,
            UIPayment_Braintree_Transaction::CREDIT
        ));
    }
}
?>
