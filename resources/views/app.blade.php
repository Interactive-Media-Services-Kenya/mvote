<html>

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>

    <body class="bg-[#010101] font-sans antialiased">
        <div class="{{ $page['component'] === 'AudienceDisplay' ? '' : 'max-w-110 mx-auto' }} min-h-screen relative bg-brand-black shadow-[0_0_100px_rgba(0,0,0,0.8)]">
            @inertia
        </div>
    </body>

</html>
