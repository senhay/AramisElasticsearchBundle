<?php

namespace Aramis\Bundle\ElasticsearchBundle\Query;

class OfferQuery
{
    const ADDITION_PART_EQUIPMENT = 'equipment';
    const ADDITION_PART_OPTION    = 'option';

    private $offersQuery = 'SELECT
        offer.id,
        offer.id_aramis,
        CASE WHEN bodywork.label IS NULL THEN "" ELSE bodywork.label END as bodywork,
        CASE WHEN simple_bodywork.label IS NULL THEN "" ELSE simple_bodywork.label END as simple_bodywork,
        CASE WHEN brand.label IS NULL THEN "" ELSE brand.label END as brand,
        CASE WHEN fuel.label IS NULL THEN "" ELSE fuel.label END as fuel,
        CASE WHEN gearbox.label IS NULL THEN "" ELSE gearbox.label END as gearbox,
        CASE WHEN simple_gearbox.label IS NULL THEN "" ELSE simple_gearbox.label END as simple_gearbox,
        CASE WHEN model.label IS NULL THEN "" ELSE model.label END as model,
        CASE WHEN sale_type.label IS NULL THEN "" ELSE sale_type.label END as sale_type,
        CASE WHEN simple_offer_name.label IS NULL THEN "" ELSE simple_offer_name.label END as simple_offer_name,
        CASE WHEN vehicle_type.label IS NULL THEN "" ELSE vehicle_type.label END  as vehicle_type,
        CASE WHEN category.label IS NULL THEN "" ELSE category.label END as category,
        CASE WHEN offer.manufacturer_warranty_time IS NULL THEN "" ELSE offer.manufacturer_warranty_time END as manufacturer_warranty_time,
        CASE WHEN offer.manufacturer_warranty_time_unit IS NULL THEN "" ELSE offer.manufacturer_warranty_time_unit END as manufacturer_warranty_time_unit,
        CASE WHEN offer.name IS NULL THEN "" ELSE offer.name END as name,
        CASE WHEN offer.aramis_price IS NULL THEN "" ELSE offer.aramis_price END as offer_price,
        "0" as offer_km,
        CASE WHEN offer.description IS NULL THEN "" ELSE offer.description END as description,
        CASE WHEN offer.autovista_aramis_id IS NULL THEN "" ELSE offer.autovista_aramis_id END as autovista_aramis_id,
        CASE WHEN offer.technical_capacity IS NULL THEN "" ELSE offer.technical_capacity END as technical_capacity,
        CASE WHEN offer.technical_capacity_unit IS NULL THEN "" ELSE offer.technical_capacity_unit END as technical_capacity_unit,
        CASE WHEN offer.nb_cylinders IS NULL THEN "" ELSE offer.nb_cylinders END as nb_cylinders,
        CASE WHEN offer.nb_valves_per_cylinders IS NULL THEN "" ELSE offer.nb_valves_per_cylinders END as nb_valves_per_cylinders,
        CASE WHEN offer.co2_emission IS NULL THEN "" ELSE offer.co2_emission END as co2_emission,
        CASE WHEN offer.co2_emission_unit IS NULL THEN "" ELSE offer.co2_emission_unit END as co2_emission_unit,
        CASE WHEN offer.power_kw IS NULL THEN "" ELSE offer.power_kw END as power_kw,
        CASE WHEN offer.nb_doors IS NULL THEN "" ELSE offer.nb_doors END as nb_doors,
        CASE WHEN offer.nb_gears IS NULL THEN "" ELSE offer.nb_gears END as nb_gears,
        CASE WHEN offer.nb_seats IS NULL THEN "" ELSE offer.nb_seats END as nb_seats,
        CASE WHEN offer.drive_type IS NULL THEN "" ELSE offer.drive_type END as drive_type,
        CASE WHEN offer.length IS NULL THEN "" ELSE offer.length END as length,
        CASE WHEN offer.length_unit IS NULL THEN "" ELSE offer.length_unit END as length_unit,
        CASE WHEN offer.emission_class IS NULL THEN "" ELSE offer.emission_class END as emission_class,
        CASE WHEN offer.width IS NULL THEN "" ELSE offer.width END as width,
        CASE WHEN offer.width_unit IS NULL THEN "" ELSE offer.width_unit END as width_unit,
        CASE WHEN offer.height IS NULL THEN "" ELSE offer.height END as height,
        CASE WHEN offer.height_unit IS NULL THEN "" ELSE offer.height_unit END as height_unit,
        CASE WHEN offer.wheelbase IS NULL THEN "" ELSE offer.wheelbase END as wheelbase,
        CASE WHEN offer.wheelbase_unit IS NULL THEN "" ELSE offer.wheelbase_unit END as wheelbase_unit,
        CASE WHEN offer.boot_capacity IS NULL THEN "" ELSE offer.boot_capacity END as boot_capacity,
        CASE WHEN offer.boot_capacity_unit IS NULL THEN "" ELSE offer.boot_capacity_unit END as boot_capacity_unit,
        CASE WHEN offer.kerb_weight_with_driver IS NULL THEN "" ELSE offer.kerb_weight_with_driver END as kerb_weight_with_driver,
        CASE WHEN offer.kerb_weight_with_driver_unit IS NULL THEN "" ELSE offer.kerb_weight_with_driver_unit END as kerb_weight_with_driver_unit,
        CASE WHEN offer.total_weight IS NULL THEN "" ELSE offer.total_weight END as total_weight,
        CASE WHEN offer.total_weight_unit IS NULL THEN "" ELSE offer.total_weight_unit END as total_weight_unit,
        CASE WHEN offer.roof_loading IS NULL THEN "" ELSE offer.roof_loading END as roof_loading,
        CASE WHEN offer.roof_loading_unit IS NULL THEN "" ELSE offer.roof_loading_unit END as roof_loading_unit,
        CASE WHEN offer.top_speed IS NULL THEN "" ELSE offer.top_speed END as top_speed,
        CASE WHEN offer.top_speed_unit IS NULL THEN "" ELSE offer.top_speed_unit END as top_speed_unit,
        CASE WHEN offer.airflow_drag_coefficient IS NULL THEN "" ELSE offer.airflow_drag_coefficient END as airflow_drag_coefficient,
        CASE WHEN offer.airflow_drag_coefficient_unit IS NULL THEN "" ELSE offer.airflow_drag_coefficient_unit END as airflow_drag_coefficient_unit,
        CASE WHEN offer.consumption_urban IS NULL THEN "" ELSE offer.consumption_urban END as consumption_urban,
        CASE WHEN offer.consumption_urban_unit IS NULL THEN "" ELSE offer.consumption_urban_unit END as consumption_urban_unit,
        CASE WHEN offer.consumption_overland IS NULL THEN "" ELSE offer.consumption_overland END as consumption_overland,
        CASE WHEN offer.consumption_overland_unit IS NULL THEN "" ELSE offer.consumption_overland_unit END as consumption_overland_unit,
        CASE WHEN offer.consumption_overall IS NULL THEN "" ELSE offer.consumption_overall END as consumption_overall,
        CASE WHEN offer.consumption_overall_unit IS NULL THEN "" ELSE offer.consumption_overall_unit END as consumption_overall_unit,
        CASE WHEN offer.manufacturer_price IS NULL THEN 0 ELSE offer.manufacturer_price END as manufacturer_price,
        CASE WHEN offer.recommended_price IS NULL THEN 0 ELSE offer.recommended_price END as recommended_price,
        CASE WHEN offer.discount IS NULL THEN 0 ELSE offer.discount END as discount,
        CASE WHEN offer.motorization IS NULL THEN "" ELSE offer.motorization END as motorization,
        CASE WHEN offer.is_vat_reclaimable IS NULL THEN "" ELSE offer.is_vat_reclaimable END as is_vat_reclaimable
        FROM offer
        LEFT JOIN autovista_aramis ON autovista_aramis.id = offer.autovista_aramis_id
        LEFT JOIN bodywork ON bodywork.id = offer.bodywork_id AND bodywork.locale_id = :locale
        LEFT JOIN simple_bodywork ON bodywork.simple_bodywork_id = simple_bodywork.id
        INNER JOIN brand ON brand.id = offer.brand_id AND brand.locale_id = :locale
        LEFT JOIN fuel ON fuel.id = offer.fuel_id AND fuel.locale_id = :locale
        LEFT JOIN gearbox ON gearbox.id = offer.gearbox_id AND gearbox.locale_id = :locale
        LEFT JOIN simple_gearbox ON gearbox.simple_gearbox_id = simple_gearbox.id
        LEFT JOIN model ON model.id = offer.model_id AND model.locale_id = :locale
        LEFT JOIN sale_type ON sale_type.id = offer.sale_type_id
        LEFT JOIN simple_offer_name ON simple_offer_name.id = offer.simple_offer_name_id AND simple_offer_name.locale_id = :locale
        LEFT JOIN vehicle_type ON vehicle_type.id = offer.vehicle_type_id
        LEFT JOIN category ON category.id = offer.category_id
        INNER JOIN offer_price ON offer_price.offer_id = offer.id AND offer_price.is_online = 1
        INNER JOIN vehicle_stock ON vehicle_stock.offer_price_id = offer_price.id AND vehicle_stock.is_active = 1
        WHERE offer.locale_id = :locale and offer.aramis_price > 1 and offer.is_active = 1
        ';

