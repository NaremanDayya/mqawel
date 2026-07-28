<meta charset="UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>{{ __('frontend.webpage_title') }}</title>

<!-- Primary Meta Tags -->
<meta name="title" content="Muqawil Plus">
<meta name="description" content="A complete system that manages contracting projects, workers, and storage">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="">
<meta property="og:title" content="A complete system that manages contracting projects, workers, and storage">
<meta property="og:description" content="A complete system that manages contracting projects, workers, and storage">
<meta property="og:image" content="">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="">
<meta property="twitter:title" content="A complete system that manages contracting projects, workers, and storage">
<meta property="twitter:description"
    content="A complete system that manages contracting projects, workers, and storage">
<meta property="twitter:image" content="{{ asset('images/logo-light.png') }}">

<!--====== Favicon Icon ======-->
<link rel="shortcut icon" href="{{ asset('images/logo_white.png') }}" type="image/svg" />

<!-- ===== Legacy CSS files (still required by non-landing frontend pages: sign-up, policies) ===== -->
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/lineicons.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/ud-styles.css') }}" />

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- ===== New landing page design (Cairo font + custom design system) ===== -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

@vite(['resources/css/frontend/landing.css'])
