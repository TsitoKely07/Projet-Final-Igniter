<?= $this->extend('operator/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <span class="brand-dot"></span>
    <div>
        <p class="eyebrow">Backoffice</p>
        <h1>Commissions inter-opérateurs</h1>
    </div>
</div>

<div class="grid-3">
    <div class="panel">
        <div class="panel-header tone-black">Ajouter une commission</div>
        <div class="panel-body">
            <form action="<?= base_url('operator/saveCommission') ?>" method="post" class="inline-form" style="flex-direction:column;">
                <select name="id_operateur_source" class="field" required>
                    <option value="">Opérateur source</option>
                    <?php foreach($operateurs as $op): ?>
                        <option value="<?= $op['id'] ?>"><?= esc($op['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="id_operateur_destination" class="field" required>
                    <option value="">Opérateur destination</option>
                    <?php foreach($operateurs as $op): ?>
                        <option value="<?= $op['id'] ?>"><?= esc($op['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="pourcentage_commission" class="field" placeholder="% Commission" step="0.1" min="0" max="100" required>
                <button type="submit" class="btn">Enregistrer</button>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header tone-black">Commissions configurées</div>
        <div class="panel-body">
            <table>
                <thead>
                    <tr><th>Source</th><th>Destination</th><th>% Commission</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($commissions)): ?>
                        <tr><td colspan="3" class="text-secondary">Aucune commission configurée.</td></tr>
                    <?php else: ?>
                        <?php foreach($commissions as $c): ?>
                            <tr>
                                <td><?= esc($c['operateur_source_nom']) ?></td>
                                <td><?= esc($c['operateur_dest_nom']) ?></td>
                                <td class="fw-bold"><?= number_format($c['pourcentage_commission'], 1) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

