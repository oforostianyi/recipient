<?php

namespace Oforostianyi\Recipient;
/**
 *
 */
class MccMncRepository
{
    private DatabaseConnection $dbConnection;

    public function __construct(DatabaseConnection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * return array records from DB
     * @param string $cc
     * @return array|null
     */
    public function getMccMncByCc(string $cc): ?array
    {

        if (empty($cc)) return null;
        if (strlen($cc) == 1 && $cc > 1) return null;
        if (strlen($cc) == 2 && $cc == 38) return null;
        if (strlen($cc) == 4 && $cc[0] != '1' && $cc[0] != '9') return null;
        $mccmncBaseCC = MemoryCache::get('system', 'mccmncbase', $cc);
        if (!empty($mccmncBaseCC)) return $mccmncBaseCC;
        $mccmncBaseCC = [];
        $connection = $this->dbConnection->getConnection();
        $sql = 'SELECT `cc`, `ndc`, `subc`, `length`,  `mcc`, `mnc`, `cc2`, `country`, `operator` FROM mcc_mnc WHERE cc = ?';
        print_r($sql);
        $statement = $connection->prepare($sql);
        $statement->bind_param('i', $cc);
        $statement->execute();
        $result = $statement->get_result();
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            # приведем все значения к строке
            $row['mcc'] = (string)$row['mcc'];
            $row['mnc'] = (string)$row['mnc'];
            $row['cc'] = (string)$row['cc'];
            $row['ndc'] = (string)$row['ndc'];
            $row['length'] = (string)$row['length'];
            $ndc = ($row['ndc'] == '') ? '-' : $row['ndc'];
            $subc = ($row['subc'] == '') ? '-' : $row['subc'];
            $mccmncBaseCC[$ndc][$subc] = array('mcc' => $row['mcc'], 'mnc' => $row['mnc'], 'country' => $row['cc2'], 'operator' => $row['operator'], 'cc' => $row['cc'], 'ndc' => $row['ndc'], 'length' => $row['length']);
        }
        $statement->close();
        MemoryCache::set('system', 'mccmncbase', [$cc => $mccmncBaseCC], 300);
        return $mccmncBaseCC ?: null;
    }
}