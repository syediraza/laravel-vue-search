<?php
$manifestPath = public_path('build/manifest.json');
$manifest = [];
if (file_exists($manifestPath)) {
    $manifest = json_decode(file_get_contents($manifestPath), true);
}

$jsAsset = isset($manifest['resources/js/app.js']['file']) 
    ? 'build/' . $manifest['resources/js/app.js']['file'] 
    : '';

$cssAsset = isset($manifest['resources/css/app.css']['file']) 
    ? 'build/' . $manifest['resources/css/app.css']['file'] 
    : '';

$jsCssAssets = isset($manifest['resources/js/app.js']['css']) 
    ? $manifest['resources/js/app.js']['css'] 
    : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AdCampaign Pro | Marketing Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @if ($cssAsset)
        <link rel="stylesheet" href="{{ asset($cssAsset) }}">
    @endif
    @foreach ($jsCssAssets as $cssDep)
        <link rel="stylesheet" href="{{ asset('build/' . $cssDep) }}">
    @endforeach
</head>
<body class="bg-slate-950 text-slate-100 antialiased">
    <div id="app"></div>

    @if ($jsAsset)
        <script type="module" src="{{ asset($jsAsset) }}"></script>
    @endif
</body>
</html>
