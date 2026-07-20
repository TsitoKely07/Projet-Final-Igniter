<?= $this->extend('operator/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <span class="brand-dot"></span>
    <div>
        <p class="eyebrow">Backoffice</p>
        <h1>Décompte opérateur</h1>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header tone-black">Montants à envoyer aux opérateurs</div>
        <div class="panel-body">
            <table>
                <thead>
                    <tr><th>Opérateur</th><th>Commission due</th><th>Déjà envoyé</th><th>Reste</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($decomptes)): ?>
                        <tr><td colspan="4" class="text-secondary">Aucun décompte pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach($decomptes as $d): ?>
                            <?php $reste = $d['montant_commission'] - $d['deja_envoye']; ?>
                            <tr>
                                <td><strong><?= esc($d['operateur_nom']) ?></strong></td>
                                <td class="fw-bold"><?= number_format($d['montant_commission'], 2, ',', ' ') ?> Ar</td>
                                <td><?= number_format($d['deja_envoye'], 2, ',', ' ') ?> Ar</td>
                                <td class="fw-bold <?= $reste > 0 ? '' : 'text-secondary' ?>"><?= number_format($reste, 2, ',', ' ') ?> Ar</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header tone-pink">Marquer un envoi</div>
        <div class="panel-body">
            <form action="<?= base_url('operator/marquerEnvoye') ?>" method="post" class="inline-form" style="flex-direction:column;">
                <select name="id_operateur" class="field" required>
                    <option value="">Sélectionner un opérateur</option>
                    <?php foreach($decomptes as $d): ?>
                        <option value="<?= $d['operateur_id'] ?>"><?= esc($d['operateur_nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="montant_envoye" class="field" placeholder="Montant envoyé (Ar)" step="100" min="0" required>
                <button type="submit" class="btn">Enregistrer l'envoi</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

