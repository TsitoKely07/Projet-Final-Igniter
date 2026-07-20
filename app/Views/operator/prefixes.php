<?= $this->extend('operator/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <span class="brand-dot"></span>
    <div>
        <p class="eyebrow">Backoffice</p>
        <h1>Préfixes valables</h1>
    </div>
</div>

<div class="grid-3">
    <div class="panel">
        <div class="panel-header tone-black">Ajouter un préfixe</div>
        <div class="panel-body">
            <form action="<?= base_url('operator/addPrefix') ?>" method="post" class="inline-form">
                <input type="text" name="prefixe" class="field" placeholder="Ex : 032" required maxlength="5">
                <select name="id_operateur" class="field">
                    <option value="">Générique</option>
                    <?php foreach($operateurs as $op): ?>
                        <option value="<?= $op['id'] ?>"><?= esc($op['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn">Ajouter</button>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header tone-black">Liste des préfixes</div>
        <div class="panel-body">
            <table>
                <thead>
                    <tr><th>Préfixe</th><th>Opérateur</th></tr>
                </thead>
                <tbody>
                    <?php foreach($prefixes as $p): ?>
                        <tr>
                            <td><strong><?= esc($p['prefixe']) ?></strong></td>
                            <td><span class="badge"><?= esc($p['operateur_nom']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

