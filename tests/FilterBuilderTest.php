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
            case "neither":
                $filter = call_user_func_array('devgateway\ldap\FilterBuilder::neither', $input);
                break;
            case "gte":
                $filter = call_user_func_array('devgateway\ldap\FilterBuilder::_gte', $input);
                break;
            case "lte":
                $filter = call_user_func_array('devgateway\ldap\FilterBuilder::_lte', $input);
                break;
            case "all":
                $filter = call_user_func_array('devgateway\ldap\FilterBuilder::all', $input);
                break;
            case "either":
                $filter = call_user_func_array('devgateway\ldap\FilterBuilder::either', $input);
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
            'neither' => "(!(|(drink=liquor)(drink=water)))",
            'or_comb' => "(|(&(objectClass=device)(objectClass=user))(&(objectClass=x)(objectClass=y)))",
            'or_comb_two' => "(|(&(|(drink=whiskey)(drink=gin)(drink=rum))(age>=21))(!(drink=liquor)))",
            'and_comb' => "(&(age<=21)(|(drink=whiskey)(drink=gin)(drink=rum)))",
            'and_comb_two' => "(&(objectClass=x)(objectClass=y)(age<=21))"
        ];

        $input = [
            'gte'     => [['age' => 21]],
            'lte'     => [['age' => 21]],
            'not'     => [['drink' => 'liquor']],
            'neither' => [['drink' => 'liquor'],['drink' => 'water']],
            'or'      => [['objectClass' => ['device', 'user']]],
            'and'     => [['objectClass' => ['device', 'user']]],
            'each'    => [['givenName', 'surname', 'uid'], 'adam'],
            'either'  => [['givenName', 'surname', 'uid'], 'adam'],
            'any'     => [['givenName', 'surname', 'uid'], ' adam  smi'],
            'or_comb' => [
                FB::all(['objectClass' => ['device', 'user']]),
                FB::all(['objectClass' => ['x', 'y']])
            ],
            'or_comb_two' => [
                FB::all(
                    FB::either(['drink' => ['whiskey', 'gin', 'rum']]),
                    FB::_gte(['age' => 21])),
                FB::neither(['drink' => 'liquor'])
            ],
            'and_comb' => [
                FB::_lte(['age' => 21]),
                FB::either(['drink' => ['whiskey', 'gin', 'rum']])
            ],
            'and_comb_two' => [
                ['objectClass' => ['x', 'y']],
                FB::_lte(['age' => 21])
            ]
        ];

        return [
            'gte'          => ["gte",     $input['gte'],          $output['gte']],
            'lte'          => ["lte",     $input['gte'],          $output['lte']],
            'not'          => ["neither", $input['not'],          $output['not']],
            'neither'      => ["neither", $input['neither'],      $output['neither']],
            'or'           => ["either",  $input['or'],           $output['or']],
            'and'          => ["all",     $input['and'],          $output['and']],
            'each'         => ["all",     $input['each'],         $output['each']],
            'either'       => ["either",  $input['either'],       $output['either']],
            'any'          => ["any",     $input['any'],          $output['any']],
            'or_comb'      => ["either",  $input['or_comb'],      $output['or_comb']],
            'and_comb'     => ["all",     $input['and_comb'],     $output['and_comb']],
            'or_comb_two'  => ["either",  $input['or_comb_two'],  $output['or_comb_two']],
            'and_comb_two' => ["all",     $input['and_comb_two'], $output['and_comb_two']]
        ];
    }
}

