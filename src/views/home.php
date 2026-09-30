<?php
include __DIR__ . '/partials/header.php';

$gallery_categories = $gallery_categories ?? [];
$galleryFallback = ViewHelper::asset('images/about-hotel.jpg');
$resolveGalleryImage = function ($path) use ($galleryFallback) {
    if (!$path) return $galleryFallback;
    if (preg_match('#^https?://#', $path) === 1) return $galleryFallback;
    $relativePath = ltrim($path, '/');
    $localPath = BASE_PATH . '/assets/' . $relativePath;
    if (!is_file($localPath)) return $galleryFallback;
    return ViewHelper::asset($relativePath);
};

$homepageGalleryImages = $gallery_images ?? [];
$spa_images = $spa_images ?? [];
while (count($spa_images) < 4) {
    $spa_images[] = $about_image ?? $galleryFallback;
}

$homeGalleryPayload = [];
foreach ($homepageGalleryImages as $image) {
    $homeGalleryPayload[] = [
        'src' => $resolveGalleryImage($image['image_path'] ?? ''),
        'title' => $image['title'] ?? 'Gallery',
        'category' => $image['category_slug'] ?? ($image['category'] ?? 'all'),
        'alt' => $image['title'] ?? 'Gallery',
    ];
}

$roomKeyMap = [
    'king' => 'K',
    'twin' => 'T',
    'deluxe' => 'D',
    'junior' => 'JS',
    'executive' => 'ES',
];
$roomDescFallback = [
    'K' => 'A calm, spacious room with a king bed for one guest or a couple. The room most business travellers choose.',
    'T' => 'Two single beds for colleagues on the same trip, or friends sharing the adventure.',
    'D' => 'Extra space and views across Bole, for longer stays and guests who like room to spread out.',
    'JS' => 'A bedroom with its own sitting area, so you can take a call or host a colleague without working from the bed.',
    'ES' => 'Our most generous space, with a separate living area, for senior delegates and long stays.',
];
$roomsPayload = [];
foreach (($rooms ?? []) as $room) {
    $type = (string) ($room['type'] ?? '');
    $key = 'R';
    foreach ($roomKeyMap as $needle => $code) {
        if (stripos($type, $needle) !== false) {
            $key = $code;
            break;
        }
    }
    $name = trim($type) !== '' ? $type : 'Room';
    if (stripos($name, 'room') === false && stripos($name, 'suite') === false) {
        $name .= ' Room';
    }
    $roomsPayload[] = [
        'key' => $key,
        'name' => $name,
        'img' => $room['display_image'] ?? ($rooms_image ?? $galleryFallback),
        'desc' => $roomDescFallback[$key] ?? 'Designed for your comfort at Azzeman Hotel.',
        'chips' => $room['chips'] ?? ['Wi-Fi', 'Air conditioning'],
        'bookingType' => $type,
    ];
}

$hallDesc = [
    'Entoto' => 'Named for the eucalyptus-covered range that rises over the capital.',
    'Ras Dashen' => 'Named for Ethiopia\'s highest peak, high in the Simien Mountains.',
    'Jegol' => 'Named for the historic walls around the old city of Harar.',
    'Sofumer' => 'Named for the vast, river-carved cave system of Bale.',
    'Sof Omar' => 'Named for the vast, river-carved cave system of Bale.',
    'Tiya' => 'Named for the UNESCO-listed carved stones of Tiya.',
];
$hallCoords = [
    'Entoto' => ['x' => 115, 'y' => 108, 'lx' => -44, 'ly' => -4],
    'Ras Dashen' => ['x' => 107, 'y' => 35, 'lx' => 10, 'ly' => 3],
    'Jegol' => ['x' => 182, 'y' => 114, 'lx' => 9, 'ly' => 3],
    'Sofumer' => ['x' => 157, 'y' => 162, 'lx' => 9, 'ly' => 3],
    'Sof Omar' => ['x' => 157, 'y' => 162, 'lx' => 9, 'ly' => 3],
    'Tiya' => ['x' => 140, 'y' => 145, 'lx' => 9, 'ly' => 3],
];
$hallsPayload = [];
foreach (($meeting_venues ?? []) as $i => $venue) {
    $name = $venue['name'] ?? ('Hall ' . ($i + 1));
    $coords = $hallCoords[$name] ?? ['x' => 120 + ($i * 12) % 60, 'y' => 80 + ($i * 18) % 80, 'lx' => 9, 'ly' => 3];
    $hallsPayload[] = [
        'name' => $name,
        'where' => $venue['capacity_note'] ?? '',
        'x' => $coords['x'],
        'y' => $coords['y'],
        'lx' => $coords['lx'],
        'ly' => $coords['ly'],
        'img' => $meetings_image ?? $galleryFallback,
        'desc' => $hallDesc[$name] ?? ('Flexible meeting space' . (!empty($venue['capacity_note']) ? ' · ' . $venue['capacity_note'] : '') . '.'),
    ];
}
?>

<!-- Redesign landing (backend booking + DB gallery preserved) -->

<a class="skip" href="#main">Skip to content</a>

