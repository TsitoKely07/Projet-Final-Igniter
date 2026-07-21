<?php

namespace App\Controllers\Client;

class OperationController extends BaseClientController
{
    public function depot()
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $clientId = session()->get('client')['id'];
        $montant = (float) $this->request->getPost('montant');

        if ($montant <= 0) {
            return redirect()->back()->with('error', 'Montant invalide.');
        }

        $this->db->query("UPDATE compte_client SET solde = solde + ? WHERE id = ?", [$montant, $clientId]);

        // Historique (Type 1 = Dépôt)
        $this->db->table('historique_operation')->insert([
            'id_compte_source' => $clientId,
            'id_type_operation' => 1,
            'montant' => $montant,
            'frais' => 0.0
        ]);

        return redirect()->to('/client/dashboard')->with('success', 'Dépôt effectué avec succès.');
    }

    public function retrait()
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $clientId = session()->get('client')['id'];
        $montant = (float) $this->request->getPost('montant');

        if ($montant <= 0) {
            return redirect()->back()->with('error', 'Montant invalide.');
        }

        $client = $this->db->table('compte_client')->where('id', $clientId)->get()->getRowArray();
        $frais = $this->getFrais(2, $montant); // Type 2 = Retrait
        $totalAEnlever = $montant + $frais;

        if ($client['solde'] < $totalAEnlever) {
            return redirect()->back()->with('error', "Solde insuffisant (Montant + Frais de {$frais} Ar).");
        }

        $this->db->query("UPDATE compte_client SET solde = solde - ? WHERE id = ?", [$totalAEnlever, $clientId]);

        $this->db->table('historique_operation')->insert([
            'id_compte_source' => $clientId,
            'id_type_operation' => 2,
            'montant' => $montant,
            'frais' => $frais
        ]);

        return redirect()->to('/client/dashboard')->with('success', 'Retrait effectué avec succès.');
    }

    public function transfertMultiple()
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $clientId = session()->get('client')['id'];
        $clientNumero = session()->get('client')['numero'];
        $operateurSource = $this->getOperateurFromNumero($clientNumero);

        // 1. Récupération des deux numéros destinataires
        $num1 = trim((string) $this->request->getPost('numero_dest_1'));
        $num2 = trim((string) $this->request->getPost('numero_dest_2'));

        // Fallback si la vue envoie un champ 'numeros' sous forme de liste
        if (empty($num1) && empty($num2)) {
            $rawNumeros = $this->request->getPost('numeros');
            $listeNumeros = array_unique(array_filter(array_map('trim', preg_split('/[\s,]+/', (string)$rawNumeros))));
        } else {
            $listeNumeros = array_unique(array_filter([$num1, $num2]));
        }

        // 2. Récupération du montant et de l'option frais de retrait
        $montantInput = $this->request->getPost('montant') ?? $this->request->getPost('montant_total');
        $montantParPersonne = (float) $montantInput;
        $inclureFraisRetrait = (bool) $this->request->getPost('inclure_frais_retrait');

        if ($montantParPersonne <= 0) {
            return redirect()->back()->with('error', 'Montant invalide.');
        }

        if (count($listeNumeros) < 2) {
            return redirect()->back()->with('error', 'Veuillez renseigner deux numéros destinataires valides et différents.');
        }

        // 3. Contrôle de l'existence des destinataires
        $destinataires = [];
        $estInteroperateurGlobal = false;

        foreach ($listeNumeros as $num) {
            $dest = $this->db->table('compte_client')->where('numero', $num)->get()->getRowArray();
            if (!$dest) {
                return redirect()->back()->with('error', "Le numéro {$num} n'existe pas dans le système.");
            }
            if ($dest['id'] == $clientId) {
                return redirect()->back()->with('error', "Vous ne pouvez pas vous inclure dans la liste des destinataires.");
            }
            $destinataires[] = $dest;

            $operateurDestCheck = $this->getOperateurFromNumero($num);
            if ($operateurSource !== null && $operateurDestCheck !== null && $operateurSource !== $operateurDestCheck) {
                $estInteroperateurGlobal = true;
            }
        }

        // 4. Pré-calcul des coûts, frais et commissions pour chaque destinataire
        $coutTotalGlobal = 0.0;
        $detailsTransactions = [];

        foreach ($destinataires as $dest) {
            $operateurDest = $this->getOperateurFromNumero($dest['numero']);
            $estInterop = ($operateurSource !== null && $operateurDest !== null && $operateurSource !== $operateurDest);

            if ($estInterop) {
                // --- AUTRE OPÉRATEUR ---
                // Frais de transfert inter-opérateur
                $fraisTransfert = $this->getFrais(3, $montantParPersonne, $operateurDest);
                
                // Aucun frais de retrait pour les autres opérateurs
                $fraisRetrait = 0.0;

                // Commission de 1% (ou taux configuré)
                $pourcentageComm = $this->getCommissionInteroperateur($operateurSource, $operateurDest);
                $commission = ($pourcentageComm > 0) ? ($montantParPersonne * ($pourcentageComm / 100)) : ($montantParPersonne * 0.01);
            } else {
                // --- MÊME OPÉRATEUR ---
                // Frais de transfert standards
                $fraisTransfert = $this->getFrais(3, $montantParPersonne);
                
                // Frais de retrait optionnels
                $fraisRetrait = $inclureFraisRetrait ? $this->getFrais(2, $montantParPersonne) : 0.0;
                
                // Aucune commission
                $commission = 0.0;
            }

            $fraisTotauxUnitaires = $fraisTransfert + $fraisRetrait + $commission;
            $coutUnitaireExpediteur = $montantParPersonne + $fraisTotauxUnitaires;
    
            $coutTotalGlobal += $coutUnitaireExpediteur;

            $detailsTransactions[] = [
                'dest'           => $dest,
                'operateur_dest' => $operateurDest,
                'est_interop'    => $estInterop,
                'frais_totaux'   => $fraisTotauxUnitaires,
                'frais_retrait'  => $fraisRetrait,
                'cout_unitaire'  => $coutUnitaireExpediteur,
            ];
        }

        // Vérification du solde global de l'expéditeur
        $client = $this->db->table('compte_client')->where('id', $clientId)->get()->getRowArray();
        if ($client['solde'] < $coutTotalGlobal) {
            return redirect()->back()->with('error', "Solde insuffisant pour exécuter cet envoi multiple. Montant total requis (avec frais et commissions) : " . number_format($coutTotalGlobal, 2, ',', ' ') . " Ar.");
        }

        // 5. Exécution sécurisée des débits, crédits et enregistrement (Transaction SQL)
        $this->db->transStart();

        foreach ($detailsTransactions as $item) {
            $dest = $item['dest'];
            $montantCredite = $montantParPersonne + $item['frais_retrait'];

            // Débit de l'expéditeur
            $this->db->query("UPDATE compte_client SET solde = solde - ? WHERE id = ?", [$item['cout_unitaire'], $clientId]);

            // Crédit du destinataire
            $this->db->query("UPDATE compte_client SET solde = solde + ? WHERE id = ?", [$montantCredite, $dest['id']]);

            // Enregistrement dans l'historique
            $this->db->table('historique_operation')->insert([
                'id_compte_source'         => $clientId,
                'id_compte_dest'           => $dest['id'],
                'id_type_operation'        => 3,
                'montant'                  => $montantParPersonne,
                'frais'                    => $item['frais_totaux'],
                'id_operateur_destination' => $item['est_interop'] ? $item['operateur_dest'] : null,
                'frais_retrait_inclus'     => ($item['frais_retrait'] > 0) ? 1 : 0
            ]);
        }

        $this->db->transComplete();

        // Si une erreur SQL survient, la transaction est automatiquement annulée (rollback)
        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', "Une erreur est survenue lors du traitement de la transaction. Aucune somme n'a été débitée.");
        }

        $montantAffiche = number_format($montantParPersonne, 2, ',', ' ');
        $msg = "Transfert multiple de {$montantAffiche} Ar réussi vers les 2 destinataires !";
        if ($estInteroperateurGlobal) {
            $msg .= " (Commission inter-opérateur appliquée sur les numéros externes).";
        }

        return redirect()->to('/client/dashboard')->with('success', $msg);
    }
}