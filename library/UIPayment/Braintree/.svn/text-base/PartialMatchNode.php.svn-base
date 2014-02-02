<?php

class UIPayment_Braintree_PartialMatchNode extends UIPayment_Braintree_EqualityNode
{
    function startsWith($value)
    {
        $this->searchTerms["starts_with"] = strval($value);
        return $this;
    }

    function endsWith($value)
    {
        $this->searchTerms["ends_with"] = strval($value);
        return $this;
    }
}
?>