<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-star" viewBox="0 0 24 24"><path fill="currentColor" d="m12 2.8 2.8 5.9 6.4.8-4.7 4.4 1.2 6.4L12 17.1l-5.7 3.2 1.2-6.4-4.7-4.4 6.4-.8L12 2.8Z"/></symbol>
  <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M4.5 12h15m-6-6 6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-chev" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-close" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
  <symbol id="i-check" viewBox="0 0 24 24"><path d="m5 12.5 4.2 4.2L19 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-plane" viewBox="0 0 24 24"><path fill="currentColor" d="M21 15.5v-1.8l-8-5V3.5a1.5 1.5 0 0 0-3 0v5.2l-8 5v1.8l8-2.5v5.1l-2 1.5V21l3.5-1 3.5 1v-1.4l-2-1.5V13l8 2.5Z"/></symbol>
  <symbol id="i-wifi" viewBox="0 0 24 24"><path d="M2.5 9a14 14 0 0 1 19 0M5.5 12.2a9.5 9.5 0 0 1 13 0M8.6 15.4a5 5 0 0 1 6.8 0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="18.6" r="1.5" fill="currentColor"/></symbol>
  <symbol id="i-award" viewBox="0 0 24 24"><circle cx="12" cy="9" r="5.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="m8.5 13.4-1.5 7.1 5-2.6 5 2.6-1.5-7.1" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></symbol>
  <symbol id="i-dish" viewBox="0 0 24 24"><path d="M3 16.5h18M5 16.5a7 7 0 0 1 14 0M12 7.5v2M9.5 20h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
  <symbol id="i-bell" viewBox="0 0 24 24"><path d="M4 17.5h16M6 17.5a6 6 0 0 1 12 0M12 9.5V8M10 6h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
  <symbol id="i-send" viewBox="0 0 24 24"><path d="M4 12 20 4l-6 16-2.5-6.5L4 12Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></symbol>
  <symbol id="i-phone" viewBox="0 0 24 24"><path d="M6.6 3.5h2.8l1.4 4.2-2 1.3a11 11 0 0 0 6.2 6.2l1.3-2 4.2 1.4v2.8a2 2 0 0 1-2.2 2A16.5 16.5 0 0 1 4.6 5.7a2 2 0 0 1 2-2.2Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></symbol>
  <symbol id="i-mail" viewBox="0 0 24 24"><rect x="3" y="5.5" width="18" height="13" rx="2.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m4 7 8 6 8-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></symbol>
  <symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-11.5A7 7 0 0 0 5 9.5C5 14.8 12 21 12 21Z" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="9.5" r="2.5" fill="currentColor"/></symbol>
  <symbol id="i-x" viewBox="0 0 24 24"><path fill="currentColor" d="M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.8-6.3L5.4 21H2.3l7.2-8.3L2 3h6.3l4.4 5.8L17.8 3Zm-1.1 16.2h1.7L7.4 4.7H5.6l11.1 14.5Z"/></symbol>
  <symbol id="i-tiktok" viewBox="0 0 24 24"><path fill="currentColor" d="M16.6 3c.3 2.2 1.6 3.6 3.9 3.8v2.6c-1.4.1-2.6-.3-3.9-1.1v5.9c0 7.6-8.3 9.9-11.6 4.5-2.1-3.5-.8-9.6 6.1-9.8v2.8c-.5.1-1.1.2-1.6.4-1.5.5-2.4 1.5-2.2 3.2.5 3.3 6.5 4.3 6-2.2V3h3.3Z"/></symbol>
  <symbol id="i-ig" viewBox="0 0 24 24"><rect x="3.5" y="3.5" width="17" height="17" rx="5" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="17.3" cy="6.7" r="1.1" fill="currentColor"/></symbol>
  <symbol id="i-fb" viewBox="0 0 24 24"><path fill="currentColor" d="M13.5 21v-7.5H16l.4-3h-2.9V8.6c0-.9.3-1.5 1.5-1.5h1.5V4.4c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.4H8.1v3h2.5V21h2.9Z"/></symbol>
  <symbol id="i-wa" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2.2a9.7 9.7 0 0 0-8.4 14.6L2.3 21.7l5-1.3A9.7 9.7 0 1 0 12 2.2Zm4.4 11.7c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.5.1l-.8.9c-.1.2-.3.2-.5.1a6.5 6.5 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.7-1.8c-.2-.5-.4-.4-.5-.4h-.5a.9.9 0 0 0-.7.3 2.8 2.8 0 0 0-.9 2.1c0 1.2.9 2.4 1 2.6.1.2 1.8 2.7 4.3 3.8 1.6.7 2.2.7 3 .6.5-.1 1.4-.6 1.6-1.1.2-.6.2-1 .1-1.1l-.5-.3Z"/></symbol>
  <!-- Azzeman mark: a 13-segment sun dial -->
  <symbol id="i-mark" viewBox="0 0 40 40">
    <circle cx="20" cy="20" r="17" fill="none" stroke="#7A6960" stroke-width="6" stroke-dasharray="6.2 2"/>
    <circle cx="20" cy="20" r="7.5" fill="#0E8040"/>
    <path d="M20 20 L20 12.5" stroke="#FFFDF9" stroke-width="2" stroke-linecap="round"/>
  </symbol>
</svg>

<!-- ================= NAV ================= -->
<header class="nav" id="nav">
  <nav class="nav-in" aria-label="Main">
    <a class="brand" href="#top" aria-label="Azzeman Hotel, home">
      <img class="brand-mark" src="<?= ViewHelper::asset('images/logo.png') ?>" alt="" width="34" height="34" style="object-fit:contain;border-radius:8px;" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='block');">
      <svg class="brand-mark" aria-hidden="true" style="display:none"><use href="#i-mark"/></svg>
      <span><b>Azzeman</b><small>Hotel · Addis Ababa</small></span>
    </a>
    <ul class="links" id="links">
      <li><a href="#about">About</a></li>
      <li><a href="#rooms">Rooms</a></li>
      <li><a href="#services">Services</a></li>
      <li><a href="#meetings">Meetings &amp; Events</a></li>
      <li><a href="#gallery">Gallery</a></li>
      <li><a href="#tour">Virtual tour</a></li>
      <li><a href="#contact">Contact</a></li>
    </ul>
    <span class="nav-clock"><b id="nav-addis">--:--</b><span id="nav-eth">Ethiopian time</span></span>
    <a class="btn btn-sun" href="#book">Book now</a>
    <button class="burger" id="burger" aria-expanded="false" aria-controls="links">
      <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8h16M4 16h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      <span class="sr">Menu</span>
    </button>
  </nav>
