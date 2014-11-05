<?php

namespace Aramis\Bundle\ElasticsearchBundle\Query;

class GeneralQuery
{
    private $dbal;

    public function __construct($connection)
    {
        $this->dbal = $connection;
        $this->dbal->getConfiguration()->setSQLLogger(null);
    }

    private $generalQuery = "SELECT
        id,
        handling_charge_nv,
        handling_charge_uv,
        handling_charge_0k,
        transportation_charge,
        vat,
        vehicule_registration_charge
        FROM general_info g
        WHERE g.locale_id = :locale";

    private $simpleBodyworksQuery = "SELECT
        label
        FROM simple_bodywork";

    /**
     * Get general infos from DataBase
     *
     * @param $locale string
     * @return array
     */
    public function getGeneralInfos($locale)
    {
        gc_enable();
        $arrOffers = array();

        try {
            $stmtOffer = $this->dbal->prepare($this->generalQuery);
            $stmtOffer->bindValue('locale', $locale);
            $stmtOffer->execute();
            $generalInfosData = $stmtOffer->fetchAll(\PDO::FETCH_ASSOC);

            // normalement, il n'y a qu'une seule ligne d'informations générale par catalog (assimilé à une locale ici)
            $generalInfoData = array_pop($generalInfosData);
            $generalInfoData['simple_bodyworks'] = $this->getSimpleBodyworks();
            $id = $generalInfoData['id'];
            unset($generalInfoData['id']);
            $arrOffers[] = new \Elastica\Document($locale, $generalInfoData);
            gc_collect_cycles();

            return $arrOffers;
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }

    private function getSimpleBodyworks()
    {
        $stmtOffer = $this->dbal->prepare($this->simpleBodyworksQuery);
        $stmtOffer->execute();
        $simpleBodyworksData = $stmtOffer->fetchAll(\PDO::FETCH_ASSOC);

        if (!$simpleBodyworksData) {
            return array();
        }

        return $simpleBodyworksData;
    }
}
