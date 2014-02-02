<?php
/**
 * Braintree Subscription module
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on Subscriptions, see {@link http://www.braintreepaymentsolutions.com/gateway/subscription-api http://www.braintreepaymentsolutions.com/gateway/subscription-api}
 *
 * PHP Version 5
 *
 * @package   Braintree
 * @copyright 2010 Braintree Payment Solutions
 */
class UIPayment_Braintree_Subscription extends UIPayment_Braintree
{
    const ACTIVE = 'active';
    const CANCELED = 'canceled';
    const PAST_DUE = 'past_due';

    public static function create($attributes)
    {
        UIPayment_Braintree_Util::verifyKeys(self::allowedAttributes(), $attributes);
        $response = UIPayment_Braintree_Http::post('/subscriptions', array('subscription' => $attributes));
        return self::_verifyGatewayResponse($response);
    }

    /**
     * @ignore
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);

        return $instance;
    }

    public static function find($id)
    {
        try {
            $response = UIPayment_Braintree_Http::get('/subscriptions/' . $id);
            return self::factory($response['subscription']);
        } catch (UIPayment_Braintree_Exception_NotFound $e) {
            throw new UIPayment_Braintree_Exception_NotFound('subscription with id ' . $id . ' not found');
        }

    }

    public static function search($query)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }

        $response = braintree_http::post('/subscriptions/advanced_search_ids', array('search' => $criteria));
        $pager = array(
            'className' => __CLASS__,
            'classMethod' => 'fetch',
            'methodArgs' => array($query)
            );

        return new UIPayment_Braintree_ResourceCollection($response, $pager);
    }

    public static function fetch($query, $ids)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }
        $criteria["ids"] = UIPayment_Braintree_SubscriptionSearch::ids()->in($ids)->toparam();
        $response = braintree_http::post('/subscriptions/advanced_search', array('search' => $criteria));

        return braintree_util::extractattributeasarray(
            $response['subscriptions'],
            'subscription'
        );
    }

    public static function update($subscriptionId, $attributes)
    {
        UIPayment_Braintree_Util::verifyKeys(self::allowedAttributes(), $attributes);
        $response = UIPayment_Braintree_Http::put(
            '/subscriptions/' . $subscriptionId,
            array('subscription' => $attributes)
        );
        return self::_verifyGatewayResponse($response);
    }

    public static function retryCharge($subscriptionId, $amount = null)
    {
        $transaction_params = array('type' => UIPayment_Braintree_Transaction::SALE,
            'subscriptionId' => $subscriptionId);
        if (isset($amount)) {
            $transaction_params['amount'] = $amount;
        }

        $response = UIPayment_Braintree_Http::post(
            '/transactions',
            array('transaction' => $transaction_params));
        return self::_verifyGatewayResponse($response);
    }

    public static function cancel($subscriptionId)
    {
        $response = UIPayment_Braintree_Http::put('/subscriptions/' . $subscriptionId . '/cancel');
        return self::_verifyGatewayResponse($response);
    }

    private static function allowedAttributes()
    {
        return array(
            'merchantAccountId', 'paymentMethodToken', 'planId', 'id', 'price', 'trialPeriod',
            'trialDuration', 'trialDurationUnit'
        );
    }

    /**
     * @ignore
     */
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;

        $transactionArray = array();
        if (isset($attributes['transactions'])) {
            foreach ($attributes['transactions'] AS $transaction) {
                $transactionArray[] = UIPayment_Braintree_Transaction::factory($transaction);
            }
        }
        $this->_attributes['transactions'] = $transactionArray;
    }

    /**
     * @ignore
     */
    private static function _verifyGatewayResponse($response)
    {
        if (isset($response['subscription'])) {
            return new UIPayment_Braintree_Result_Successful(
                self::factory($response['subscription'])
            );
        } else if (isset($response['transaction'])) {
            // return a populated instance of UIPayment_Braintree_Transaction, for subscription retryCharge
            return new UIPayment_Braintree_Result_Successful(
                UIPayment_Braintree_Transaction::factory($response['transaction'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new UIPayment_Braintree_Result_Error($response['apiErrorResponse']);
        }
    }
}
?>
