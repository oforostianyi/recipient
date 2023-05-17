<?php

namespace Oforostianyi\Recipient;
/**
 * return records by countryCode from database or MemoryStorage
 */
class MccMncRepository
{
    private DatabaseConnection $dbConnection;

    /**
     * @param DatabaseConnection $dbConnection
     */
    public function __construct(DatabaseConnection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * return array records from DB
     * @param string $cc
     * @return array|null
     */
    public function getMccMncByCc(string $cc)
    {

        if (empty($cc)) return null;
        // only 1 CountryCode can be 1
        if (strlen($cc) == 1 && $cc > 1) return null;
        // only CountryCode with langth 4 can't start with 1 or 9
        if (strlen($cc) == 4 && $cc[0] != '1' && $cc[0] != '9') return null;
        // check in memoryCache. We don't need make additional queries to DB
        if(MemoryCache::checkKey('system', 'mccmncbase', $cc))
        {
            return MemoryCache::get('system', 'mccmncbase', $cc);
        }
        $mccmncBaseCC = [];
        $connection = $this->dbConnection->getConnection();
        $sql = 'SELECT `cc`, `ndc`, `subc`, `length`,  `mcc`, `mnc`, `cc2`, `country`, `operator` FROM `mcc_mnc` WHERE cc = ?';
        $statement = $connection->prepare($sql);
        $statement->bind_param('i', $cc);
        $statement->execute();
        $result = $statement->get_result();
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            # let's bring all the values to the string
            $row['mcc'] = (string) $row['mcc'];
            $row['mnc'] = (string) $row['mnc'];
            $row['cc'] = (string) $row['cc'];
            $row['ndc'] = (string) $row['ndc'];
            $row['length'] = (string) $row['length'];
            $ndc = ($row['ndc'] == '') ? '-' : $row['ndc'];
            $subc = ($row['subc'] == '') ? '-' : $row['subc'];
            $mccmncBaseCC[$ndc][$subc] = ['mcc' => $row['mcc'], 'mnc' => $row['mnc'], 'country' => $row['cc2'], 'operator' => $row['operator'], 'cc' => $row['cc'], 'ndc' => $row['ndc'], 'length' => $row['length']];
        }
        $statement->close();
        // store result in memoryCache
        MemoryCache::set('system', 'mccmncbase', [$cc => $mccmncBaseCC], 300);
        return $mccmncBaseCC ?: null;
    }
}