    private $offerPriceQuery = 'SELECT
        color.label as color,
        interior.label as interior,
        scc.label as simple_color_label,
        CASE WHEN sci.label IS NULL THEN "" ELSE sci.label END as interior_simple_color_label,
        CASE WHEN simple_upholstery.label IS NULL THEN "" ELSE simple_upholstery.label END as simple_upholstery_label,
        offer_price.id as id,
        offer_price.aramis_price as price,
        offer_price.id_aramis as offer_price_id_aramis,
        CASE WHEN color.is_metallic IS NULL THEN 0 ELSE color.is_metallic END as is_metallic,
        CASE WHEN offer_price.is_online_classified_ad IS NULL THEN 0 ELSE offer_price.is_online_classified_ad END as is_online_classified_ad
        FROM offer_price
        INNER JOIN color ON color.id = offer_price.color_id AND color.locale_id = :locale
        LEFT JOIN simple_color scc ON scc.id = color.simple_color_id
        INNER JOIN interior ON interior.id = offer_price.interior_id AND interior.locale_id = :locale
        LEFT JOIN simple_color sci ON sci.id = interior.simple_color_id
        LEFT JOIN simple_upholstery ON simple_upholstery.id = interior.simple_upholstery_id
        INNER JOIN vehicle_stock ON vehicle_stock.offer_price_id = offer_price.id AND vehicle_stock.is_active = 1
        WHERE offer_price.is_online = 1 AND offer_price.offer_id = :offer_id
        GROUP BY offer_price.id
        ';

