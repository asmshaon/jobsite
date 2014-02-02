<?php
/**
 * Braintree CreditCard module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Creates and manages Braintree CreditCards
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on CreditCards, see {@link http://www.braintreepaymentsolutions.com/gateway/credit-card-api http://www.braintreepaymentsolutions.com/gateway/credit-card-api}<br />
 * For more detailed information on CreditCard verifications, see {@link http://www.braintreepaymentsolutions.com/gateway/credit-card-verification-api http://www.braintreepaymentsolutions.com/gateway/credit-card-verification-api}
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2010 Braintree Payment Solutions
 *
 * @property-read string $billingAddress
 * @property-read string $bin
 * @property-read string $cardType
 * @property-read string $cardholderName
 * @property-read string $createdAt
 * @property-read string $customerId
 * @property-read string $expirationDate
 * @property-read string $expirationMonth
 * @property-read string $expirationYear
 * @property-read string $last4
 * @property-read string $maskedNumber
 * @property-read string $token
 * @property-read string $updatedAt
 */
class UIPayment_Braintree_CreditCard extends UIPayment_Braintree
{
    // Card Type
    const AMEX = 'American Express';
    const CARTE_BLANCHE = 'Carte Blanche';
    const CHINA_UNION_PAY = 'China UnionPay';
    const DINERS_CLUB_INTERNATIONAL = 'Diners Club';
    const DISCOVER = 'Discover';
    const JCB = 'JCB';
    const LASER = 'Laser';
    const MAESTRO = 'Maestro';
    const MASTER_CARD = 'MasterCard';
    const SOLO = 'Solo';
    const SWITCH_TYPE = 'Switch';
    const VISA = 'Visa';
    const UNKNOWN = 'Unknown';

	// Credit card origination location
	const INTERNATIONAL = "international";
	const US            = "us";

    public static function create($attribs)
    {
        UIPayment_Braintree_Util::verifyKeys(self::createSignature(), $attribs);
        return self::_doCreate('/payment_methods', array('credit_card' => $attribs));
    }

    /**
     * attempts the create operation assuming all data will validate
     * returns a UIPayment_Braintree_CreditCard object instead of a Result
     *
     * @access public
     * @param array $attribs
     * @return object
     * @throws UIPayment_Braintree_Exception_ValidationError
     */
    public static function createNoValidate($attribs)
    {
        $result = self::create($attribs);
        return self::returnObjectOrThrowException(__CLASS__, $result);
    }
    /**
     * create a customer from a TransparentRedirect operation
     *
     * @access public
     * @param array $attribs
     * @return object
     */
    public static function createFromTransparentRedirect($queryString)
    {
        trigger_error("DEPRECATED: Please use UIPayment_Braintree_TransparentRedirectRequest::confirm", E_USER_NOTICE);
        $params = UIPayment_Braintree_TransparentRedirect::parseAndValidateQueryString(
            $queryString
        );
        return self::_doCreate(
            '/payment_methods/all/confirm_transparent_redirect_request',
            array('id' => $params['id'])
        );
    }

    /**
     *
     * @access public
     * @param none
     * @return string
     */
    public static function createCreditCardUrl()
    {
        trigger_error("DEPRECATED: Please use UIPayment_Braintree_TransparentRedirectRequest::url", E_USER_NOTICE);
        return UIPayment_Braintree_Configuration::merchantUrl() .
                '/payment_methods/all/create_via_transparent_redirect_request';
    }

    /**
     * returns a ResourceCollection of expired credit cards
     * @return object ResourceCollection
     */
    public static function expired()
    {
        $response = UIPayment_Braintree_Http::post("/payment_methods/all/expired_ids");
        $pager = array(
            'className' => __CLASS__,
            'classMethod' => 'fetchExpired',
            'methodArgs' => array()
        );

        return new UIPayment_Braintree_ResourceCollection($response, $pager);
    }

