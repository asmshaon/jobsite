<?php

class UIPayment_Braintree_TextNode extends UIPayment_Braintree_PartialMatchNode
{
    function contains($value)
    {
        $this->searchTerms["contains"] = strval($value);
        return $this;
    }
}
?>
