<?php

class Registration_Model_Billing extends Zend_Db_Table_Abstract {

    /**
     *
     * @param <type> $planId
     * @param <type> $data
     * @return <type>
     */
    public function payment($planId, $data = array()) {
        
        UIPayment_Braintree_Configuration::environment('sandbox');
        UIPayment_Braintree_Configuration::merchantId('7j2btkg8qdkrr854');
        UIPayment_Braintree_Configuration::publicKey('v7gzdtnpc3xnvn4p');
        UIPayment_Braintree_Configuration::privateKey('hxstx5rgjkxphzpg');

        try {
            $customer = UIPayment_Braintree_Customer::createNoValidate(array(
                        'creditCard' => array(
                            'number' => $data['card_number'],
                            'expirationDate' => $data['exp_month'] . '/' . $data['exp_year'],
                            'cvv' => $data['cvv'],
                            'cardholderName' => $data['card_name'],
                            'billingAddress' => array(
                                'streetAddress' => $data['street_address'],
                                'locality' => $data['city'],
                                'region' => $data['state'],
                                'postalCode' => $data['zip'],
                                'countryName' => 'United States of America'
                            )
                        )
                    ));

            $creditCard = $customer->creditCards[0];
            $result = UIPayment_Braintree_Subscription::create(array(
                        'paymentMethodToken' => $creditCard->token,
                        'planId' => trim($planId)
                    ));

            $subscription = $result->subscription;
            $subscriptionId = $subscription->transactions[0]->subscriptionId;

            $customerId = $subscription->transactions[0]->customerDetails->id;

            $transaction = array(
                'subscription_id' => $subscriptionId,
                'customer_id' => $customerId
            );

            return $transaction;

        } catch (Exception $ex) {           
            return false;
        }
    }

    /**
     *
     * @param <type> $subscriptionId
     * @return <type>
     */
    public function cancelSubscription($subscriptionId) {
 
        UIPayment_Braintree_Configuration::environment('sandbox');
        UIPayment_Braintree_Configuration::merchantId('7j2btkg8qdkrr854');
        UIPayment_Braintree_Configuration::publicKey('v7gzdtnpc3xnvn4p');
        UIPayment_Braintree_Configuration::privateKey('hxstx5rgjkxphzpg');

        try {

            $result = UIPayment_Braintree_Subscription::cancel($subscriptionId);

            if ($result->success)
                return true;
            else
                return false;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     *
     * @param <type> $customerId
     * @param <type> $subscriptionId
     * @param <type> $planId
     * @return <type> 
     */
    public function updateSubscription($customerId, $subscriptionId, $planId, $price ,$data = array()) {

        UIPayment_Braintree_Configuration::environment('sandbox');
        UIPayment_Braintree_Configuration::merchantId('7j2btkg8qdkrr854');
        UIPayment_Braintree_Configuration::publicKey('v7gzdtnpc3xnvn4p');
        UIPayment_Braintree_Configuration::privateKey('hxstx5rgjkxphzpg');

        try {

            $customerToken = UIPayment_Braintree_Customer::find($customerId);
            $token         = $customerToken->creditCards[0]->token;

            $customer = UIPayment_Braintree_Customer::update(
                            $customerId,
                            array(
                                'creditCard' => array(
                                    'number' => $data['card_number'],
                                    'expirationDate' => $data['exp_month'] . '/' . $data['exp_year'],
                                    'cvv' => $data['cvv'],
                                    'cardholderName' => $data['card_name'],
                                    'options' => array(
                                        'updateExistingToken' => $token,
                                        'verifyCard' => true
                                    ),

                                    'billingAddress' => array(
                                        'streetAddress' => $data['street_address'],
                                        'locality' => $data['city'],
                                        'region' => $data['state'],
                                        'postalCode' => $data['zip'],
                                        'countryName' => 'United States of America',
                                        'options' => array(
                                            'updateExisting' => true
                                        )
                                    )
                                )
                    ));

            $customerToken = UIPayment_Braintree_Customer::find($customerId);
            $token    = $customerToken->creditCards[0]->token;

            $creditCard = $customer->creditCards[0];
            $result = UIPayment_Braintree_Subscription::update(
                            $subscriptionId,
                            array(
                                'paymentMethodToken' => $token,
                                'planId' => trim($planId),
                                'price'  => $price
                    ));

           if($result->success)
                   return true;
           else
               return false;

        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * 
     */
    public function canceledCustomerSubscription($customerId, $planId, $data = array())
    {
        UIPayment_Braintree_Configuration::environment('sandbox');
        UIPayment_Braintree_Configuration::merchantId('7j2btkg8qdkrr854');
        UIPayment_Braintree_Configuration::publicKey('v7gzdtnpc3xnvn4p');
        UIPayment_Braintree_Configuration::privateKey('hxstx5rgjkxphzpg');

        try {

            $customerToken = UIPayment_Braintree_Customer::find($customerId);
            $token         = $customerToken->creditCards[0]->token;

            $customer = UIPayment_Braintree_Customer::update(
                            $customerId,
                            array(
                                'creditCard' => array(
                                    'number' => $data['card_number'],
                                    'expirationDate' => $data['exp_month'] . '/' . $data['exp_year'],
                                    'cvv' => $data['cvv'],
                                    'cardholderName' => $data['card_name'],
                                    'options' => array(
                                        'updateExistingToken' => $token,
                                        'verifyCard' => true
                                    ),

                                    'billingAddress' => array(
                                        'streetAddress' => $data['street_address'],
                                        'locality' => $data['city'],
                                        'region' => $data['state'],
                                        'postalCode' => $data['zip'],
                                        'countryName' => 'United States of America',
                                        'options' => array(
                                            'updateExisting' => true
                                        )
                                    )
                                )
                    ));

            $customerToken = UIPayment_Braintree_Customer::find($customerId);
            $token    = $customerToken->creditCards[0]->token;

            $result = UIPayment_Braintree_Subscription::create(array(
                        'paymentMethodToken' => $token,
                        'planId' => trim($planId)
                    ));

            $subscription = $result->subscription;
            $subscriptionId = $subscription->transactions[0]->subscriptionId;

            $customerId = $subscription->transactions[0]->customerDetails->id;

            $transaction = array(
                'subscription_id' => $subscriptionId,
                'customer_id' => $customerId
            );

            return $transaction;
        } catch (Exception $ex) {
            return false;
        }
    }

}
?>