    public static function fetchExpired($ids)
    {
        $response = UIPayment_Braintree_Http::post("/payment_methods/all/expired", array('search' => array('ids' => $ids)));

        return braintree_util::extractattributeasarray(
            $response['paymentMethods'],
            'creditCard'
        );
    }
    /**
     * returns a ResourceCollection of credit cards expiring between start/end
     *
     * @return object ResourceCollection
     */
    public static function expiringBetween($startDate, $endDate)
    {
        $queryPath = '/payment_methods/all/expiring_ids?start=' . date('mY', $startDate) . '&end=' . date('mY', $endDate);
        $response = UIPayment_Braintree_Http::post($queryPath);
        $pager = array(
            'className' => __CLASS__,
            'classMethod' => 'fetchExpiring',
            'methodArgs' => array($startDate, $endDate)
        );

        return new UIPayment_Braintree_ResourceCollection($response, $pager);
    }

    public static function fetchExpiring($startDate, $endDate, $ids)
    {
        $queryPath = '/payment_methods/all/expiring?start=' . date('mY', $startDate) . '&end=' . date('mY', $endDate);
        $response = UIPayment_Braintree_Http::post($queryPath, array('search' => array('ids' => $ids)));

        return UIPayment_Braintree_Util::extractAttributeAsArray(
            $response['paymentMethods'],
            'creditCard'
        );
    }

    /**
     * find a creditcard by token
     *
     * @access public
     * @param string $token credit card unique id
     * @return object UIPayment_Braintree_CreditCard
     * @throws UIPayment_Braintree_Exception_NotFound
     */
    public static function find($token)
    {
        self::_validateId($token);
        try {
            $response = UIPayment_Braintree_Http::get('/payment_methods/'.$token);
            return self::factory($response['creditCard']);
        } catch (UIPayment_Braintree_Exception_NotFound $e) {
            throw new UIPayment_Braintree_Exception_NotFound(
                'credit card with token ' . $token . ' not found'
            );
        }

    }

   /**
     * create a credit on the card for the passed transaction
     *
     * @access public
     * @param array $attribs
     * @return object UIPayment_Braintree_Result_Successful or UIPayment_Braintree_Result_Error
     */
    public static function credit($token, $transactionAttribs)
    {
        self::_validateId($token);
        return UIPayment_Braintree_Transaction::credit(
            array_merge(
                $transactionAttribs,
                array('paymentMethodToken' => $token)
            )
        );
    }

    /**
     * create a credit on this card, assuming validations will pass
     *
     * returns a UIPayment_Braintree_Transaction object on success
     *
     * @access public
     * @param array $attribs
     * @return object UIPayment_Braintree_Transaction
     * @throws UIPayment_Braintree_Exception_ValidationError
     */
    public static function creditNoValidate($token, $transactionAttribs)
    {
        $result = self::credit($token, $transactionAttribs);
        return self::returnObjectOrThrowException('Transaction', $result);
    }

    /**
     * create a new sale for the current card
     *
     * @param string $token
     * @param array $transactionAttribs
     * @return object UIPayment_Braintree_Result_Successful or UIPayment_Braintree_Result_Error
     * @see UIPayment_Braintree_Transaction::sale()
     */
    public static function sale($token, $transactionAttribs)
    {
        self::_validateId($token);
        return UIPayment_Braintree_Transaction::sale(
            array_merge(
                $transactionAttribs,
                array('paymentMethodToken' => $token)
            )
        );
    }

    /**
     * create a new sale using this card, assuming validations will pass
     *
     * returns a UIPayment_Braintree_Transaction object on success
     *
     * @access public
     * @param array $transactionAttribs
     * @param string $token
     * @return object UIPayment_Braintree_Transaction
     * @throws UIPayment_Braintree_Exception_ValidationsFailed
     * @see UIPayment_Braintree_Transaction::sale()
     */
    public static function saleNoValidate($token, $transactionAttribs)
    {
        $result = self::sale($token, $transactionAttribs);
        return self::returnObjectOrThrowException('Transaction', $result);
    }