</header>

<main id="main">
<span id="top"></span>

<!-- ================= HERO ================= -->
<section class="hero shell" id="home" aria-labelledby="hero-title">
  <div>
    <div class="welcome"><span class="geez" lang="am">እንኳን ደህና መጡ</span><span>Welcome, in Amharic</span></div>
    <h1 id="hero-title">
      <span class="ln"><span>Your time</span></span>
      <span class="ln"><span>in Addis</span></span>
      <span class="ln"><span>starts at</span></span>
      <span class="ln"><span><span class="hl">the gate.</span></span></span>
    </h1>
    <p class="lead">Unparalleled hospitality, ten minutes from Bole International Airport. A four-star hotel where luxury meets comfort in the heart of Bole.</p>
    <div class="hero-cta">
      <a class="btn btn-sun" href="#book">Book your stay <svg class="go" width="18" height="18" aria-hidden="true"><use href="#i-arrow"/></svg></a>
      <a class="btn btn-line" href="#tour" onclick="if(typeof openVirtualTourModal==='function'){openVirtualTourModal();} return false;">Take the virtual tour</a>
    </div>
    <div class="stars"><i aria-hidden="true"><svg width="15" height="15"><use href="#i-star"/></svg><svg width="15" height="15"><use href="#i-star"/></svg><svg width="15" height="15"><use href="#i-star"/></svg><svg width="15" height="15"><use href="#i-star"/></svg></i><span>Four-star hotel · Bole, Addis Ababa</span></div>
  </div>

  <div class="dial-wrap" aria-label="A dial of the thirteen Ethiopian months, framing photos of the hotel">
    <svg class="dial" viewBox="0 0 400 400" id="dial">
      <defs>
        <clipPath id="dial-clip"><circle cx="200" cy="200" r="118"/></clipPath>
        <path id="label-path" d="M200,200 m-155,0 a155,155 0 1,1 310,0 a155,155 0 1,1 -310,0"/>
      </defs>
      <circle cx="200" cy="200" r="198" fill="#FFFDF9"/>
      <g class="ring" id="dial-ring"></g>
      <g id="dial-ticks"></g>
      <circle cx="200" cy="200" r="124" fill="#fff"/>
      <g clip-path="url(#dial-clip)" id="dial-photos"></g>
      <circle cx="200" cy="200" r="118" fill="none" stroke="#fff" stroke-width="6"/>
      <g class="hand" id="dial-hand"><path d="M200 200 L200 88" stroke="#24160F" stroke-width="3" stroke-linecap="round"/><circle cx="200" cy="88" r="6" fill="#F3B21B" stroke="#24160F" stroke-width="2"/></g>
      <circle cx="200" cy="200" r="8" fill="#24160F"/>
    </svg>
    <div class="chip c1"><span>Your time</span><b id="t-you">--:--</b></div>
    <div class="chip c2"><span>Addis Ababa</span><b id="t-addis">--:--</b></div>
    <div class="chip c3"><span id="t-eth-label">Ethiopian time</span><b id="t-eth">--</b></div>
  </div>
</section>

<div class="tibeb-band" aria-hidden="true"></div>

<!-- ================= BOARDING PASS (booking) ================= -->
<section class="pass-sec band shell" id="book" aria-labelledby="book-title" style="padding-top:clamp(60px,8vw,110px)">
  <span class="tag rv">Book your stay</span>
  <h2 id="book-title" class="rv" style="max-width:18ch;margin-bottom:clamp(30px,4vw,50px)">Your boarding pass to Azzeman.</h2>

  <!-- No booking engine yet: this sends a pre-filled request to reservations on
       WhatsApp. Point the submit handler at a booking engine when one exists. -->
  <form class="pass pop" id="pass" novalidate style="--r:-1deg">
    <div class="pass-main">
      <div class="pass-top"><span class="t">AZZEMAN · BOARDING PASS</span><span class="cls">Book direct · best rate</span></div>
      <div class="route-row">
        <div class="code">ADD<small>Bole International</small></div>
        <div class="flight"><span class="plane"><svg width="18" height="18" style="transform:rotate(90deg)" aria-hidden="true"><use href="#i-plane"/></svg></span><span>≈ 10 min · 2 km</span></div>
        <div class="code right">AZM<small>Azzeman Hotel</small></div>
      </div>
      <div class="fields">
        <div class="fld"><label for="checkin">ARRIVAL</label><input type="date" id="checkin" required /></div>
        <div class="fld"><label for="checkout">DEPARTURE</label><input type="date" id="checkout" required /></div>
        <div class="fld"><label for="guests">PASSENGERS</label>
          <select id="guests"><option>1 adult</option><option selected>2 adults</option><option>3 adults</option><option>2 adults, 1 child</option><option>Group (5+)</option></select></div>
        <div class="fld"><label for="roomtype">CLASS</label>
          <select id="roomtype"><option>Any room</option><option>King Room</option><option>Twin Room</option><option>Deluxe Room</option><option>Junior Suite</option><option>Executive Suite</option></select></div>
      </div>
    </div>
    <div class="pass-stub">
      <div class="stub-row"><span>Gate<b>Arrivals</b></span><span>Shuttle<b>On request</b></span><span>Nights<b id="nights">1</b></span></div>
      <div class="barcode" aria-hidden="true"></div>
      <p class="pass-err" id="pass-err" role="alert"></p>
      <button class="btn btn-sun" type="submit">Check availability <svg class="go" width="18" height="18" aria-hidden="true"><use href="#i-arrow"/></svg></button>
    </div>
  </form>
  <div class="perks rv">
    <span><svg width="16" height="16" aria-hidden="true"><use href="#i-check"/></svg> Best rate when you book direct</span>
    <span><svg width="16" height="16" aria-hidden="true"><use href="#i-check"/></svg> Airport shuttle</span>
    <span><svg width="16" height="16" aria-hidden="true"><use href="#i-check"/></svg> Free high-speed Wi-Fi</span>
    <span><svg width="16" height="16" aria-hidden="true"><use href="#i-check"/></svg> Reservations reply on WhatsApp</span>
  </div>