    private $offerPriceImageQuery = 'SELECT
        offer_price_image.image_path as image_path,
        offer_price_image.rank as rank
        FROM offer_price_image
        WHERE offer_price_image.offer_price_id = :offer_price_id
        ORDER BY offer_price_image.rank
        ';

    private $vehicleStocksQuery = 'SELECT
        vehicle_stock.mileage as mileage,
        vehicle_stock.id_aramis as vehicle_id_aramis,
        vehicle_stock.location as location,
        vehicle_stock.first_registration_at as first_registration_at,
        vehicle_stock.emission_test_at as emission_test_at,
        vehicle_stock.technical_inspection_at as technical_inspection_at,
        CASE WHEN vehicle_stock.warranty_time IS NULL THEN "" ELSE vehicle_stock.warranty_time END as warranty_time,
        CASE WHEN vehicle_stock.disponibility_time IS NULL THEN "" ELSE vehicle_stock.disponibility_time END as disponibility_time,
        CASE WHEN vehicle_stock.warranty_time_unit IS NULL THEN "" ELSE vehicle_stock.warranty_time_unit END as warranty_time_unit,
        CASE WHEN vehicle_stock.is_damaged_vehicle IS NULL THEN 0 ELSE vehicle_stock.is_damaged_vehicle END as is_damaged_vehicle,
        CASE WHEN vehicle_stock.is_accident_free IS NULL THEN 0 ELSE vehicle_stock.is_accident_free END as is_accident_free,
        CASE WHEN vehicle_stock.has_service_book IS NULL THEN 0 ELSE vehicle_stock.has_service_book END as has_service_book,
        CASE WHEN vehicle_stock.has_technical_inspection_after_sale IS NULL THEN 0 ELSE vehicle_stock.has_technical_inspection_after_sale END as has_technical_inspection_after_sale,
        CASE WHEN vehicle_stock.origin_catalog_id IS NULL THEN "" ELSE vehicle_stock.origin_catalog_id END as country_of_origin,
        vehicle_stock.cohiba_vehicle_number as cohiba_vehicle_number,
        CASE WHEN vehicle_stock.previous_owners IS NULL THEN "" ELSE vehicle_stock.previous_owners END as previous_owners,
        vehicle_status.label as vehicle_status,
        vehicle_stock.arrival_date_at as arrival_date_at
        FROM vehicle_stock
        LEFT JOIN vehicle_status ON vehicle_status.id= vehicle_stock.vehicle_status_id
        WHERE vehicle_stock.offer_price_id = :offer_price_id AND vehicle_stock.is_active = 1
        ';

