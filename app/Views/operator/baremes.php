<?= $this->extend('operator/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <span class="brand-dot"></span>
    <div>
        <p class="eyebrow">Backoffice</p>
        <h1>Barèmes des frais</h1>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header tone-pink">Ajouter un barème</div>
        <div class="panel-body">
            <form action="<?= base_url('operator/saveBareme') ?>" method="post" style="display:flex;flex-direction:column;gap:10px;">
                <select name="id_type_operation" class="field" required>
                    <option value="">Sélectionner un type</option>
                    <option value="2">Retrait</option>
                    <option value="3">Transfert</option>
                </select>
                <div style="display:flex;gap:10px;">
                    <input type="number" name="montant_min" class="field" placeholder="Min" required>
                    <input type="number" name="montant_max" class="field" placeholder="Max" required>
                </div>
                <input type="number" name="frais" class="field" placeholder="Frais" required>
                <select name="type_frais" class="field">
                    <option value="standard">Standard</option>
                    <option value="retrait">Retrait</option>
                    <option value="transfert_interne">Transfert interne</option>
                    <option value="transfert_externe">Transfert externe</option>
                </select>
                <select name="id_operateur" class="field">
                    <option value="">Propre opérateur</option>
                    <?php foreach($operateurs as $op): ?>
                        <option value="<?= $op['id'] ?>"><?= esc($op['nom']) ?> (inter-opérateur)</option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-yellow">Ajouter</button>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header tone-pink">Liste des barèmes</div>
        <div class="panel-body scroll">
            <table>
                <thead>
                    <tr><th>Type</th><th>Opérateur</th><th>Tranche montant</th><th>Frais</th></tr>
                </thead>
                <tbody>
                    <?php foreach($baremes as $b): ?>
                        <tr>
                            <td><span class="badge"><?= esc($b['type_nom']) ?></span></td>
                            <td><?= esc($b['operateur_nom']) ?></td>
                            <td>De <?= number_format($b['montant_min'], 0, ',', ' ') ?> à <?= number_format($b['montant_max'], 0, ',', ' ') ?> Ar</td>
                            <td class="fw-bold"><?= number_format($b['frais'], 0, ',', ' ') ?> Ar</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

