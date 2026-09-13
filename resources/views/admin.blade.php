<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Poriseba Admin</title>

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
