<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Poriseba Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @php
        $manifestPath = public_path('build/manifest.json');

        $manifest = file_exists($manifestPath)
            ? json_decode(file_get_contents($manifestPath), true)
            : [];

        $entry = $manifest['resources/js/main.js'] ?? null;
    @endphp

    @if($entry && isset($entry['css']))
        @foreach($entry['css'] as $css)
            <link rel="stylesheet"
                  href="{{ asset('build/' . $css) }}">
        @endforeach
    @endif
</head>

<body>

<div id="app"></div>

@if($entry)
    <script
        type="module"
        src="{{ asset('build/' . $entry['file']) }}">
    </script>
@endif

</body>
</html>
