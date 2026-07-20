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

    public function transfert()
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $clientId = session()->get('client')['id'];
        $clientNumero = session()->get('client')['numero'];
        $destNumero = trim($this->request->getPost('numero_dest'));
        $montant = (float) $this->request->getPost('montant');
        $inclureFraisRetrait = (bool) $this->request->getPost('inclure_frais_retrait');

        if ($montant <= 0) {
            return redirect()->back()->with('error', 'Montant invalide.');
        }

        $destinataire = $this->db->table('compte_client')->where('numero', $destNumero)->get()->getRowArray();
        
        if (!$destinataire) {
            return redirect()->back()->with('error', 'Numéro destinataire introuvable.');
        }

        if ($destinataire['id'] == $clientId) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas faire un transfert vers vous-même.');
        }

        // Détection opérateur source et destination
        $operateurSource = $this->getOperateurFromNumero($clientNumero);
        $operateurDest = $this->getOperateurFromNumero($destNumero);
        $estInteroperateur = ($operateurSource !== null && $operateurDest !== null && $operateurSource !== $operateurDest);

        // Calcul des frais de transfert
        if ($estInteroperateur) {
            // Transfert inter-opérateur : utiliser le barème externe si disponible, sinon standard
            $fraisTransfert = $this->getFrais(3, $montant, $operateurDest);
            
            // Pas de frais de retrait pour les autres opérateurs
            $fraisRetrait = 0.0;
            $inclureFraisRetrait = false; // Force à false

            // Calcul commission inter-opérateur (pourcentage du montant)
            $pourcentageCommission = $this->getCommissionInteroperateur($operateurSource, $operateurDest);
            $commissionMontant = $montant * ($pourcentageCommission / 100);
        } else {
            // Transfert interne (même opérateur ou générique)
            $fraisTransfert = $this->getFrais(3, $montant);
            $fraisRetrait = $inclureFraisRetrait ? $this->getFrais(2, $montant) : 0.0;
            $commissionMontant = 0.0;
            $operateurDest = null; // Pas d'opérateur destination spécifique
        }

        $totalFrais = $fraisTransfert + $fraisRetrait + $commissionMontant;
        $totalAEnleverExpediteur = $montant + $totalFrais;
        $montantCrediteDestinataire = $montant + $fraisRetrait;

        $client = $this->db->table('compte_client')->where('id', $clientId)->get()->getRowArray();
        if ($client['solde'] < $totalAEnleverExpediteur) {
            $msg = "Solde insuffisant (Montant: {$montant} Ar + Frais: {$totalFrais} Ar";
            if ($estInteroperateur) {
                $msg .= " dont commission {$commissionMontant} Ar";
            }
            $msg .= ").";
            return redirect()->back()->with('error', $msg);
        }

        // Transaction : Débit expéditeur & Crédit destinataire
        $this->db->query("UPDATE compte_client SET solde = solde - ? WHERE id = ?", [$totalAEnleverExpediteur, $clientId]);
        $this->db->query("UPDATE compte_client SET solde = solde + ? WHERE id = ?", [$montantCrediteDestinataire, $destinataire['id']]);

        // Historique des frais payés
        $fraisTotauxEnregistres = $fraisTransfert + $fraisRetrait + $commissionMontant;

        $this->db->table('historique_operation')->insert([
            'id_compte_source'      => $clientId,
            'id_compte_dest'        => $destinataire['id'],
            'id_type_operation'     => 3,
            'montant'               => $montant,
            'frais'                 => $fraisTotauxEnregistres,
            'id_operateur_destination' => $operateurDest,
            'frais_retrait_inclus'  => $inclureFraisRetrait ? 1 : 0
        ]);

        $msg = 'Transfert réussi !';
        if ($estInteroperateur) {
            $msg .= " (Transfert inter-opérateur, commission: {$pourcentageCommission}%)";
        }
        return redirect()->to('/client/dashboard')->with('success', $msg);
    }

    public function transfertMultiple()
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $clientId = session()->get('client')['id'];
        $clientNumero = session()->get('client')['numero'];
        $operateurSource = $this->getOperateurFromNumero($clientNumero);

        $rawNumeros = $this->request->getPost('numeros');
        $montantTotal = (float) $this->request->getPost('montant_total');
        $inclureFraisRetrait = (bool) $this->request->getPost('inclure_frais_retrait');

        if ($montantTotal <= 0) {
            return redirect()->back()->with('error', 'Montant total invalide.');
        }

        // Séparation et nettoyage de la liste des numéros
        $listeNumeros = array_unique(array_filter(array_map('trim', preg_split('/[\s,]+/', $rawNumeros))));

        if (empty($listeNumeros)) {
            return redirect()->back()->with('error', 'Veuillez renseigner au moins un numéro destinataire.');
        }

        $nbDestinataires = count($listeNumeros);
        $montantParPersonne = $montantTotal / $nbDestinataires;

        // 1. Contrôle des destinataires et détection inter-opérateur
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

        // Pas de frais de retrait en cas d'inter-opérateur
        if ($estInteroperateurGlobal) {
            $inclureFraisRetrait = false;
        }

        // 2. Calcul des coûts par destinataire
        $fraisTransfertUnitaire = $this->getFrais(3, $montantParPersonne);
        $fraisRetraitUnitaire   = $inclureFraisRetrait ? $this->getFrais(2, $montantParPersonne) : 0.0;

        $coutUnitaireExpediteur = $montantParPersonne + $fraisTransfertUnitaire + $fraisRetraitUnitaire;
        $coutTotalGlobal        = $coutUnitaireExpediteur * $nbDestinataires;

        $client = $this->db->table('compte_client')->where('id', $clientId)->get()->getRowArray();
        if ($client['solde'] < $coutTotalGlobal) {
            return redirect()->back()->with('error', "Solde insuffisant pour exécuter cet envoi multiple. Montant requis : " . number_format($coutTotalGlobal, 2, ',', ' ') . " Ar.");
        }

        // 3. Exécution des transferts avec détection individuelle par destinataire
        foreach ($destinataires as $dest) {
            $operateurDest = $this->getOperateurFromNumero($dest['numero']);
            $estInterop = ($operateurSource !== null && $operateurDest !== null && $operateurSource !== $operateurDest);

            // Frais spécifiques pour cet opérateur destination
            $fraisTransfertDest = $estInterop ? $this->getFrais(3, $montantParPersonne, $operateurDest) : $fraisTransfertUnitaire;
            $fraisRetraitDest = $estInterop ? 0.0 : $fraisRetraitUnitaire;

            // Commission inter-opérateur
            $commissionDest = 0.0;
            if ($estInterop) {
                $pourcentageCommission = $this->getCommissionInteroperateur($operateurSource, $operateurDest);
                $commissionDest = $montantParPersonne * ($pourcentageCommission / 100);
            }

            $coutTotalDest = $montantParPersonne + $fraisTransfertDest + $fraisRetraitDest + $commissionDest;
            $montantCredite = $montantParPersonne + $fraisRetraitDest;

            $this->db->query("UPDATE compte_client SET solde = solde - ? WHERE id = ?", [$coutTotalDest, $clientId]);
            $this->db->query("UPDATE compte_client SET solde = solde + ? WHERE id = ?", [$montantCredite, $dest['id']]);

            $this->db->table('historique_operation')->insert([
                'id_compte_source'      => $clientId,
                'id_compte_dest'        => $dest['id'],
                'id_type_operation'     => 3,
                'montant'               => $montantParPersonne,
                'frais'                 => $fraisTransfertDest + $fraisRetraitDest + $commissionDest,
                'id_operateur_destination' => $estInterop ? $operateurDest : null,
                'frais_retrait_inclus'  => $inclureFraisRetrait ? 1 : 0
            ]);
        }

        $msg = "Envoi multiple de " . number_format($montantTotal, 2, ',', ' ') . " Ar réussi vers {$nbDestinataires} destinataires !";
        if ($estInteroperateurGlobal) {
            $msg .= " (Transferts inter-opérateurs détectés, pas de frais de retrait)";
        }
        return redirect()->to('/client/dashboard')->with('success', $msg);
    }
}