    private $offerAdditionQuery = 'SELECT
        addition.label,
        addition.aramis_price,
        addition.manufacturer_price,
        category.label as category,
        addition_type.label as type,
        simple_addition.label as simple_name,
        addition_category.label as addition_category
        FROM addition
        LEFT JOIN category ON addition.addition_category_id = category.id
        INNER JOIN addition_part ON addition_part.id = addition.addition_part_id
        LEFT JOIN addition_type ON addition_type.id = addition.addition_type_id
        LEFT JOIN simple_addition ON simple_addition.id = addition.simple_addition_id
        LEFT JOIN addition_category on addition_category.id = addition.addition_category_id
        WHERE
        addition.offer_id = :offer_id
        AND addition_part.label = :type
        ';

    private $specificationsQuery = 'SELECT
        offer.nb_gears,
        offer.nb_seats,
        concat(offer.co2_emission, " " ,offer.co2_emission_unit) as co2_emission,
        offer.brake_horse_power,
        concat(offer.technical_capacity, " ", offer.technical_capacity_unit) as technical_capacity,
        offer.nb_cylinders,
        offer.nb_valves_per_cylinders,
        concat(offer.torque, " ", offer.torque_unit) as torque,
        offer.drive_type,
        concat(offer.length, " ", offer.length_unit) as length,
        concat(offer.wheelbase, " ", offer.wheelbase_unit) as wheelbase,
        concat(offer.boot_capacity, " ", offer.boot_capacity_unit) as boot_capacity,
        concat(offer.kerb_weight_with_driver, " ", offer.kerb_weight_with_driver_unit) as kerb_weight_with_driver,
        concat(offer.total_weight, " ", offer.total_weight_unit) as total_weight,
        concat(offer.roof_loading, " ", offer.roof_loading_unit) as roof_loading,
        concat(offer.top_speed, " ", offer.top_speed_unit) as top_speed,
        concat(offer.airflow_drag_coefficient, " ", offer.airflow_drag_coefficient_unit) as airflow_drag_coefficient,
        offer.acceleration,
        concat(offer.consumption_urban, " ", offer.consumption_urban_unit) as consumption_urban,
        concat(offer.consumption_overland, " ", offer.consumption_overland_unit) as consumption_overland,
        concat(offer.consumption_overall, " ", offer.consumption_overall_unit) as consumption_overall
        FROM offer
        WHERE offer.id = :offer_id
        ';

    private $offerColorsQuery = 'SELECT
        distinct simple_color.label as simple_color
        FROM simple_color
        INNER JOIN color ON simple_color.id = color.simple_color_id
        INNER JOIN offer_price ON color.id = offer_price.color_id AND color.locale_id = :locale
        WHERE offer_price.is_online = 1 AND offer_price.offer_id = :offer_id
        ';

        private $offerBestVehicle = array();
        private $tools;

    public function __construct($connection, $tools)
    {
        $this->dbal = $connection;
        $this->dbal->getConfiguration()->setSQLLogger(null);
        $this->offerQuery = $this->offersQuery.' AND offer.id = :offer_id group by offer.id';
        $this->tools = $tools;
    }