    /**
     * updates the creditcard record
     *
     * if calling this method in static context, $token
     * is the 2nd attribute. $token is not sent in object context.
     *
     * @access public
     * @param array $attributes
     * @param string $token (optional)
     * @return object UIPayment_Braintree_Result_Successful or UIPayment_Braintree_Result_Error
     */
    public static function update($token, $attributes)
    {
        UIPayment_Braintree_Util::verifyKeys(self::updateSignature(), $attributes);
        self::_validateId($token);
        return self::_doUpdate('put', '/payment_methods/' . $token, array('creditCard' => $attributes));
    }

    /**
     * update a creditcard record, assuming validations will pass
     *
     * if calling this method in static context, $token
     * is the 2nd attribute. $token is not sent in object context.
     * returns a UIPayment_Braintree_CreditCard object on success
     *
     * @access public
     * @param array $attributes
     * @param string $token
     * @return object UIPayment_Braintree_CreditCard
     * @throws UIPayment_Braintree_Exception_ValidationsFailed
     */
    public static function updateNoValidate($token, $attributes)
    {
        $result = self::update($token, $attributes);
        return self::returnObjectOrThrowException(__CLASS__, $result);
    }
    /**
     *
     * @access public
     * @param none
     * @return string
     */
    public static function updateCreditCardUrl()
    {
        trigger_error("DEPRECATED: Please use UIPayment_Braintree_TransparentRedirectRequest::url", E_USER_NOTICE);
        return UIPayment_Braintree_Configuration::merchantUrl() .
                '/payment_methods/all/update_via_transparent_redirect_request';
    }

    /**
     * update a customer from a TransparentRedirect operation
     *
     * @access public
     * @param array $attribs
     * @return object
     */
    public static function updateFromTransparentRedirect($queryString)
    {
        trigger_error("DEPRECATED: Please use UIPayment_Braintree_TransparentRedirectRequest::confirm", E_USER_NOTICE);
        $params = UIPayment_Braintree_TransparentRedirect::parseAndValidateQueryString(
            $queryString
        );
        return self::_doUpdate(
            'post',
            '/payment_methods/all/confirm_transparent_redirect_request',
            array('id' => $params['id'])
        );
    }

    /* instance methods */
    /**
     * returns false if default is null or false
     *
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * checks whether the card is expired based on the current date
     *
     * @return boolean
     */
    public function isExpired()
    {
        return $this->expired;
    }

    public static function delete($token)
    {
        self::_validateId($token);
        UIPayment_Braintree_Http::delete('/payment_methods/' . $token);
        return new UIPayment_Braintree_Result_Successful();
    }

    /**
     * sets instance properties from an array of values
     *
     * @access protected
     * @param array $creditCardAttribs array of creditcard data
     * @return none
     */
    protected function _initialize($creditCardAttribs)
    {
        // set the attributes
        $this->_attributes = $creditCardAttribs;

        // map each address into its own object
        $billingAddress = isset($creditCardAttribs['billingAddress']) ?
            UIPayment_Braintree_Address::factory($creditCardAttribs['billingAddress']) :
            null;

        $subscriptionArray = array();
        if (isset($creditCardAttribs['subscriptions'])) {
            foreach ($creditCardAttribs['subscriptions'] AS $subscription) {
                $subscriptionArray[] = UIPayment_Braintree_Subscription::factory($subscription);
            }
        }

        $this->_set('subscriptions', $subscriptionArray);
        $this->_set('billingAddress', $billingAddress);
        $this->_set('expirationDate', $this->expirationMonth . '/' . $this->expirationYear);
        $this->_set('maskedNumber', $this->bin . '******' . $this->last4);
    }

    /**
     * returns false if comparing object is not a UIPayment_Braintree_CreditCard,
     * or is a UIPayment_Braintree_CreditCard with a different id
     *
     * @param object $otherCreditCard customer to compare against
     * @return boolean
     */
    public function isEqual($otherCreditCard)
    {
        return !($otherCreditCard instanceof UIPayment_Braintree_CreditCard) ? false : $this->token === $otherCreditCard->token;
    }

