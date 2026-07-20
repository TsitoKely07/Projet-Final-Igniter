<?= $this->extend('operator/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <span class="brand-dot"></span>
    <div>
        <p class="eyebrow">Backoffice</p>
        <h1>Situation des gains</h1>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header tone-pink">Gains propres (Opérateur)</div>
        <div class="panel-body">
            <table>
                <thead><tr><th>Type d'opération</th><th>Total des gains</th></tr></thead>
                <tbody>
                    <?php if (empty($gains_propres)): ?>
                        <tr><td colspan="2" class="text-secondary">Aucun gain propre pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach($gains_propres as $g): ?>
                            <tr>
                                <td class="text-capitalize"><?= esc($g['type']) ?></td>
                                <td class="fw-bold"><?= number_format($g['total_frais'], 2, ',', ' ') ?> Ar</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header tone-black">Commissions inter-opérateurs</div>
        <div class="panel-body">
            <table>
                <thead><tr><th>Opérateur destinataire</th><th>Total commission</th></tr></thead>
                <tbody>
                    <?php if (empty($gains_interoperateur)): ?>
                        <tr><td colspan="2" class="text-secondary">Aucune commission inter-opérateur pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach($gains_interoperateur as $g): ?>
                            <tr>
                                <td><?= esc($g['operateur_dest']) ?></td>
                                <td class="fw-bold"><?= number_format($g['total_commission'], 2, ',', ' ') ?> Ar</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