</section>

<div class="pill-book" id="pill-book" aria-hidden="true"><span>Ready when you are</span><a class="btn btn-sun" href="#book" tabindex="-1">Book your stay</a></div>

<!-- ================= ABOUT / STORY ================= -->
<section class="band shell" id="about" aria-labelledby="about-title" style="padding-top:0">
  <div class="story">
    <div>
      <span class="tag rv">Welcome to Azzeman Hotel</span>
      <h2 id="about-title" class="sr">Welcome to Azzeman Hotel</h2>
      <p class="fill" id="fill">A four-star hotel in Bole, Addis Ababa's fastest-growing district. Two kilometres from Bole International Airport, next door to 2000 Habesha cultural restaurant, and surrounded by shops, cafés, restaurants, bars and clubs. A blend of modern luxury and timeless elegance.</p>
    </div>
    <div class="stack">
      <div class="frame f1 pop" style="--d:.05s"><img src="<?= ViewHelper::e($about_image) ?>" alt="The lobby of Azzeman Hotel" loading="lazy" /></div>
      <div class="frame f2 pop" style="--d:.2s"><img src="https://azzemanhotel.com/assets/uploads/gallery/gallery_6927f8ddb2b376.82487801.jpg" alt="The hotel restaurant" loading="lazy" /></div>
      <div class="badge" aria-label="Four stars"><div><span>4★</span>Bole · Addis</div></div>
    </div>
  </div>

  <div class="feats">
    <article class="feat rv"><div class="ic"><svg width="24" height="24" aria-hidden="true"><use href="#i-award"/></svg></div><h3>Award-winning service</h3><p>Recognised for exceptional hospitality and guest satisfaction.</p></article>
    <article class="feat rv" style="--d:.1s"><div class="ic" style="color:var(--saffron-deep)"><svg width="24" height="24" aria-hidden="true"><use href="#i-wifi"/></svg></div><h3>Complimentary Wi-Fi</h3><p>Stay connected with high-speed internet throughout the hotel.</p></article>
    <article class="feat rv" style="--d:.2s"><div class="ic" style="color:var(--red)"><svg width="24" height="24" aria-hidden="true"><use href="#i-dish"/></svg></div><h3>Gourmet dining</h3><p>Indulge in Ethiopian and international cooking at our on-site restaurant.</p></article>
  </div>

  <div class="rv" style="margin-top:clamp(40px,5vw,64px)">
    <h3>In every room</h3>
    <div class="amen">
      <span>Flat-screen TV</span><span>Coffee &amp; tea maker</span><span>Electric kettle</span><span>Wi-Fi</span><span>Minibar</span><span>Writing table</span><span>Air conditioning</span><span>Shower</span><span>Safety channels</span><span>Safe deposit box</span><span>Wardrobe</span><span>Hair dryer</span><span>Coffee table with chairs</span>
    </div>
  </div>
</section>

<!-- ================= SERVICES: SKY SLIDER ================= -->
<section class="sky band" id="services" data-phase="dawn" aria-labelledby="sky-title">
  <div class="shell">
    <div class="sky-head">
      <div>
        <span class="tag">Our services</span>
        <h2 id="sky-title">A day at Azzeman,<br/>on two clocks.</h2>
      </div>
      <p class="lead" style="max-width:40ch">Ethiopia counts the hours from sunrise, so 7 in the morning is 1 o'clock here. Move the sun through a day at the hotel.</p>
    </div>

    <div class="arc" id="arc" aria-hidden="true">
      <svg viewBox="0 0 1000 200" preserveAspectRatio="none"><path class="path" id="arc-path" d="M20 190 Q 500 -150 980 190"/></svg>
      <svg class="skyline" viewBox="0 0 1000 44" preserveAspectRatio="none"><path fill="currentColor" d="M0 44V30h40v-8h30v14h26V18h18v-8h14v8h20v26h30V26h44v18h20V14h10V6h8v8h12v30h34V24h50v20h28V20h22v24h44V30h36V12h16v32h40V26h60v18h30V16h20v28h46V28h30v16h24V8h8v-6h4v6h8v36h40V22h60v22h36V30h40v14Z"/></svg>
      <span class="orb" id="orb"></span>
    </div>

    <div class="hours" id="hours" role="tablist" aria-label="Times of day"></div>

    <div class="moment" id="moment" role="tabpanel" aria-live="polite">
      <div class="frame"><img id="m-img" src="https://azzemanhotel.com/assets/uploads/gallery/gallery_6927f8ddb2b376.82487801.jpg" alt="" loading="lazy" /></div>
      <div class="txt">
        <div class="clocks"><span>Addis <b id="m-addis"></b></span><span>Ethiopian time <b id="m-eth"></b></span></div>
        <h3 id="m-title"></h3>
        <p class="lead" id="m-desc" style="margin:0"></p>
      </div>
    </div>
  </div>
