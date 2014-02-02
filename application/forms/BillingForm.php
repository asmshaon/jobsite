<?php
class Form_BillingForm extends Zend_Form
{
    public function init($options = null)
    {
        $cardName = new Zend_Form_Element_Text('card_name');
        $cardName->setLabel('Name on Card')
                ->setRequired(true)                
                ->addFilter('StringTrim')  
                ->setAttrib('class','input')               
                ->addValidator('NotEmpty', true,
                        array('messages' => 'Card Name is required'));

        $cardNumber = new Zend_Form_Element_Text('card_number');
        $cardNumber->setLabel('Card Number')
                ->setRequired(true)
                ->addValidator('Digits', true, array('messages' => 'Please enter valid card number'))
                ->addFilter('StringTrim')
                ->setAttrib('class','input')               
                ->addValidator('NotEmpty', true,
                        array('messages' => 'Card Number is required'));

        $months = array(
            ''   => '-Mohth-',
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December'
        );
        $years = array();
      

        $expMonth = new Zend_Form_Element_Select('exp_month');
        $expMonth->setLabel("Expiration month")
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                        array('messages' => 'Please select a month'));

        foreach ($months as $key => $c) {
            $expMonth->addMultiOption($key, $c);
        }


        $expYear = new Zend_Form_Element_Select('exp_year');
        $expYear->setLabel('Expiration year')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                        array('messages' => 'Please select a year'));

        $years[''] = '-Year-';
        for($i=2010; $i<2030; $i++)
            $years[$i] = $i;

        foreach ($years as $key => $c) {
            $expYear->addMultiOption($key, $c);
        }

        $cvv = new Zend_Form_Element_Text('cvv');
        $cvv->setLabel('CVV')
                ->setRequired(true)
                ->addFilter('StringTrim')
                ->setAttrib('class','input')               
                ->addValidator('Digits', true, array('messages' => 'Please enter valid CVV number'))
                 ->addValidator('stringLength', false, array(3, 4))
                ->addValidator('NotEmpty', true,
                        array('messages' => 'CVV is required'));

        $streetAddress = new Zend_Form_Element_Text('street_address');
        $streetAddress->setLabel('Street Address')
                ->setRequired(true)
                ->setAttrib('class','input')               
                ->addValidator('NotEmpty', true,
                        array('messages' => 'Address is required'));

         $stateList = array(
                ''          => '-State-',
                'AL'        => 'Alabama',
                'AK'        => 'Alaska',
                'AZ'        => 'Arizona',
                'AR'        => 'Arkansas',
                'CA'        => 'California',
                'CO'        => 'Colorado',
                'CT'        => 'Connecticut',
                'DE'        => 'Delaware',
                'DC'        => 'District of Columbia',
                'FL'        => 'Florida',
                'GA'        => 'Georgia',
                'HI'        => 'Hawaii',
                'ID'        => 'Idaho',
                'IL'        => 'Illinois',
                'IN'        => 'Indiana',
                'IA'        => 'Iowa',
                'KS'        => 'Kansas',
                'KY'        => 'Kentucky',
                'LA'        => 'Louisiana',
                'ME'        => 'Maine',
                'MD'        => 'Maryland',
                'MA'        => 'Massachusetts',
                'MI'        => 'Michigan',
                'MN'        => 'Minnesota',
                'MS'        => 'Mississippi',
                'MO'        => 'Missouri',
                'MT'        => 'Montana',
                'NE'        => 'Nebraska',
                'NV'        => 'Nevada',
                'NH'        => 'New Hampshire',
                'NJ'        => 'New Jersey',
                'NM'        => 'New Mexico',
                'NY'        => 'New York',
                'NC'        => 'North Carolina',
                'ND'        => 'North Dakota',
                'OH'        => 'Ohio',
                'OK'        => 'Oklahoma',
                'OR'        => 'Oregon',
                'PA'        => 'Pennsylvania',
                'RI'        => 'Rhode Island',
                'SC'        => 'South Carolina',
                'SD'        => 'South Dakota',
                'TN'        => 'Tennessee',
                'TX'        => 'Texas',
                'UT'        => 'Utah',
                'VT'        => 'Vermont',
                'VA'        => 'Virginia',
                'WA'        => 'Washington',
                'WV'        => 'West Virginia',
                'WI'        => 'Wisconsin',
                'WY'        => 'Wyoming');

        $city = new Zend_Form_Element_Text('city');
        $city->setLabel('City')
                ->setRequired(true)
                ->setAttrib('class','input')               
                ->addValidator('NotEmpty', true,
                        array('messages' => 'City name is required'));

        $state = new Zend_Form_Element_Select('state');
        $state->setLabel('State')
                 ->setRequired(true)
                ->addValidator('NotEmpty', true,
                        array('messages' => 'Please select a state'));

        foreach ($stateList as $key => $c) {
            $state->addMultiOption($key, $c);
        }

        $zip = new Zend_Form_Element_Text('zip');
        $zip->setLabel('Zip')
                ->setRequired(true)
                ->setAttrib('class','input')               
                ->addValidator('NotEmpty', true,
                        array('messages' => 'Zip code is required'));
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Buy Now')
                ->setAttrib('class', 'button_buynow');

        $loginForm = new Zend_Form();
        $this->setName('join-now')
                ->setAttrib('id', 'join-now')
                ->setAttrib('action', 'billing')
                ->addElements(
                        array($cardName, $cardNumber, $expMonth, $expYear, $cvv,
                            $streetAddress, $city, $state ,$zip,
                            $submit)
                        );

    }
}