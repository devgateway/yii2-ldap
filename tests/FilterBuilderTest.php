<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\FilterBuilder as FB;

class FilterBuilderTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testFilterBuilder($type, $input, $output)
    {
        switch($type) {
            case "or":
                $filter = call_user_func_array('devgateway\ldap\FilterBuilder::_or', $input);
                break;
            case "and":
                $filter = call_user_func_array('devgateway\ldap\FilterBuilder::_and', $input);
                break;
            case "not":
                $filter = call_user_func_array('devgateway\ldap\FilterBuilder::_not', $input);
                break;
            case "gte":
                $filter = call_user_func_array('devgateway\ldap\FilterBuilder::_gte', $input);
                break;
            case "lte":
                $filter = call_user_func_array('devgateway\ldap\FilterBuilder::_lte', $input);
                break;
            case "each":
                $filter = call_user_func_array('devgateway\ldap\FilterBuilder::_each', $input);
                break;
            case "either":
                $filter = call_user_func_array('devgateway\ldap\FilterBuilder::_either', $input);
                break;
            case "any":
                $filter = call_user_func_array('devgateway\ldap\FilterBuilder::_any', $input);
                break;
            default:
                throw new \RuntimeException("Invalid Type");
        }
        $this->assertEquals($filter, $output);
    }

    public function provider()
    {
        $output = [
            'any' => "(|(givenName=*adam*)(givenName=*smi*)(surname=*adam*)(surname=*smi*)(uid=*adam*)(uid=*smi*))",
            'either' => "(|(givenName=adam)(surname=adam)(uid=adam))",
            'each' => "(&(givenName=adam)(surname=adam)(uid=adam))",
            'or' => "(|(objectClass=device)(objectClass=user))",
            'and' => "(&(objectClass=device)(objectClass=user))",
            'gte' => "(age>=21)",
            'lte' => "(age<=21)",
            'not' => "(!(drink=liquor))",
            'or_comb' => "(|(&(objectClass=device)(objectClass=user))(&(objectClass=x)(objectClass=y)))",
            'or_comb_two' => "(|(&(|(drink=whiskey)(drink=gin)(drink=rum))(age>=21))(!(drink=liquor)))",
            'and_comb' => "(&(age<=21)(|(drink=whiskey)(drink=gin)(drink=rum)))",
            'and_comb_two' => "(&(objectClass=x)(objectClass=y)(age<=21))"
        ];

        $input = [
            'gte'     => [['age' => 21]],
            'lte'     => [['age' => 21]],
            'not'     => [['drink' => 'liquor']],
            'or'      => [['objectClass' => ['device', 'user']]],
            'and'     => [['objectClass' => ['device', 'user']]],
            'each'    => [['givenName', 'surname', 'uid'], 'adam'],
            'either'  => [['givenName', 'surname', 'uid'], 'adam'],
            'any'     => [['givenName', 'surname', 'uid'], ' adam  smi'],
            'or_comb' => [
                FB::_and(['objectClass' => ['device', 'user']]),
                FB::_and(['objectClass' => ['x', 'y']])
            ],
            'or_comb_two' => [
                FB::_and(
                    FB::_or(['drink' => ['whiskey', 'gin', 'rum']]),
                    FB::_gte(['age' => 21])),
                FB::_not(['drink' => 'liquor'])
            ],
            'and_comb' => [
                FB::_lte(['age' => 21]),
                FB::_or(['drink' => ['whiskey', 'gin', 'rum']])
            ],
            'and_comb_two' => [
                ['objectClass' => ['x', 'y']],
                FB::_lte(['age' => 21])
            ]
        ];

        return [
            'gte'          => ["gte",    $input['gte'],          $output['gte']],
            'lte'          => ["lte",    $input['gte'],          $output['lte']],
            'not'          => ["not",    $input['not'],          $output['not']],
            'or'           => ["or",     $input['or'],           $output['or']],
            'and'          => ["and",    $input['and'],          $output['and']],
            'each'         => ["each",   $input['each'],         $output['each']],
            'either'       => ["either", $input['either'],       $output['either']],
            'any'          => ["any",    $input['any'],          $output['any']],
            'or_comb'      => ["or",     $input['or_comb'],      $output['or_comb']],
            'and_comb'     => ["and",    $input['and_comb'],     $output['and_comb']],
            'or_comb_two'  => ["or",     $input['or_comb_two'],  $output['or_comb_two']],
            'and_comb_two' => ["and",    $input['and_comb_two'], $output['and_comb_two']]
        ];
    }
}