</section>

<!-- ================= ROOMS: ELEVATOR ================= -->
<section class="band shell" id="rooms" aria-labelledby="rooms-title">
  <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-end;gap:24px;margin-bottom:clamp(34px,4vw,56px)">
    <div class="rv">
      <span class="tag">Our rooms &amp; suites</span>
      <h2 id="rooms-title">Press a button.<br/>Step inside.</h2>
    </div>
    <p class="lead rv" style="--d:.1s">Designed for your comfort, a peaceful retreat. From cosy King rooms to spacious suites with private amenities, find your ideal sanctuary.</p>
  </div>

  <div class="lift">
    <div class="panel rv">
      <div class="led" aria-live="polite"><small>NOW SHOWING</small><b id="led">KING ROOM</b></div>
      <div class="lift-btns" id="lift-btns" role="group" aria-label="Room types"></div>
      <p class="note">All rooms: flat-screen TV, Wi-Fi, minibar, writing table, air conditioning, safe, coffee &amp; tea maker.</p>
    </div>
    <div class="car rv" id="car" style="--d:.1s">
      <div class="frame"><img id="car-img" src="https://azzemanhotel.com/assets/uploads/site-images/rooms_image_1782464244.jpeg" alt="King Room at Azzeman Hotel" loading="lazy" /></div>
      <div class="room-info">
        <div>
          <h3 id="r-name">King Room</h3>
          <p id="r-desc"></p>
          <div class="chips" id="r-chips"></div>
        </div>
        <a class="btn btn-sun" href="#book" id="r-book">Book this room</a>
      </div>
      <div class="door l" aria-hidden="true"></div>
      <div class="door r" aria-hidden="true"></div>
    </div>
  </div>
</section>

<!-- ================= BRAND MOMENT ================= -->
<section class="brand-moment" id="bm" aria-hidden="true">
  <div class="bm-stage">
    <span class="bm-geez" lang="am">አዜማን ሆቴል</span>
    <div class="bm-word" id="bm-word">
      <span style="--i:0">A</span><span style="--i:1">Z</span><span style="--i:2">Z</span><span style="--i:3">E</span><span style="--i:4">M</span><span style="--i:5">A</span><span style="--i:6">N</span>
    </div>
    <p class="bm-cap">Woven like tibeb. Rooted in Bole. Open to the world.</p>
  </div>
</section>
<!-- CONFIRM: the hotel's own Amharic spelling of its name before launch -->

<!-- ================= SPA ================= -->
<section class="band shell" id="spa" aria-labelledby="spa-title">
  <div class="spa">
    <div class="breath">
      <div class="ph p1 pop" style="--r:-6deg"><img src="<?= ViewHelper::e($spa_images[0] ?? $about_image) ?>" alt="Spa treatment room" loading="lazy" /></div>
      <div class="ph p2 pop" style="--r:5deg;--d:.1s"><img src="<?= ViewHelper::e($spa_images[1] ?? $about_image) ?>" alt="Spa interior" loading="lazy" /></div>
      <div class="ph p3 pop" style="--r:4deg;--d:.2s"><img src="<?= ViewHelper::e($spa_images[2] ?? $about_image) ?>" alt="Spa details" loading="lazy" /></div>
      <div class="ph p4 pop" style="--r:-3deg;--d:.3s"><img src="<?= ViewHelper::e($spa_images[3] ?? $about_image) ?>" alt="Spa and fitness area" loading="lazy" /></div>
      <button class="orb-btn idle" id="orb-btn" aria-live="polite"><span id="orb-text">Breathe with us<small>Tap to start</small></span></button>
    </div>
    <div>
      <span class="tag rv">Relax &amp; rejuvenate</span>
      <h2 id="spa-title" class="rv">Escape to a world of tranquillity.</h2>
      <p class="lead rv" style="margin-top:22px">Our spa offers a full menu of treatments to soothe mind, body and soul. From therapeutic massages to revitalising facials, our expert therapists use only the finest natural ingredients.</p>
      <ul class="spec rv">
        <li><b>Massage</b><span>Therapeutic and relaxing</span></li>
        <li><b>Facials</b><span>Natural ingredients</span></li>
        <li><b>Sauna &amp; steam</b><span>Heat after a long flight</span></li>
        <li><b>Fitness centre</b><span>Keep your routine</span></li>
      </ul>
      <a class="btn btn-green rv" href="#spa" onclick="if(typeof openSpaBookingModal==='function'){openSpaBookingModal();} return false;">Book a treatment <svg class="go" width="18" height="18" aria-hidden="true"><use href="#i-arrow"/></svg></a>
    </div>
  </div>
</section>

