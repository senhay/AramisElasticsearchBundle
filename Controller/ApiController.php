<?php

namespace Aramis\Bundle\ElasticsearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Yaml\Parser;

class ApiController extends Controller
{
    /**
     * @Route("/{catalog}/brands/{vehicleType}", name="esBrands")
     */
    public function getBrandsAction($catalog, $vehicleType)
    {
        $dataIndex = $this->get('offer_' . $catalog . '_data');

        $searchParams = array();
        if (strtoupper($vehicleType) == 'ALL') {
            $brands = $dataIndex->getFacet('brand', $searchParams);
        } elseif (strtoupper($vehicleType) == 'NV0K') {
            $searchParams['vehicle_type'] = array('NV', '0k');
            $brands = $dataIndex->getFacet('brand', $searchParams);
        } else {
            $searchParams['vehicle_type'] = array($vehicleType);
            $brands = $dataIndex->getFacet('brand', $searchParams);
        }
        usort($brands, array($this, 'cmpBrandsModels'));

        /*
        foreach ($brands as &$brand) {
            $brand["term"] = ucwords(strtolower($brand["term"]));
        }
        */

        return new Response(json_encode($brands));
    }

    public function cmpBrandsModels($a, $b)
    {
        return strcasecmp($a["term"], $b["term"]);
    }

    /**
     * @Route("/{catalog}/modelsbybrand/{brand}/{vehicleType}", name="esModels")
     */
    public function getModelsByBrandAction($catalog, $brand, $vehicleType)
    {
        $dataIndex = $this->get('offer_' . $catalog . '_data');

        $searchParams = array();
        $searchParams['brand'] = array($brand);
        if (strtoupper($vehicleType) == 'ALL') {
            $models = $dataIndex->getFacet('model', $searchParams);
        } elseif (strtoupper($vehicleType) == 'NV0K') {
            $searchParams['vehicle_type'] = array('NV', '0k');
            $models = $dataIndex->getFacet('model', $searchParams);
        } else {
            $searchParams['vehicle_type'] = array($vehicleType);
            $models = $dataIndex->getFacet('model', $searchParams);
        }

        usort($models, array($this, 'cmpBrandsModels'));

        return new Response(json_encode($models));
    }

    /**
     * @Route("/{catalog}/bestvehicleimage/{brand}/{model}/{vehicleType}", name="esBestVehicleImage")
     */
    public function getBestVehicleImageAction($catalog, $brand, $model, $vehicleType)
    {
        $dataIndex = $this->get('offer_' . $catalog . '_data');

        $searchParams = $vehicleTypeParams = array();
        $searchParams['brand'] = array(strtoupper($brand));
        $searchParams['model'] = array($model);
        if (strtoupper($vehicleType) == 'NV0K') {
            $vehicleTypeParams['vehicle_type'] = array('NV', '0k');
        } else if (strtoupper($vehicleType) == 'ALL') {
            $vehicleTypeParams['vehicle_type'] = array();
        } else {
            $vehicleTypeParams['vehicle_type'] = array($vehicleType);
        }
        $resultSet = $dataIndex->searchOffers($vehicleTypeParams, $searchParams, array());

        return new Response(json_encode(array('image_path' => $resultSet['results'][0]['offer_best_vehicle']['image_path'])));
    }
}
