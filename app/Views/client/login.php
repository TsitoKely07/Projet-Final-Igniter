<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mobile Money - Connexion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg:#F7F6F2;
            --pink:#00BFFF;
            --yellow:#FFC72C;
            --purple:#C9BEFF;
            --black:#0F0F10;
            --gray:#6B6A70;
        }
        *{ box-sizing:border-box; }
        body{
            margin:0;
            min-height:100vh;
            font-family:'Inter' ,-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;;
            color:var(--black);
            background:
                radial-gradient(circle at 15% 20%, rgba(255,31,109,0.10), transparent 40%),
                radial-gradient(circle at 85% 15%, rgba(255,199,44,0.14), transparent 40%),
                radial-gradient(circle at 50% 90%, rgba(201,190,255,0.18), transparent 45%),
                var(--bg);
            display:flex;
            align-items:center;
            justify-content:center;
            padding:24px;
        }
        .auth-card{
            width:100%;
            max-width:400px;
            background:#fff;
            
            padding:40px 36px;
            box-shadow:0 20px 45px -20px rgba(15,15,16,0.18);
            border:1px solid rgba(15,15,16,0.06);
        }
        .brand{
            display:flex;
            align-items:center;
            gap:8px;
            margin-bottom:28px;
        }
        .brand-dot{
            width:14px;height:14px;            background:var(--pink);
        }
        .brand-name{
            font-family:'Inter' ,-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;;
            font-weight:800;
            font-size:1.05rem;
            letter-spacing:-0.02em;
        }
        h1{
            font-family:'Inter' ,-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;;
            font-weight:800;
            font-size:1.9rem;
            line-height:1.15;
            margin:0 0 6px;
            letter-spacing:-0.02em;
        }
        h1 .pill{
            display:inline-block;
            background:var(--pink);
            color:#fff;
            ;
            padding:2px 14px;
            font-size:1.6rem;
        }
        p.subtitle{
            color:var(--gray);
            font-size:0.95rem;
            margin:0 0 28px;
        }
        .alert{
            
            padding:12px 16px;
            font-size:0.9rem;
            margin-bottom:20px;
            font-weight:500;
        }
        .alert-danger{
            background:rgba(255,31,109,0.10);
            color:#C4104F;
            border:1px solid rgba(255,31,109,0.25);
        }
        label{
            display:block;
            font-size:0.85rem;
            font-weight:600;
            margin-bottom:8px;
            color:var(--black);
        }
        .form-control{
            width:100%;
            border:1.5px solid #E7E5E0;
            
            padding:14px 16px;
            font-family:'Inter' ,-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;;
            font-size:1rem;
            background:#FAFAF8;
            outline:none;
            transition:border-color .15s ease, background .15s ease;
        }
        .form-control:focus{
            border-color:var(--pink);
            background:#fff;
        }
        .mb-3{ margin-bottom:22px; }
        .btn-submit{
            width:100%;
            border:none;
            ;
            background:var(--black);
            color:#fff;
            font-family:'Inter' ,-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;;
            font-weight:700;
            font-size:1rem;
            padding:15px;
            cursor:pointer;
            transition:transform .12s ease, background .15s ease;
        }
        .btn-submit:hover{
            background:var(--pink);
            transform:translateY(-1px);
        }
        .foot-note{
            text-align:center;
            font-size:0.8rem;
            color:var(--gray);
            margin-top:22px;
        }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="brand">
        <span class="brand-dot"></span>
        <span class="brand-name">Mobile Money</span>
    </div>

    <h1>Content de te <span class="pill">revoir</span></h1>
    <p class="subtitle">Connecte-toi avec ton numéro pour accéder à ton espace client.</p>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('client/loginProcess') ?>" method="post">
        <div class="mb-3">
            <label for="numero">Numéro de téléphone</label>
            <input type="text" id="numero" name="numero" class="form-control" placeholder="ex : 0331234567" required>
        </div>
        <button type="submit" class="btn-submit">Se connecter</button>
    </form>

    <p class="foot-note">Service disponible 24h/24 · Frais transparents</p>
</div>
</body>
</html>