<!-- ================= MEETINGS & EVENTS: MAP ================= -->
<section class="shell" id="meetings" aria-labelledby="meet-title">
  <div class="meet">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-end;gap:24px">
      <div>
        <span class="tag light">Meetings &amp; events</span>
        <h2 id="meet-title">Four halls. Four landmarks.</h2>
      </div>
      <p class="lead" style="max-width:44ch">Host your next successful event in versatile, technologically advanced spaces. Each hall carries the name of a place in Ethiopia; our events team takes care of every step.</p>
    </div>

    <div class="meet-grid">
      <div class="eth-map">
        <svg viewBox="-12 -12 324 252" role="img" aria-label="Illustrative map of Ethiopia marking the four landmarks">
          <path class="land" d="M68 12 80 14 98 2 110 12 122 6 140 10 158 18 174 32 188 50 176 68 198 80 206 110 220 120 260 132 298 140 270 170 240 200 220 202 200 208 180 220 164 222 156 216 136 230 120 230 102 228 78 212 58 208 46 192 34 168 20 156 0 140 0 132 22 128 22 110 32 84 46 72 58 48 62 30Z"/>
          <ellipse class="contour" cx="110" cy="70" rx="56" ry="44"/><ellipse class="contour" cx="110" cy="70" rx="34" ry="26"/>
          <ellipse class="contour" cx="150" cy="150" rx="46" ry="30"/><ellipse class="contour" cx="150" cy="150" rx="24" ry="14"/>
          <g id="map-pins"></g>
          <g><path class="addis" d="M117 128l2.2 4.6 5 .6-3.7 3.4 1 4.9-4.5-2.5-4.5 2.5 1-4.9-3.7-3.4 5-.6Z"/><text x="84" y="152" fill="#fff" font-size="8" font-weight="600">Azzeman · Addis Ababa</text></g>
        </svg>
        <p class="map-note">Illustrative map, not to scale.</p>
      </div>
      <div>
        <!-- ADD capacity per layout (theatre / classroom / banquet) when available -->
        <article class="hall-card" id="hall-card">
          <div class="frame"><img id="h-img" src="https://azzemanhotel.com/assets/uploads/gallery/gallery_6926da72e3fd56.52104420.jpg" alt="" loading="lazy" /></div>
          <div class="body">
            <div class="where" id="h-where"></div>
            <h3 id="h-name"></h3>
            <p class="muted" id="h-desc" style="margin-bottom:18px"></p>
            <a class="btn btn-green" id="h-mail" href="#meetings" onclick="if(typeof openMeetingBookingModal==='function'){openMeetingBookingModal();} return false;">Enquire now <svg class="go" width="18" height="18" aria-hidden="true"><use href="#i-arrow"/></svg></a>
          </div>
        </article>
        <div class="hall-tabs" id="hall-tabs" role="group" aria-label="Meeting halls"></div>
      </div>
    </div>

    <div class="meet-feats">
      <span><svg width="16" height="16" aria-hidden="true"><use href="#i-check"/></svg> State-of-the-art A/V equipment</span>
      <span><svg width="16" height="16" aria-hidden="true"><use href="#i-check"/></svg> Flexible spaces for various group sizes</span>
      <span><svg width="16" height="16" aria-hidden="true"><use href="#i-check"/></svg> Customised catering options</span>
      <span><svg width="16" height="16" aria-hidden="true"><use href="#i-check"/></svg> Dedicated events team</span>
    </div>
  </div>
</section>

<!-- ================= VIRTUAL TOUR: SPOTLIGHT ================= -->
<section class="band shell" id="tour" aria-labelledby="tour-title">
  <div class="tour rv" id="tour-box">
    <div class="base"><img src="https://azzemanhotel.com/assets/uploads/gallery/gallery_6a3e3f80e5ba42.08198011.jpg" alt="" loading="lazy" /></div>
    <div class="sharp"><img src="https://azzemanhotel.com/assets/uploads/gallery/gallery_6a3e3f80e5ba42.08198011.jpg" alt="The lobby of Azzeman Hotel" loading="lazy" /></div>
    <span class="ring" aria-hidden="true"></span>
    <span class="badge360" aria-hidden="true">360°</span>
    <div class="tour-ui">
      <div>
        <span class="tag light">Take the virtual tour</span>
        <h2 id="tour-title">Look around before you land.</h2>
      </div>
      <a class="btn btn-sun" href="#tour" onclick="if(typeof openVirtualTourModal==='function'){openVirtualTourModal();} return false;">Start the 360° tour <svg class="go" width="18" height="18" aria-hidden="true"><use href="#i-arrow"/></svg></a>
    </div>
  </div>
</section>

<!-- ================= GALLERY ================= -->
<section class="band shell" id="gallery" aria-labelledby="gallery-title" style="padding-top:0">
  <div class="gal-head">
    <div class="rv">
      <span class="tag">Our gallery</span>
      <h2 id="gallery-title">A glimpse of the<br/>Azzeman experience.</h2>
    </div>
    <div class="filters rv" role="group" aria-label="Filter photos" style="--d:.1s">
      <button class="filter" aria-pressed="true" data-f="all">All</button>
      <?php foreach ($gallery_categories as $cat): ?>
      <button class="filter" aria-pressed="false" data-f="<?= ViewHelper::e($cat['id'] ?? $cat['name'] ?? '') ?>"><?= ViewHelper::e($cat['name'] ?? '') ?></button>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="masonry" id="masonry"></div>
</section>

<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Photo viewer">
  <button class="lb-btn lb-close" id="lb-close" aria-label="Close"><svg width="20" height="20" aria-hidden="true"><use href="#i-close"/></svg></button>
  <button class="lb-btn lb-prev" id="lb-prev" aria-label="Previous photo"><svg width="20" height="20" style="transform:scaleX(-1)" aria-hidden="true"><use href="#i-chev"/></svg></button>
  <figure><img id="lb-img" src="" alt="" /><figcaption id="lb-cap"></figcaption></figure>
  <button class="lb-btn lb-next" id="lb-next" aria-label="Next photo"><svg width="20" height="20" aria-hidden="true"><use href="#i-chev"/></svg></button>
</div>

