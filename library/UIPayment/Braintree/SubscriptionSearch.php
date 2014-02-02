<?php
class UIPayment_Braintree_SubscriptionSearch
{
    static function planId()
    {
        return new UIPayment_Braintree_TextNode("plan_id");
    }

    static function daysPastDue()
    {
        return new UIPayment_Braintree_TextNode("days_past_due");
    }

    static function status()
    {
        return new UIPayment_Braintree_MultipleValueNode("status");
    }

    static function ids()
    {
        return new UIPayment_Braintree_MultipleValueNode("ids");
    }
}
?>
