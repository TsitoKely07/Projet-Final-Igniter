<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;

class BaseClientController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Get fee for a given operation type and amount, optionally for a specific operator
     */
    protected function getFrais(int $typeOpId, float $montant, ?int $idOperateur = null): float
    {
        if ($idOperateur !== null) {
            // Look for operator-specific fee schedule (e.g., transfert_externe)
            $bareme = $this->db->table('bareme_frais')
                ->where('id_type_operation', $typeOpId)
                ->where('id_operateur', $idOperateur)
                ->where('montant_min <=', $montant)
                ->where('montant_max >=', $montant)
                ->get()->getRowArray();
            if ($bareme) {
                return (float)$bareme['frais'];
            }
        }

        // Fall back to standard fee schedule (operator-independent)
        $bareme = $this->db->table('bareme_frais')
            ->where('id_type_operation', $typeOpId)
            ->where('id_operateur', null)
            ->where('montant_min <=', $montant)
            ->where('montant_max >=', $montant)
            ->get()->getRowArray();

        return $bareme ? (float)$bareme['frais'] : 0.0;
    }

    /**
     * Get operator ID from a phone number based on prefix
     */
    protected function getOperateurFromNumero(string $numero): ?int
    {
        // Extract first 3 digits as prefix
        $prefix = substr($numero, 0, 3);

        $operateur = $this->db->query("
            SELECT p.id_operateur 
            FROM prefixe p 
            WHERE p.code = ? AND p.id_operateur IS NOT NULL
            LIMIT 1
        ", [$prefix])->getRowArray();

        return $operateur ? (int)$operateur['id_operateur'] : null;
    }

    /**
     * Get commission percentage between two operators
     */
    protected function getCommissionInteroperateur(int $sourceId, int $destId): float
    {
        $commission = $this->db->table('commission_interoperateur')
            ->where('id_operateur_source', $sourceId)
            ->where('id_operateur_destination', $destId)
            ->get()->getRowArray();

        return $commission ? (float)$commission['pourcentage_commission'] : 0.0;
    }

    protected function checkAuth()
    {
        if (!session()->has('client')) {
            return redirect()->to('/');
        }
        return null;
    }
}