<!-- ================= GUEST POSTCARDS ================= -->
<section class="band cards-sec" id="cards" aria-labelledby="cards-title">
  <div class="shell reviews">
    <div>
      <span class="tag rv">What our guests say</span>
      <h2 id="cards-title" class="rv">Postcards from our guests.</h2>
      <div class="score rv">
        <!-- VERIFY and keep current: from the hotel's Hotels.com listing -->
        <div class="postmark"><div><b>8.4</b>OUT OF 10</div></div>
        <p class="muted" style="margin:0;max-width:22ch">Rated "Very good" by guests on Hotels.com</p>
      </div>
      <div class="deck-nav">
        <button class="round" id="pc-prev" aria-label="Previous postcard"><svg width="18" height="18" style="transform:scaleX(-1)" aria-hidden="true"><use href="#i-arrow"/></svg></button>
        <button class="round" id="pc-next" aria-label="Next postcard"><svg width="18" height="18" aria-hidden="true"><use href="#i-arrow"/></svg></button>
      </div>
    </div>
    <!-- Guest quotes from the current site; spelling lightly corrected, room numbers removed -->
    <div class="deck" id="deck">
      <button class="postcard" data-i="0" aria-label="Postcard from Sara, ECDD">
        <span class="msg">Thank you for your service. The care you give to guests with disabilities was fantastic. Thank you again!</span>
        <span class="side"><span class="stamp"><i style="background:var(--green)">ET</i>ECDD</span><span class="lines" aria-hidden="true"><i></i><i></i><i></i></span><span class="from"><b>Sara</b>ECDD</span></span>
      </button>
      <button class="postcard" data-i="1" aria-label="Postcard from Vinansio, Spain">
        <span class="msg">May God continue blessing the staff of this hotel.</span>
        <span class="side"><span class="stamp"><i style="background:var(--red)">ES</i>SPAIN</span><span class="lines" aria-hidden="true"><i></i><i></i><i></i></span><span class="from"><b>Vinansio</b>Spain</span></span>
      </button>
      <button class="postcard" data-i="2" aria-label="Postcard from Joseph Boateng, Ghana">
        <span class="msg">I never expected this kind of hospitality. Everything was great!</span>
        <span class="side"><span class="stamp"><i style="background:var(--saffron-deep)">GH</i>GHANA</span><span class="lines" aria-hidden="true"><i></i><i></i><i></i></span><span class="from"><b>Joseph Boateng</b>Ghana</span></span>
      </button>
    </div>
  </div>
</section>

<!-- ================= CONTACT: ARRIVALS BOARD ================= -->
<section class="band shell" id="contact" aria-labelledby="contact-title">
  <div class="rv" style="margin-bottom:clamp(34px,4vw,56px)">
    <span class="tag">Get in touch</span>
    <h2 id="contact-title">We'd love to hear from you.</h2>
    <p class="lead" style="margin-top:18px">Contact us for reservations or enquiries. Here's what's around us in Bole.</p>
  </div>
  <div class="contact-grid">
    <div>
      <div class="board rv" id="board">
        <div class="board-top"><b>NEARBY · FROM AZZEMAN</b><span>Distances approximate</span></div>
        <div class="brow h"><span>DESTINATION</span><span>DISTANCE</span><span>STATUS</span></div>
      </div>
      <div class="map rv">
        <iframe title="Map showing Azzeman Hotel in Bole, Addis Ababa" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.752396347101!2d38.77929697485987!3d9.001547091079313!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x164b8506bfc637e3%3A0x506e5392de010802!2sAzzeman%20Hotel!5e0!3m2!1sen!2sus!4v1722026115907!5m2!1sen!2sus"></iframe>
      </div>
    </div>
    <div class="contact-cards">
      <a class="ccard rv" href="https://maps.google.com/?q=Azzeman+Hotel+Addis+Ababa" target="_blank" rel="noopener"><span class="ci"><svg width="22" height="22" aria-hidden="true"><use href="#i-pin"/></svg></span><span><small>Address</small><b>Bole Sub-City, Woreda 03, Addis Ababa</b></span></a>
      <a class="ccard rv" style="--d:.06s" href="tel:+251116393132"><span class="ci"><svg width="22" height="22" aria-hidden="true"><use href="#i-phone"/></svg></span><span><small>Phone</small><b>+251 116 393 132</b></span></a>
      <a class="ccard rv" style="--d:.12s" href="mailto:info@azzemanhotel.com"><span class="ci"><svg width="22" height="22" aria-hidden="true"><use href="#i-mail"/></svg></span><span><small>Email</small><b>info@azzemanhotel.com</b></span></a>
      <a class="ccard rv" style="--d:.18s" href="https://api.whatsapp.com/send?phone=251939767676" target="_blank" rel="noopener"><span class="ci" style="color:#1FA855"><svg width="22" height="22" aria-hidden="true"><use href="#i-wa"/></svg></span><span><small>Chat on WhatsApp</small><b>+251 939 767 676</b></span></a>
    </div>
  </div>
</section>

<!-- ================= FINALE ================= -->
<section class="band shell finale" id="finale" aria-labelledby="finale-title" style="padding-top:0">
  <span class="tag rv" style="justify-content:center">Your home away from home</span>
  <h2 id="finale-title" class="rv">Land. Breathe. You're already here.</h2>
  <p class="lead rv" style="--d:.1s">Book direct for our best rate, and tell us your flight so we can meet you at the gate.</p>
  <div class="actions rv" style="--d:.2s">
    <a class="btn btn-sun" href="#book">Book your stay <svg class="go" width="18" height="18" aria-hidden="true"><use href="#i-arrow"/></svg></a>
    <a class="btn btn-line" href="https://api.whatsapp.com/send?phone=251939767676" target="_blank" rel="noopener">Chat on WhatsApp</a>
  </div>