    /**
     * Get offers from DataBase
     *
     * @param $locale string
     * @return array
     */
    public function getOffers($locale)
    {
        gc_enable();
        $arrOffers = array();

        try {
            $this->offersQueryGB = $this->offersQuery.' group by offer.id';
            $stmtOffer = $this->dbal->prepare($this->offersQueryGB);
            $stmtOffer->bindValue('locale', $locale);
            $stmtOffer->execute();
            $offerData = $stmtOffer->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($offerData as $arrData) {
                $offer = $this->getOffer($arrData['id'], $locale);

                if (!$offer) {
                    continue;
                }

                $arrOffers[] = new \Elastica\Document($arrData['id_aramis'], $offer);
                gc_collect_cycles();
            }

            return $arrOffers;
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * Return offer with given id in given locale
     *
     * @param $id     offer id
     * @param $locale offer locale
     *
     * @return array
     */
    public function getOffer($id, $locale)
    {

        $stmtOffer = $this->dbal->prepare($this->offerQuery);
        $stmtOffer->bindValue('offer_id', $id);
        $stmtOffer->bindValue('locale', $locale);
        $stmtOffer->execute();
        $offer = $stmtOffer->fetch(\PDO::FETCH_ASSOC);

        if (!$offer) {
            return array();
        }

        // on devrait passer le catalog et pas la locale en paramètre, mais comme on ne l'a pas, on fait avec car pour l'instant, catalog et locale ont les mêmes valeurs
        $energyEfficiencyClass = $this->tools->getEnergyEfficiencyClass($locale, $offer['co2_emission'], $offer['kerb_weight_with_driver']);
        $offer['energy_efficiency_class'] = $energyEfficiencyClass;

        $offer['equipments']     = $this->getAdditions($id, self::ADDITION_PART_EQUIPMENT);
        $offer['options']        = $this->getAdditions($id, self::ADDITION_PART_OPTION);
        $offer['pictograms']     = $this->getPictograms($offer['options'], $offer['equipments']);
        $offer['offer_prices']   = $this->getOfferPrices($id, $locale);
        $offer['offer_colors']   = $this->getOfferColors($id, $locale);
        $offer['specifications'] = $this->getSpecifications($id);
        $offer['offer_best_vehicle']    = $this->offerBestVehicle[$id];

        $offer['availability'] = $offer['offer_best_vehicle']['vehicle_status'];
        $offer['offer_km'] = $offer['offer_best_vehicle']['mileage'];

        $offer['arrival_date_min_at'] = $offer['offer_best_vehicle']['arrival_date_min_at'];
        $offer['arrival_date_max_at'] = $offer['offer_best_vehicle']['arrival_date_max_at'];

        // /!\ on ne remonte pas l'id dans l'index car il ne doit jamais apparaitre sur le front /!\
        unset($offer['id']);

        return $offer;
    }

    /**
     * Return offerPriceImages of given offerPrice
     *
     * @param $id     offerPrice id
     *
     * @return array
     */
    public function getOfferPriceImages($id)
    {
        $stmtPrice = $this->dbal->prepare($this->offerPriceImageQuery);
        $stmtPrice->bindValue('offer_price_id', $id);
        $stmtPrice->execute();
        $offerPriceImages = $stmtPrice->fetchAll(\PDO::FETCH_ASSOC);

        if (!$offerPriceImages) {
            $offerPriceImages = array();
        }
        return $offerPriceImages;
    }

    /**
     * Return offerPriceImages of given offerPrice
     *
     * @param $id     offerPrice id
     *
     * @return array
     */
    public function getVehicleStocks($id, $locale)
    {
        $stmtPrice = $this->dbal->prepare($this->vehicleStocksQuery);
        $stmtPrice->bindValue('offer_price_id', $id);
        $stmtPrice->execute();
        $vehicleStocks = $stmtPrice->fetchAll(\PDO::FETCH_ASSOC);

        if (!$vehicleStocks) {
            $vehicleStocks = array();
        } else {
            foreach ($vehicleStocks as &$vehicleStock) {
                // les 3 derniers caractères du NVN ne doivent surtout pas être publique
                $vehicleStock['cohiba_vehicle_number'] = substr($vehicleStock['cohiba_vehicle_number'], 0, -3);
                $arrivalDates = $this->tools->getArrivalDates($locale, $vehicleStock['vehicle_status'], $vehicleStock['arrival_date_at']);
                $vehicleStock['arrival_date_min_at'] = $arrivalDates['min'];
                $vehicleStock['arrival_date_max_at'] = $arrivalDates['max'];
                // on n'a pas besoin que l'arrival_date_at soit public
                unset($vehicleStock['arrival_date_at']);
            }
        }

        return $vehicleStocks;
    }

    /**
     * Return colors and interiors of given offer in given locale
     *
     * @param $id     offer id
     * @param $locale offer locale
     *
     * @return array
     */
    public function getOfferPrices($id, $locale)
    {
        $stmtPrice = $this->dbal->prepare($this->offerPriceQuery);
        $stmtPrice->bindValue('locale', $locale);
        $stmtPrice->bindValue('offer_id', $id);
        $stmtPrice->execute();
        $offerPrices = $stmtPrice->fetchAll(\PDO::FETCH_GROUP);
        $colors = array();

        if (!$offerPrices) {
            return $colors;
        }

        foreach ($offerPrices as $color => $interiors) {
            $item = array(
                'color_label'           => $color,
                'simple_color_label'    => $interiors[0]['simple_color_label'],
                'is_metallic'           => $interiors[0]['is_metallic'],
                'is_online_classified_ad' => $interiors[0]['is_online_classified_ad'],
                'offer_price_id_aramis' => $interiors[0]['offer_price_id_aramis'],
                'interiors'             => array(),
            );
            // maintain an array of indexes to later retrieve interiors and add vehicles
            $indexes = array();

            foreach ($interiors as $interior) {
                // add interior for a color only once
                if (!isset($indexes[$interior['interior']])) {
                    $item['interiors'][] = array(
                        'label' => $interior['interior'],
                        'interior_simple_color_label' => $interior['interior_simple_color_label'],
                        'simple_upholstery_label'     => $interior['simple_upholstery_label'],
                    );
                    $indexes[$interior['interior']] = count($item['interiors']) - 1;
                    // add images for this offer_price

                    $item['interiors'][$indexes[$interior['interior']]]['images'] = $this->getOfferPriceImages($interior['id']);

                    // on récupère la meilleure image
                    $bestImage = $this->getBestImage($item['interiors'][$indexes[$interior['interior']]]['images']);

                    $item['interiors'][$indexes[$interior['interior']]]['vehicles'] = $this->getVehicleStocks($interior['id'], $locale);


                    // on récupère le meilleur véhicule selon sa disponibilité
                    if (!empty($item['interiors'][$indexes[$interior['interior']]]['vehicles'])) {
                        $bestVehicle = $this->getBestVehicle($item['interiors'][$indexes[$interior['interior']]]['vehicles']);
                    }

                    $bestVehicle['image_path'] = $bestImage['image_path'];
                    $item['interiors'][$indexes[$interior['interior']]]['best_vehicle'] = $bestVehicle;

                    // on compare ce véhicule avec le meilleur de l'offre
                    if (!isset($this->offerBestVehicle[$id])) {
                        $this->offerBestVehicle[$id] = array();
                    }
                    $this->offerBestVehicle[$id] = $this->getOfferBestVehicle($this->offerBestVehicle[$id], $bestVehicle);

                }
            }
            $colors[] = $item;
        }

        return $colors;
    }

    /**
     * Return colors of given offer
     *
     * @param $id           offer id
     * @param $locale locale
     *
     * @return array
     */
    public function getOfferColors($id, $locale)
    {
        $stmtColor = $this->dbal->prepare($this->offerColorsQuery);
        $stmtColor->bindValue('offer_id', $id);
        $stmtColor->bindValue('locale', $locale);
        $stmtColor->execute();
        $colors = $stmtColor->fetchAll(\PDO::FETCH_ASSOC);

        if (!$colors) {
            $colors = array();
        }

        return $colors;
    }

    /**
     * Return additions of given offer
     *
     * @param $id           offer id
     * @param $additionPart addition part
     *
     * @return array
     */
    public function getAdditions($id, $additionPart)
    {
        $stmtAddition = $this->dbal->prepare($this->offerAdditionQuery);
        $stmtAddition->bindValue('offer_id', $id);
        $stmtAddition->bindValue('type', $additionPart);
        $stmtAddition->execute();
        $additions = $stmtAddition->fetchAll(\PDO::FETCH_ASSOC);

        if (!$additions) {
            $additions = array();
        }

        return $additions;
    }

    /**
     * Return
     *
     * @param $options    offer options
     * @param $equipments offer equipments
     *
     * @return array
     */
    public function getPictograms($options, $equipments)
    {
        $pictograms = array();
        $additions = array_merge($options, $equipments);

        foreach ($additions as $addition) {
            if (!in_array(array('label' => $addition['simple_name']), $pictograms)) {
                if (is_null($addition['simple_name'])) {
                    continue;
                }
                if (in_array($addition['simple_name'], $pictograms)) {
                    continue;
                }
                $pictograms[] = array(
                    'pictogram_label' => $addition['simple_name']
                );
            }
        }

        return $pictograms;
    }

    /**
     * Return vehicle specifications
     *
     * @param $offerId
     *
     * @return array
     */
    public function getSpecifications($offerId)
    {
        $stmtSpecs = $this->dbal->prepare($this->specificationsQuery);
        $stmtSpecs->bindValue('offer_id', $offerId);
        $stmtSpecs->execute();
        $specs = $stmtSpecs->fetch(\PDO::FETCH_ASSOC);

        $returnedSpecs = array();

        foreach ($specs as $oneSpecIndex => $oneSpecValue) {
            $returnedSpecs[] = array('label' => str_replace('_', ' ', ucfirst($oneSpecIndex)), 'value' => $oneSpecValue);
        }

        return $returnedSpecs;
    }

    /**
     * returns the image with the best rank
     */
    private function getBestImage($images)
    {
        $bestImage = array_shift($images);
        foreach ($images as $image) {
            if (!isset($image['rank'])) {
                continue;
            }
            if ($image['rank'] == 1) {

                return $image;
            } elseif (!empty($bestImage) && $image['rank'] < $bestImage['rank']) {
                $bestImage = $image;
            }
        }

        return $bestImage;
    }

    /**
     * returns the best of the two vehicle
     */
    private function getOfferBestVehicle($vehicleOffer, $vehicleOfferPrice)
    {
        if (empty($vehicleOffer)) {
            return $vehicleOfferPrice;
        }
        switch ($vehicleOffer['vehicle_status']) {
            case 'available':
                return $vehicleOffer;
            case 'available within 2-3 weeks':
                switch ($vehicleOfferPrice['vehicle_status']) {
                    case 'available':
                        return $vehicleOfferPrice;
                }
                break;
            case 'coming':
                switch ($vehicleOfferPrice['vehicle_status']) {
                    case 'available':
                        return $vehicleOfferPrice;
                    case 'available within 2-3 weeks':
                        $vehicleOffer = $vehicleOfferPrice;
                        break;
                }
                break;
            case 'available within 1-2 months':
                switch ($vehicleOfferPrice['vehicle_status']) {
                    case 'available':
                        return $vehicleOfferPrice;
                    case 'available within 2-3 weeks':
                        $vehicleOffer = $vehicleOfferPrice;
                        break;
                    case 'coming':
                        $vehicleOffer = $vehicleOfferPrice;
                        break;
                }
                break;
            case 'order':
                switch ($vehicleOfferPrice['vehicle_status']) {
                    case 'available':
                        return $vehicleOfferPrice;
                    case 'available within 2-3 weeks':
                        $vehicleOffer = $vehicleOfferPrice;
                        break;
                    case 'available within 1-2 months':
                        $vehicleOffer = $vehicleOfferPrice;
                        break;
                    case 'coming':
                        $vehicleOffer = $vehicleOfferPrice;
                        break;
                }
                break;
            default:
                break;
        }
        return $vehicleOffer;
    }

    /**
     * returns the vehicle with the best availability
     *
     * @return array
     *
     */
    private function getBestVehicle($vehicles)
    {
        $bestVehicle = array_shift($vehicles);
        foreach ($vehicles as $vehicle) {
            switch ($bestVehicle['vehicle_status']) {
                case 'available':
                    return $bestVehicle;
                case 'available within 2-3 weeks':
                    switch ($vehicle['vehicle_status']) {
                        case 'available':
                            return $vehicle;
                    }
                    break;
                case 'coming':
                    switch ($vehicle['vehicle_status']) {
                        case 'available':
                            return $vehicle;
                        case 'available within 2-3 weeks':
                            $bestVehicle = $vehicle;
                            break;
                    }
                    break;
                case 'available within 1-2 months':
                    switch ($vehicle['vehicle_status']) {
                        case 'available':
                            return $vehicle;
                        case 'available within 2-3 weeks':
                            $bestVehicle = $vehicle;
                            break;
                        case 'coming':
                            $bestVehicle = $vehicle;
                            break;
                    }
                    break;
                case 'order':
                    switch ($vehicle['vehicle_status']) {
                        case 'available':
                            return $vehicle;
                        case 'available within 2-3 weeks':
                            $bestVehicle = $vehicle;
                            break;
                        case 'available within 1-2 months':
                            $bestVehicle = $vehicle;
                            break;
                        case 'coming':
                            $vehicleOffer = $vehicleOfferPrice;
                            break;
                    }
                    break;
                default:
                    break;
            }
        }
        return $bestVehicle;
    }

    /**
     * Get image path
     * @param  array $vehicles
     * @return string
     */
    public function getImagePath($vehicles)
    {
        foreach ($vehicles as $vehicle) {
            if ($vehicle['image_path'] != '') {

                return $vehicle['image_path'];
            }
        }

        return '';
    }
}