   public static function createSignature()
   {
        return array(
            'customerId', 'cardholderName', 'cvv', 'number',
            'expirationDate', 'expirationMonth', 'expirationYear', 'token',
            array('options' => array('makeDefault', 'verificationMerchantAccountId', 'verifyCard')),
            array(
                'billingAddress' => array(
                    'firstName',
                    'lastName',
                    'company',
                    'countryName',
                    'extendedAddress',
                    'locality',
                    'region',
                    'postalCode',
                    'streetAddress'
                ),
            ),
        );
   }
   public static function updateSignature()
   {
        $signature = self::createSignature();

        $updateExistingBillingSignature = array(
            array(
                'options' => array(
                    'updateExisting'
                )
            )
        );

        foreach($signature AS $key => $value) {
            if(is_array($value) and array_key_exists('billingAddress', $value)) {
                $signature[$key]['billingAddress'] = array_merge_recursive($value['billingAddress'], $updateExistingBillingSignature);
            }
        }

        // return all but the customerId (the first element)
        return array_slice($signature, 1);
   }

    /**
     * sends the create request to the gateway
     *
     * @ignore
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public static function _doCreate($url, $params)
    {
        $response = UIPayment_Braintree_Http::post($url, $params);

        return self::_verifyGatewayResponse($response);
    }

    /**
     * create a printable representation of the object as:
     * ClassName[property=value, property=value]
     * @return string
     */
    public function  __toString()
    {
        $objOutput = UIPayment_Braintree_Util::implodeAssociativeArray($this->_attributes);
        return __CLASS__ . '[' . $objOutput . ']';
    }

    /**
     * verifies that a valid credit card token is being used
     * @ignore
     * @param string $token
     * @throws InvalidArgumentException
     */
    private static function _validateId($token = null)
    {
        if (empty($token)) {
           throw new InvalidArgumentException(
                   'expected address id to be set'
                   );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $token)) {
            throw new InvalidArgumentException(
                    $token . ' is an invalid address id.'
                    );
        }
    }
    /**
     * sets private properties
     * this function is private so values are read only
     * @ignore
     * @access protected
     * @param string $key
     * @param mixed $value
     */
    private function _set($key, $value)
    {
        $this->_attributes[$key] = $value;
    }

     /* private class methods */

    /**
     * sends the update request to the gateway
     *
     * @ignore
     * @param string $url
     * @param array $params
     * @return mixed
     */
    private static function _doUpdate($httpVerb, $url, $params)
    {
        $response = UIPayment_Braintree_Http::$httpVerb($url, $params);
        return self::_verifyGatewayResponse($response);
    }

    /**
     * generic method for validating incoming gateway responses
     *
     * creates a new UIPayment_Braintree_CreditCard object and encapsulates
     * it inside a UIPayment_Braintree_Result_Successful object, or
     * encapsulates a UIPayment_Braintree_Errors object inside a Result_Error
     * alternatively, throws an Unexpected exception if the response is invalid.
     *
     * @ignore
     * @param array $response gateway response values
     * @return object Result_Successful or Result_Error
     * @throws UIPayment_Braintree_Exception_Unexpected
     */
    private static function _verifyGatewayResponse($response)
    {
        if (isset($response['creditCard'])) {
            // return a populated instance of UIPayment_Braintree_Address
            return new UIPayment_Braintree_Result_Successful(
                    self::factory($response['creditCard'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new UIPayment_Braintree_Result_Error($response['apiErrorResponse']);
        } else {
            throw new UIPayment_Braintree_Exception_Unexpected(
            "Expected address or apiErrorResponse"
            );
        }
    }

    /**
     *  factory method: returns an instance of UIPayment_Braintree_CreditCard
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of UIPayment_Braintree_CreditCard
     */
    public static function factory($attributes)
    {
        $defaultAttributes = array(
            'bin' => '',
            'expirationMonth'    => '',
            'expirationYear'    => '',
            'last4'  => '',
        );

        $instance = new self();
        $instance->_initialize(array_merge($defaultAttributes, $attributes));
        return $instance;
    }
}