</section>
</main>

<!-- ================= FOOTER ================= -->
<footer class="footer">
  <div class="tibeb-band" aria-hidden="true"></div>
  <div class="shell inner">
    <div class="cols">
      <div>
        <a class="brand" href="#top" aria-label="Azzeman Hotel, home">
          <img class="brand-mark" src="<?= ViewHelper::asset('images/logo.png') ?>" alt="" width="34" height="34" style="object-fit:contain;border-radius:8px;" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='block');">
          <svg class="brand-mark" aria-hidden="true" style="display:none"><use href="#i-mark"/></svg>
          <span><b>Azzeman</b><small>Hotel · Addis Ababa</small></span>
        </a>
        <p class="brand-blurb">Experience the pinnacle of luxury and comfort at Azzeman Hotel, your home away from home in the vibrant heart of the city.</p>
        <!-- REPLACE # with the hotel's real social profiles -->
        <div class="socials">
          <a href="#" aria-label="Azzeman Hotel on Facebook"><svg width="18" height="18" aria-hidden="true"><use href="#i-fb"/></svg></a>
          <a href="#" aria-label="Azzeman Hotel on X"><svg width="16" height="16" aria-hidden="true"><use href="#i-x"/></svg></a>
          <a href="#" aria-label="Azzeman Hotel on Instagram"><svg width="18" height="18" aria-hidden="true"><use href="#i-ig"/></svg></a>
          <a href="#" aria-label="Azzeman Hotel on TikTok"><svg width="18" height="18" aria-hidden="true"><use href="#i-tiktok"/></svg></a>
        </div>
      </div>
      <div><h4>Quick links</h4><ul><li><a href="#about">About us</a></li><li><a href="#rooms">Rooms</a></li><li><a href="#gallery">Gallery</a></li><li><a href="#services">Services</a></li><li><a href="#meetings">Meetings &amp; Events</a></li><li><a href="#contact">Contact</a></li></ul></div>
      <div><h4>Experience</h4><ul><li><a href="#spa">Spa &amp; fitness</a></li><li><a href="#tour">Virtual tour</a></li><li><a href="#book">Book now</a></li><li><a href="https://api.whatsapp.com/send?phone=251939767676" target="_blank" rel="noopener">WhatsApp</a></li></ul></div>
      <div><h4>Contact</h4><ul><li>Bole Sub-City, Woreda 03<br/>Addis Ababa, Ethiopia</li><li><a href="tel:+251116393132">+251 116 393 132</a></li><li><a href="mailto:info@azzemanhotel.com">info@azzemanhotel.com</a></li></ul></div>
    </div>
    <div class="giant" aria-hidden="true">AZZEMAN</div>
    <div class="base"><span>© <span id="year">2026</span> Azzeman Hotel. All rights reserved.</span><span id="foot-date"></span></div>
  </div>
</footer>

<!-- ================= CONCIERGE ================= -->
<!-- chatbot included from PHP partial -->

<?php
// Existing booking modals + virtual tour live in JS globals from header scripts.
// Keep WhatsApp + chatbot from current site.
?>
<a href="https://api.whatsapp.com/send?phone=251939767676" target="_blank" rel="noopener noreferrer" class="whatsapp-button" aria-label="Chat on WhatsApp with +251 939 767676">
    <svg xmlns="http://www.w3.org/2000/svg" height="28" width="28" viewBox="0 0 448 512" fill="currentColor">
        <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.8 0-67.6-9.5-97.8-27.2l-6.9-4-72.3 19 19.3-70.5-4.4-7.2c-19.4-31.4-29.7-68.3-29.7-106.3 0-108.6 88.4-197 197-197 52.9 0 102.7 20.5 139.3 57.1 36.6 36.6 57.1 86.5 57.1 139.3-.1 108.6-88.5 197-197.1 197zm101.9-138.3c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
    </svg>
    <span class="whatsapp-tooltip">Chat on WhatsApp with +251 939 767676</span>
</a>

<?php include __DIR__ . '/partials/chatbot.php'; ?>

<script>
window.HOME_GALLERY_ITEMS = <?= json_encode($homeGalleryPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
window.AZZEMAN_SITE_IMAGES = <?= json_encode(rtrim(ViewHelper::asset('uploads/site-images'), '/') . '/') ?>;
window.AZZEMAN_GALLERY_BASE = <?= json_encode(rtrim(ViewHelper::asset('uploads/gallery'), '/') . '/') ?>;
window.AZZEMAN_ROOMS_IMAGE = <?= json_encode($rooms_image ?? '') ?>;
window.AZZEMAN_ABOUT_IMAGE = <?= json_encode($about_image ?? '') ?>;
window.AZZEMAN_MEETINGS_IMAGE = <?= json_encode($meetings_image ?? '') ?>;
window.AZZEMAN_SPA_IMAGES = <?= json_encode(array_values($spa_images), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
window.AZZEMAN_ROOMS = <?= json_encode($roomsPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
window.AZZEMAN_HALLS = <?= json_encode($hallsPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
window.AZZEMAN_DIAL_PHOTOS = <?= json_encode(array_values(array_filter([
    $about_image ?? null,
    $rooms_image ?? null,
    $spa_images[0] ?? null,
    $meetings_image ?? null,
    $homeGalleryPayload[0]['src'] ?? null,
    $homeGalleryPayload[1]['src'] ?? null,
])), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="<?= ViewHelper::asset('js/azzeman-landing.js') ?>?v=6" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
</body>
</html>
