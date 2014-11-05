<?php

namespace Aramis\ElasticsearchBundle\Twig;

class SearchFormExtension extends \Twig_Extension
{
    public function buildSelectOfFacet($arrElements, $params = array('id' => '', 'class' => ''))
    {
        return array(
            'price' => new \Twig_Filter_Method($this, 'priceFilter'),
        );
    }
}