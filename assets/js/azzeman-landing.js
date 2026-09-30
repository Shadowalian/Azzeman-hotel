
(function(){
  'use strict';
  var S = (window.AZZEMAN_SITE_IMAGES || 'https://azzemanhotel.com/assets/uploads/site-images/');
  var G = (window.AZZEMAN_GALLERY_BASE || 'https://azzemanhotel.com/assets/uploads/gallery/');
  var WA = '251939767676';
  var reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fine = matchMedia('(pointer: fine)').matches;
  var $ = function(id){ return document.getElementById(id); };
  var NS = 'http://www.w3.org/2000/svg';
  if ($('year')) $('year').textContent = new Date().getFullYear();

  /* =========================================================
     TIME, THE ETHIOPIAN WAY
     ========================================================= */
  var MONTHS = ['Meskerem','Tikimt','Hidar','Tahsas','Tir','Yekatit','Megabit','Miyazya','Ginbot','Sene','Hamle','Nehase','Pagume'];

  // Gregorian -> Julian Day Number -> Ethiopian date
  function toEthiopian(d){
    var y = d.y, m = d.m, day = d.d;
    var a = Math.floor((14 - m) / 12), yy = y + 4800 - a, mm = m + 12 * a - 3;
    var jdn = day + Math.floor((153 * mm + 2) / 5) + 365 * yy + Math.floor(yy / 4) - Math.floor(yy / 100) + Math.floor(yy / 400) - 32045;
    var r = (jdn - 1723856) % 1461, n = r % 365 + 365 * Math.floor(r / 1460);
    return { year: 4 * Math.floor((jdn - 1723856) / 1461) + Math.floor(r / 365) - Math.floor(r / 1460), month: Math.floor(n / 30) + 1, day: n % 30 + 1 };
  }
  // Ethiopian clock: hours are counted from 6:00 (sunrise)
  function ethClock(h, min){
    var e = (h + 6) % 12; if (e === 0) e = 12;
    var part = (h >= 6 && h < 18) ? 'in the day' : 'at night';
    return { text: e + ':' + String(min).padStart(2,'0'), part: part };
  }
  function addisParts(){
    var f = new Intl.DateTimeFormat('en-GB', { timeZone:'Africa/Addis_Ababa', hour:'2-digit', minute:'2-digit', year:'numeric', month:'2-digit', day:'2-digit', hourCycle:'h23' });
    var o = {}; f.formatToParts(new Date()).forEach(function(p){ o[p.type] = p.value; });
    return { h:+o.hour, min:+o.minute, y:+o.year, m:+o.month, d:+o.day };
  }
  var now = addisParts();
  var eDate = toEthiopian(now);

  function tickClocks(){
    var a = addisParts();
    var hhmm = String(a.h).padStart(2,'0') + ':' + String(a.min).padStart(2,'0');
    var e = ethClock(a.h, a.min);
    var you = new Intl.DateTimeFormat(undefined, { hour:'2-digit', minute:'2-digit' }).format(new Date());
    $('t-you').textContent = you;
    $('t-addis').textContent = hhmm;
    $('t-eth').textContent = e.text + ' ' + e.part;
    $('nav-addis').textContent = 'Addis ' + hhmm;
    $('nav-eth').textContent = e.text + ' ' + e.part + ', Ethiopian time';
    var ang = ((a.h % 12) + a.min / 60) * 30;
    $('dial-hand').style.transform = 'rotate(' + ang + 'deg)';
  }
  $('foot-date').textContent = 'Today in Ethiopia: ' + MONTHS[eDate.month - 1] + ' ' + eDate.day + ', ' + eDate.year;

  /* ---------- the 13-month dial ---------- */
  var ring = $('dial-ring'), cols = ['#D23C2A','#7A6960','#0E8040','#2356A8'];
  function polar(r, deg){ var a = (deg - 90) * Math.PI / 180; return [200 + r * Math.cos(a), 200 + r * Math.sin(a)]; }
  function arc(r1, r2, a1, a2){
    var p1 = polar(r2, a1), p2 = polar(r2, a2), p3 = polar(r1, a2), p4 = polar(r1, a1), large = a2 - a1 > 180 ? 1 : 0;
    return 'M' + p1 + ' A' + r2 + ' ' + r2 + ' 0 ' + large + ' 1 ' + p2 + ' L' + p3 + ' A' + r1 + ' ' + r1 + ' 0 ' + large + ' 0 ' + p4 + 'Z';
  }
  // 12 months of 30 days + Pagume (5 or 6), drawn to scale
  var days = MONTHS.map(function(_, i){ return i < 12 ? 30 : 5.25; });
  var total = days.reduce(function(a,b){ return a + b; }, 0), start = 0;
  MONTHS.forEach(function(name, i){
    var span = days[i] / total * 360, a1 = start + .9, a2 = start + span - .9, isNow = i === eDate.month - 1;
    var p = document.createElementNS(NS, 'path');
    p.setAttribute('d', arc(isNow ? 164 : 170, isNow ? 197 : 190, a1, a2));
    p.setAttribute('fill', isNow ? '#24160F' : cols[i % 4]);
    p.setAttribute('class','seg');
    ring.appendChild(p);
    var t = document.createElementNS(NS, 'text'); t.setAttribute('class', 'mlabel' + (isNow ? ' now' : ''));
    var tp = document.createElementNS(NS, 'textPath'); tp.setAttribute('href', '#label-path');
    tp.setAttribute('startOffset', ((start + span / 2) / 360 * 100 + 25) % 100 + '%'); tp.setAttribute('text-anchor','middle');
    tp.textContent = i === 12 ? 'PAG.' : name.toUpperCase();
    t.appendChild(tp); ring.appendChild(t);
    start += span;
  });
  // 12 hour ticks
  var ticks = $('dial-ticks');
  for (var k = 0; k < 60; k++){
    var q1 = polar(k % 5 ? 134 : 130, k * 6), q2 = polar(140, k * 6), ln = document.createElementNS(NS, 'line');
    ln.setAttribute('x1', q1[0]); ln.setAttribute('y1', q1[1]); ln.setAttribute('x2', q2[0]); ln.setAttribute('y2', q2[1]);
    ln.setAttribute('class', 'tick' + (k % 5 ? '' : ' major')); ticks.appendChild(ln);
  }
  // photos crossfading inside the dial
  var DIAL_PHOTOS = [S+'about_image_1782464231.jpeg', S+'rooms_image_1782464244.jpeg', G+'gallery_6927f8ddb2b376.82487801.jpg', S+'spa_image_1_1763964944.jpeg', G+'gallery_6926db12d6a119.81907360.jpg', G+'gallery_6927f9be4a4a81.96526898.jpg'];
  if (Array.isArray(window.AZZEMAN_DIAL_PHOTOS) && window.AZZEMAN_DIAL_PHOTOS.length) {
    DIAL_PHOTOS = window.AZZEMAN_DIAL_PHOTOS.filter(Boolean);
  }
  var ph = $('dial-photos'), phEls = DIAL_PHOTOS.map(function(src, i){
    var im = document.createElementNS(NS, 'image');
    im.setAttribute('href', src); im.setAttribute('x', 82); im.setAttribute('y', 82); im.setAttribute('width', 236); im.setAttribute('height', 236);
    im.setAttribute('preserveAspectRatio', 'xMidYMid slice'); im.setAttribute('class', 'photo'); im.style.opacity = i ? 0 : 1;
    ph.appendChild(im); return im;
  });
  var pi = 0;
  if (!reduced) setInterval(function(){ phEls[pi].style.opacity = 0; pi = (pi + 1) % phEls.length; phEls[pi].style.opacity = 1; }, 4200);
  tickClocks(); setInterval(tickClocks, 15000);

  /* ---------- nav ---------- */
  var nav = $('nav'), burger = $('burger'), links = $('links');
  burger.addEventListener('click', function(){
    var open = burger.getAttribute('aria-expanded') !== 'true';
    burger.setAttribute('aria-expanded', String(open)); links.classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  });
  links.addEventListener('click', function(e){ if (e.target.tagName === 'A'){ links.classList.remove('open'); burger.setAttribute('aria-expanded','false'); document.body.style.overflow = ''; } });

  /* =========================================================
     BOARDING PASS
     ========================================================= */
  var pass = $('pass'), inEl = $('checkin'), outEl = $('checkout'), nightsEl = $('nights'), err = $('pass-err');
  function iso(d){ return new Date(d.getTime() - d.getTimezoneOffset() * 6e4).toISOString().slice(0,10); }
  inEl.min = iso(new Date()); inEl.value = iso(new Date());
  outEl.min = iso(new Date(Date.now() + 864e5)); outEl.value = outEl.min;
  function nights(){ return Math.round((new Date(outEl.value) - new Date(inEl.value)) / 864e5); }
  function refresh(){ var n = nights(); nightsEl.textContent = n > 0 ? n : '–'; }
  inEl.addEventListener('change', function(){
    var next = iso(new Date(new Date(inEl.value + 'T12:00').getTime() + 864e5));
    outEl.min = next; if (!outEl.value || outEl.value <= inEl.value) outEl.value = next; refresh();
  });
  outEl.addEventListener('change', refresh);
    pass.addEventListener('submit', function(e){
    e.preventDefault();
    if (!$('cin').value || !$('cout').value){ alert('Please choose check-in and check-out dates.'); return; }
    if (typeof window.openBookingModal === 'function') {
      window.openBookingModal();
    } else {
      location.hash = '#book';
    }
  });

  /* =========================================================
     STORY: words light as you read
     ========================================================= */
  var fill = $('fill');
  fill.innerHTML = fill.textContent.trim().split(/\s+/).map(function(w){ return '<span class="w">' + w + '</span>'; }).join(' ');
  var words = fill.querySelectorAll('.w');

  /* =========================================================
     SKY SLIDER: a day at Azzeman
     ========================================================= */
  var MOMENTS = [
    { h:6,  m:30, phase:'dawn',  img:G+'gallery_6927f8ddb2b376.82487801.jpg', title:'Fine dining, from breakfast', desc:'A full breakfast spread before the early flights, then Ethiopian and international plates through the day.' },
    { h:9,  m:0,  phase:'day',   img:S+'meetings_image_1782464278.jpeg', title:'Free high-speed Wi-Fi', desc:'Fast, complimentary internet throughout the hotel, so your working day starts wherever you are.' },
    { h:12, m:30, phase:'day',   img:S+'spa_image_3_1763964969.jpeg', title:'Fitness centre', desc:'Keep your routine on the road, then unwind in the sauna and steam room.' },
    { h:16, m:0,  phase:'day',   img:G+'gallery_6a3e3f80e5ba42.08198011.jpg', title:'Airport shuttle', desc:'Early arrival or late departure, our shuttle runs between the hotel and Bole International, two kilometres away.' },
    { h:18, m:30, phase:'dusk',  img:G+'gallery_6927f9abbcfb80.07517474.jpg', title:'Bar & café at sunset', desc:'Coffee by day and cocktails by night, with Bole\'s restaurants and clubs a short walk away.' },
    { h:21, m:0,  phase:'night', img:G+'gallery_6927f9be4a4a81.96526898.jpg', title:'Dinner and drinks', desc:'Our restaurant and bar keep going into the evening, and room service is there when you\'d rather stay in.' },
    { h:23, m:30, phase:'night', img:G+'gallery_6927dfec4bb910.72859977.jpg', title:'24/7 concierge & laundry', desc:'Someone is always at the desk. Leave your laundry with us tonight and wear it again tomorrow.' }
  ];
  var sky = $('services'), hoursEl = $('hours'), orb = $('orb'), arcEl = $('arc'), arcPath = $('arc-path'), momentEl = $('moment'), hourBtns = [], curM = -1;
  MOMENTS.forEach(function(mo, i){
    var e = ethClock(mo.h, mo.m);
    var b = document.createElement('button'); b.className = 'hour'; b.setAttribute('role','tab'); b.id = 'hr-' + i;
    b.innerHTML = '<b>' + String(mo.h).padStart(2,'0') + ':' + String(mo.m).padStart(2,'0') + '</b><small>' + e.text + ' ET</small>';
    b.setAttribute('aria-label', mo.title + ', ' + mo.h + ':' + String(mo.m).padStart(2,'0'));
    b.addEventListener('click', function(){ setMoment(i); stopAuto(); });
    b.addEventListener('keydown', function(ev){
      var d = ev.key === 'ArrowRight' ? 1 : ev.key === 'ArrowLeft' ? -1 : 0;
      if (d){ ev.preventDefault(); var n = (i + d + MOMENTS.length) % MOMENTS.length; setMoment(n); hourBtns[n].focus(); stopAuto(); }
    });
    hoursEl.appendChild(b); hourBtns.push(b);
  });
  function placeOrb(i){
    var mo = MOMENTS[i], t = mo.h + mo.m / 60;
    // day arc 6:00–18:00 left→right; night reuses the arc, dimmed as a moon
    var f = (t >= 6 && t <= 18.5) ? (t - 6) / 12.5 : ((t < 6 ? t + 24 : t) - 18.5) / 11.5;
    var L = arcPath.getTotalLength(), pt = arcPath.getPointAtLength(Math.max(0, Math.min(1, f)) * L);
    var w = arcEl.clientWidth, h = arcEl.clientHeight;
    orb.style.left = (pt.x / 1000 * w) + 'px'; orb.style.top = (pt.y / 200 * h) + 'px';
  }
  function setMoment(i){
    if (i === curM) return;
    var mo = MOMENTS[i], e = ethClock(mo.h, mo.m);
    sky.dataset.phase = mo.phase;
    hourBtns.forEach(function(b, k){ b.setAttribute('aria-selected', String(k === i)); b.tabIndex = k === i ? 0 : -1; });
    momentEl.setAttribute('aria-labelledby', 'hr-' + i);
    placeOrb(i);
    var paint = function(){
      $('m-img').src = mo.img; $('m-img').alt = mo.title;
      $('m-addis').textContent = String(mo.h).padStart(2,'0') + ':' + String(mo.m).padStart(2,'0');
      $('m-eth').textContent = e.text + ' ' + e.part;
      $('m-title').textContent = mo.title; $('m-desc').textContent = mo.desc;
      momentEl.classList.remove('swap');
    };
    if (curM < 0) paint(); else { momentEl.classList.add('swap'); setTimeout(paint, 320); }
    curM = i;
  }
  setMoment(0);
  var auto = null;
  function stopAuto(){ clearInterval(auto); auto = null; }
  if (!reduced && 'IntersectionObserver' in window){
    new IntersectionObserver(function(es){
      es.forEach(function(e){
        if (e.isIntersecting && auto === null && curM === 0) auto = setInterval(function(){ if (curM >= MOMENTS.length - 1) return stopAuto(); setMoment(curM + 1); }, 3200);
      });
    }, { threshold:.5 }).observe(sky);
  }
  window.addEventListener('resize', function(){ if (curM > -1) placeOrb(curM); });

  /* =========================================================
     ROOMS: ELEVATOR
     Only the King and Twin photos exist on the current site.
     REPLACE the others with photos of each room type; add size and rates.
     ========================================================= */
  var ROOMS = [
    { key:'K',  name:'King Room', img:S+'rooms_image_1782464244.jpeg', desc:'A calm, spacious room with a king bed for one guest or a couple. The room most business travellers choose.', chips:['King bed','Writing table','Coffee & tea maker'] },
    { key:'T',  name:'Twin Room', img:G+'gallery_6927dfec4bb910.72859977.jpg', desc:'Two single beds for colleagues on the same trip, or friends sharing the adventure.', chips:['Two single beds','Wardrobe','Minibar'] },
    { key:'D',  name:'Deluxe Room', img:S+'rooms_image_1782464244.jpeg', desc:'Extra space and views across Bole, for longer stays and guests who like room to spread out.', chips:['More space','City view','Coffee table with chairs'] },
    { key:'JS', name:'Junior Suite', img:G+'gallery_6927dfec4bb910.72859977.jpg', desc:'A bedroom with its own sitting area, so you can take a call or host a colleague without working from the bed.', chips:['Separate sitting area','Private amenities','Safe deposit box'] },
    { key:'ES', name:'Executive Suite', img:S+'about_image_1782464231.jpeg', desc:'Our most generous space, with a separate living area, for senior delegates and long stays.', chips:['Separate living area','Private amenities','Minibar'] }
  ];
  if (Array.isArray(window.AZZEMAN_ROOMS) && window.AZZEMAN_ROOMS.length) {
    ROOMS = window.AZZEMAN_ROOMS.map(function(r){
      return {
        key: r.key || 'R',
        name: r.name || 'Room',
        img: r.img || window.AZZEMAN_ROOMS_IMAGE || S+'rooms_image_1782464244.jpeg',
        desc: r.desc || '',
        chips: Array.isArray(r.chips) && r.chips.length ? r.chips : ['Wi-Fi','Air conditioning'],
        bookingType: r.bookingType || r.name || ''
      };
    });
  } else {
    if (window.AZZEMAN_ROOMS_IMAGE) {
      ROOMS.forEach(function(r){ if (r.key === 'K' || r.key === 'D') r.img = window.AZZEMAN_ROOMS_IMAGE; });
    }
    if (window.AZZEMAN_ABOUT_IMAGE) {
      ROOMS.forEach(function(r){ if (r.key === 'ES') r.img = window.AZZEMAN_ABOUT_IMAGE; });
    }
  }
  var car = $('car'), liftBtns = $('lift-btns'), lb = [], curR = -1, busy = false;
  if (liftBtns) {
  ROOMS.forEach(function(r, i){
    var b = document.createElement('button'); b.className = 'lbtn'; b.type = 'button';
    b.innerHTML = '<i>' + r.key + '</i><span>' + r.name + '</span>';
    b.addEventListener('click', function(){ ride(i); });
    liftBtns.appendChild(b); lb.push(b);
  });
  }
  function paintRoom(i){
    var r = ROOMS[i];
    $('car-img').src = r.img; $('car-img').alt = r.name + ' at Azzeman Hotel';
    $('r-name').textContent = r.name; $('r-desc').textContent = r.desc;
    $('r-chips').innerHTML = r.chips.map(function(c){ return '<span>' + c + '</span>'; }).join('');
    $('led').textContent = r.name.toUpperCase();
  }
  function ride(i){
    if (busy || i === curR) return;
    lb.forEach(function(b, k){ b.setAttribute('aria-pressed', String(k === i)); });
    if (curR < 0 || reduced){ paintRoom(i); car.classList.add('open'); curR = i; return; }
    busy = true; car.classList.remove('open');
    $('led').textContent = '· · ·';
    setTimeout(function(){ paintRoom(i); car.classList.add('open'); busy = false; }, 850);
    curR = i;
  }
  ride(0);
  $('r-book').addEventListener('click', function(e){ e.preventDefault(); if (curR > -1 && $('roomtype')) $('roomtype').value = ROOMS[curR].bookingType || ROOMS[curR].name; if (typeof window.openBookingModal==='function') window.openBookingModal(); });
  // doors open the first time the lift comes into view
  car.classList.remove('open');
  if ('IntersectionObserver' in window){
    new IntersectionObserver(function(es, o){ es.forEach(function(e){ if (e.isIntersecting){ setTimeout(function(){ car.classList.add('open'); }, 350); o.disconnect(); } }); }, { threshold:.45 }).observe(car);
  } else car.classList.add('open');

  /* =========================================================
     SPA: breathe with us (4 in · 4 hold · 6 out, three rounds)
     ========================================================= */
  var orbBtn = $('orb-btn'), orbText = $('orb-text'), breathing = false;
  orbBtn.addEventListener('click', function(){
    if (breathing) return; breathing = true; orbBtn.classList.remove('idle');
    var steps = [], rounds = 3;
    for (var r = 0; r < rounds; r++) steps.push(['Breathe in', 4000, true], ['Hold', 4000, true], ['Breathe out', 6000, false]);
    var k = 0;
    (function next(){
      if (k >= steps.length){ orbBtn.classList.remove('in'); orbText.innerHTML = 'Welcome to<br/>slow time<small>Tap to go again</small>'; breathing = false; orbBtn.classList.add('idle'); return; }
      var s = steps[k++];
      orbBtn.style.transitionDuration = s[1] + 'ms';
      orbBtn.classList.toggle('in', s[2]);
      orbText.innerHTML = s[0] + '<small>' + Math.ceil(k / 3) + ' of ' + rounds + '</small>';
      setTimeout(next, s[1]);
    })();
  });

  /* =========================================================
     MEETINGS: halls on the map
     ========================================================= */
  var HALLS = [
    { name:'Entoto', where:'the mountain above Addis Ababa', x:115, y:108, lx:-44, ly:-4, img:G+'gallery_6926da72e3fd56.52104420.jpg', desc:'Named for the eucalyptus-covered range that rises over the capital.' },
    { name:'Ras Dashen', where:'the Simien Mountains', x:107, y:35, lx:10, ly:3, img:G+'gallery_6926db12d6a119.81907360.jpg', desc:'Named for Ethiopia\'s highest peak, high in the Simien Mountains.' },
    { name:'Jegol', where:'the walls of Harar', x:182, y:114, lx:9, ly:3, img:G+'gallery_6926dad6d69cb9.12523831.jpg', desc:'Named for the historic walls around the old city of Harar.' },
    { name:'Sof Omar', where:'the caves of Bale', x:157, y:162, lx:9, ly:3, img:G+'gallery_6927f9d5c52ac3.81461131.jpg', desc:'Named for the vast, river-carved cave system of Bale.' }
  ];
  if (Array.isArray(window.AZZEMAN_HALLS) && window.AZZEMAN_HALLS.length) {
    HALLS = window.AZZEMAN_HALLS.map(function(h){
      return {
        name: h.name,
        where: h.where || '',
        x: h.x, y: h.y, lx: h.lx, ly: h.ly,
        img: h.img || window.AZZEMAN_MEETINGS_IMAGE || G+'gallery_6926da72e3fd56.52104420.jpg',
        desc: h.desc || ''
      };
    });
  }
  var pins = $('map-pins'), tabs = $('hall-tabs'), hallCard = $('hall-card'), pinEls = [], tabEls = [], curH = -1;
  HALLS.forEach(function(h, i){
    var g = document.createElementNS(NS, 'g'); g.setAttribute('class','mpin'); g.setAttribute('tabindex','0'); g.setAttribute('role','button');
    g.setAttribute('aria-label', h.name + ' Hall');
    g.innerHTML = '<circle class="halo" cx="' + h.x + '" cy="' + h.y + '" r="9"/><circle class="dot" cx="' + h.x + '" cy="' + h.y + '" r="5"/><text x="' + (h.x + h.lx) + '" y="' + (h.y + h.ly) + '">' + h.name + '</text>';
    g.addEventListener('click', function(){ pickHall(i); });
    g.addEventListener('keydown', function(e){ if (e.key === 'Enter' || e.key === ' '){ e.preventDefault(); pickHall(i); } });
    pins.appendChild(g); pinEls.push(g);
    var t = document.createElement('button'); t.className = 'htab'; t.textContent = h.name;
    t.addEventListener('click', function(){ pickHall(i); });
    tabs.appendChild(t); tabEls.push(t);
  });
  function pickHall(i){
    if (i === curH) return;
    var h = HALLS[i];
    pinEls.forEach(function(p, k){ p.classList.toggle('on', k === i); });
    tabEls.forEach(function(t, k){ t.setAttribute('aria-pressed', String(k === i)); });
    var paint = function(){
      $('h-img').src = h.img; $('h-img').alt = h.name + ' Hall';
      $('h-where').textContent = h.where
        ? (/pax|floor|·/i.test(h.where) ? h.where : ('Named for ' + h.where))
        : '';
      $('h-name').textContent = h.name + ' Hall'; $('h-desc').textContent = h.desc;
      $('h-mail').href = 'mailto:info@azzemanhotel.com?subject=' + encodeURIComponent(h.name + ' Hall enquiry');
      hallCard.classList.remove('swap');
    };
    if (curH < 0) paint(); else { hallCard.classList.add('swap'); setTimeout(paint, 300); }
    curH = i;
  }
  pickHall(0);

  /* =========================================================
     VIRTUAL TOUR: a spotlight on the lobby
     ========================================================= */
  var tour = $('tour-box'), tx = 50, ty = 45, cx = 50, cy = 45, touched = false;
  tour.addEventListener('pointermove', function(e){
    var r = tour.getBoundingClientRect(); touched = true;
    tx = (e.clientX - r.left) / r.width * 100; ty = (e.clientY - r.top) / r.height * 100;
  });
  tour.addEventListener('pointerleave', function(){ touched = false; });
  var t0 = performance.now();
  (function spot(t){
    if (!touched && !reduced){ var s = (t - t0) / 1000; tx = 50 + Math.sin(s * .5) * 28; ty = 42 + Math.cos(s * .7) * 16; }
    cx += (tx - cx) * .12; cy += (ty - cy) * .12;
    tour.style.setProperty('--mx', cx + '%'); tour.style.setProperty('--my', cy + '%');
    requestAnimationFrame(spot);
  })(t0);

  /* =========================================================
     GALLERY
     ========================================================= */
  var PHOTOS = [
    { src:G+'gallery_6a3e3f80e5ba42.08198011.jpg', cap:'Lobby Area', cat:'Lobby' },
    { src:G+'gallery_6927f9d5c52ac3.81461131.jpg', cap:'Sof Omar Hall', cat:'Event facilities' },
    { src:G+'gallery_6927f9be4a4a81.96526898.jpg', cap:'Bar Interior', cat:'Bar & Cafe' },
    { src:G+'gallery_6927f9abbcfb80.07517474.jpg', cap:'Bar Entrance', cat:'Bar & Cafe' },
    { src:G+'gallery_6927f8ddb2b376.82487801.jpg', cap:'Restaurant', cat:'Restaurant' },
    { src:G+'gallery_6927f8c6441f10.40894854.jpg', cap:'Restaurant', cat:'Restaurant' },
    { src:G+'gallery_6927dfec4bb910.72859977.jpg', cap:'Twin Room', cat:'Rooms' },
    { src:G+'gallery_6926db12d6a119.81907360.jpg', cap:'Ras Dashen Hall', cat:'Event facilities' },
    { src:G+'gallery_6926dad6d69cb9.12523831.jpg', cap:'Jegol Hall', cat:'Event facilities' },
    { src:G+'gallery_6926da72e3fd56.52104420.jpg', cap:'Entoto Hall', cat:'Event facilities' },
    { src:S+'spa_image_1_1763964944.jpeg', cap:'Spa', cat:'Spa & GYM' },
    { src:S+'spa_image_4_1763965830.jpeg', cap:'Spa & fitness', cat:'Spa & GYM' },
    { src:S+'rooms_image_1782464244.jpeg', cap:'Guest room', cat:'Rooms' },
    { src:S+'about_image_1782464231.jpeg', cap:'Lobby', cat:'Lobby' }
  ];
  if (Array.isArray(window.HOME_GALLERY_ITEMS) && window.HOME_GALLERY_ITEMS.length) {
    PHOTOS = window.HOME_GALLERY_ITEMS.map(function(it){
      return {
        src: it.src,
        cap: it.title || it.cap || it.alt || 'Gallery',
        cat: it.category || it.cat || 'all'
      };
    });
  }
  var ratios = ['4/5','1/1','3/4','4/3','5/6'], mas = $('masonry');
  var shots = [];
  if (mas) {
  shots = PHOTOS.map(function(p, i){
    var b = document.createElement('button'); b.className = 'shot'; b.dataset.cat = p.cat; b.dataset.i = i;
    b.setAttribute('aria-label', 'Open photo: ' + p.cap);
    b.innerHTML = '<div class="frame" style="aspect-ratio:' + ratios[i % ratios.length] + '"><img src="' + p.src + '" alt="' + p.cap + '" loading="lazy" /><span class="cap">' + p.cap + '</span></div>';
    b.addEventListener('click', function(){ openLb(i); });
    mas.appendChild(b); return b;
  });
  }
  document.querySelectorAll('.filter').forEach(function(f, _, all){
    f.addEventListener('click', function(){
      all.forEach(function(x){ x.setAttribute('aria-pressed', String(x === f)); });
      shots.forEach(function(s){
        var show = f.dataset.f === 'all' || s.dataset.cat === f.dataset.f;
        if (show && s.hidden){ s.style.animation = 'none'; void s.offsetHeight; s.style.animation = ''; }
        s.hidden = !show;
      });
    });
  });
  var box = $('lightbox'), lbImg = $('lb-img'), lbCap = $('lb-cap'), lbCur = 0, lastFocus = null;
  function visible(){ return shots.filter(function(s){ return !s.hidden; }).map(function(s){ return +s.dataset.i; }); }
  function show(i){ if (!PHOTOS[i] || !lbImg) return; lbCur = i; lbImg.src = PHOTOS[i].src; lbImg.alt = PHOTOS[i].cap; if (lbCap) lbCap.textContent = PHOTOS[i].cap + ' · ' + PHOTOS[i].cat; }
  function openLb(i){ if (!box) return; lastFocus = document.activeElement; show(i); box.classList.add('open'); document.body.style.overflow = 'hidden'; if ($('lb-close')) $('lb-close').focus(); }
  function closeLb(){ if (!box) return; box.classList.remove('open'); document.body.style.overflow = ''; if (lastFocus) lastFocus.focus(); }
  function step(d){ var v = visible(); if (!v.length) return; show(v[(v.indexOf(lbCur) + d + v.length) % v.length]); }
  if ($('lb-close')) $('lb-close').addEventListener('click', closeLb);
  if ($('lb-prev')) $('lb-prev').addEventListener('click', function(){ step(-1); });
  if ($('lb-next')) $('lb-next').addEventListener('click', function(){ step(1); });
  if (box) box.addEventListener('click', function(e){ if (e.target === box) closeLb(); });

  /* =========================================================
     POSTCARDS
     ========================================================= */
  var cards = Array.prototype.slice.call(document.querySelectorAll('.postcard')), front = 0;
  var POSE = [ 'translate(0,0) rotate(-2deg)', 'translate(4%,8%) rotate(3deg)', 'translate(8%,14%) rotate(7deg)' ];
  function layoutCards(){
    cards.forEach(function(c, i){
      var pos = (i - front + cards.length) % cards.length;
      c.style.zIndex = cards.length - pos;
      c.style.transform = POSE[pos];
      c.tabIndex = pos === 0 ? 0 : -1;
      c.setAttribute('aria-hidden', pos === 0 ? 'false' : 'true');
    });
  }
  function nextCard(d){ front = (front + d + cards.length) % cards.length; layoutCards(); }
  cards.forEach(function(c){ c.addEventListener('click', function(){ nextCard(1); }); });
  $('pc-next').addEventListener('click', function(){ nextCard(1); });
  $('pc-prev').addEventListener('click', function(){ nextCard(-1); });
  layoutCards();

  /* =========================================================
     ARRIVALS BOARD (split-flap)
     ========================================================= */
  var PLACES = [
    ['EDNA MALL', '1.1 KM', 'walk', 'Walkable'],
    ['MATTI MULTIPLEX', '1.1 KM', 'walk', 'Walkable'],
    ['FRIENDSHIP CENTER', '1.5 KM', 'walk', 'Walkable'],
    ['BOLE AIRPORT', '1.8 KM', 'shuttle', 'Shuttle'],
    ['DEMBEL CITY CENTER', '3.2 KM', 'drive', 'Short drive'],
    ['UNECA CONF. CENTRE', '3.7 KM', 'drive', 'Short drive'],
    ['ADDIS ABABA MUSEUM', '3.8 KM', 'drive', 'Short drive'],
    ['NATIONAL PALACE', '4.3 KM', 'drive', 'Short drive']
  ];
  var board = $('board'), CH = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.';
  var flaps = [];
  PLACES.forEach(function(p){
    var row = document.createElement('div'); row.className = 'brow';
    row.innerHTML = '<span class="flap" aria-label="' + p[0] + '"></span><span class="flap" aria-label="' + p[1] + '"></span><span class="status ' + p[2] + '">' + p[3] + '</span>';
    [0,1].forEach(function(c){
      var el = row.children[c];
      p[c].split('').forEach(function(ch){
        var i = document.createElement('i'); i.setAttribute('aria-hidden','true');
        if (ch === ' '){ i.className = 'sp'; i.innerHTML = '&nbsp;'; } else { i.textContent = reduced ? ch : CH[Math.floor(Math.random() * CH.length)]; flaps.push({ el:i, to:ch }); }
        el.appendChild(i);
      });
    });
    board.appendChild(row);
  });
  function flipAll(){
    flaps.forEach(function(f, n){
      var spins = 6 + Math.floor(Math.random() * 10), k = 0;
      setTimeout(function spin(){
        if (k++ >= spins){ f.el.textContent = f.to; return; }
        f.el.textContent = CH[Math.floor(Math.random() * CH.length)]; setTimeout(spin, 45);
      }, n * 6);
    });
  }
  if (!reduced && 'IntersectionObserver' in window){
    new IntersectionObserver(function(es, o){ es.forEach(function(e){ if (e.isIntersecting){ flipAll(); o.disconnect(); } }); }, { threshold:.3 }).observe(board);
  }

  /* =========================================================
     ONE SCROLL LOOP
     ========================================================= */
  var bm = $('bm'), bmWord = $('bm-word'), bmGeez = document.querySelector('.bm-geez'), bmCap = document.querySelector('.bm-cap');
  var pill = $('pill-book'), bookSec = $('book'), ticking = false;
  function clamp(v){ return Math.max(0, Math.min(1, v)); }
  function frame(){
    try {
      var vh = window.innerHeight;
      if (nav) nav.classList.toggle('scrolled', window.scrollY > 30);

      if (typeof fill !== 'undefined' && fill && words && words.length) {
        var fr = fill.getBoundingClientRect();
        var lit = Math.round(clamp((vh * .85 - fr.top) / (fr.height + vh * .3)) * words.length);
        for (var i = 0; i < words.length; i++) words[i].classList.toggle('lit', reduced || i < lit);
      }

      if (!reduced && bm && bmWord){
        var r = bm.getBoundingClientRect(), p = clamp(-r.top / (Math.max(1, r.height - vh)));
        var bp = Math.min(1, (p < .55 ? p / .55 : 1 - (p - .55) / .45) * 1.25);
        var bpStr = bp.toFixed(3), zStr = (3 - bp * 1.6).toFixed(3);
        bmWord.style.setProperty('--bp', bpStr);
        bmWord.style.setProperty('--z', zStr);
        var letters = bmWord.children;
        for (var li = 0; li < letters.length; li++) {
          letters[li].style.setProperty('--bp', bpStr);
        }
        if (bmGeez) bmGeez.style.setProperty('--bp', bpStr);
        if (bmCap) bmCap.style.setProperty('--bp', bpStr);
      } else if (reduced && bmWord) {
        bmWord.style.setProperty('--bp', '1');
        if (bmGeez) bmGeez.style.setProperty('--bp', '1');
        if (bmCap) bmCap.style.setProperty('--bp', '1');
      }

      if (bookSec) {
        var br = bookSec.getBoundingClientRect(), footEl = document.querySelector('.footer'), foot = footEl ? footEl.getBoundingClientRect() : { top: vh + 1 };
        var asElOpen = document.getElementById('assistant');
        var showPill = br.bottom < 0 && foot.top > vh && !(asElOpen && asElOpen.classList.contains('open'));
        if (pill) {
          pill.classList.toggle('show', showPill);
          pill.setAttribute('aria-hidden', String(!showPill));
          var pillA = pill.querySelector('a');
          if (pillA) pillA.tabIndex = showPill ? 0 : -1;
        }
      }
    } catch (err) { /* keep page usable */ }
    ticking = false;
  }
  window.addEventListener('scroll', function(){ if (!ticking){ ticking = true; requestAnimationFrame(frame); } }, { passive:true });
  window.addEventListener('resize', frame);
  frame();

  /* ---------- reveals ---------- */
  var rv = document.querySelectorAll('.rv, .pop');
  if ('IntersectionObserver' in window && !reduced){
    var io = new IntersectionObserver(function(es){ es.forEach(function(e){ if (e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); } }); }, { rootMargin:'0px 0px -8% 0px', threshold:.08 });
    rv.forEach(function(el){ io.observe(el); });
  } else rv.forEach(function(el){ el.classList.add('in'); });

  /* Concierge UI removed — site uses chatbot.php instead */
  if (false && $('fab') && $('assistant')) {
  var fab = $('fab'), asEl = $('assistant'), body = $('as-body'), chipsEl = $('as-chips'), form = $('as-form'), input = $('as-input');
  var waLink = '<a href="https://api.whatsapp.com/send?phone=' + WA + '" target="_blank" rel="noopener">WhatsApp</a>';
  var KB = [
    { k:['room','suite','bed','king','twin','deluxe','executive','junior'], chip:'Rooms', a:'We have five room types: King Room, Twin Room, Deluxe Room, Junior Suite and Executive Suite. Every room has a flat-screen TV, Wi-Fi, a minibar, a writing table, air conditioning, a safe and a coffee and tea maker. <a href="#rooms">Ride the elevator to see them</a>' },
    { k:['airport','shuttle','pickup','pick up','transfer','flight'], chip:'Airport shuttle', a:'We are 2 km from Bole International Airport, about ten minutes by car. Our airport shuttle can meet you. Add your flight number when you book.' },
    { k:['spa','massage','facial','sauna','steam','gym','fitness','treatment'], chip:'Spa & gym', a:'Our spa offers therapeutic massages and facials with natural ingredients, plus a sauna, steam room and fitness centre. Book a treatment on ' + waLink + '.' },
    { k:['meeting','event','conference','hall','wedding','venue','catering'], chip:'Meeting halls', a:'We have four halls: Entoto, Ras Dashen, Jegol and Sof Omar, with A/V equipment, flexible layouts and customised catering. Email <a href="mailto:info@azzemanhotel.com?subject=Event%20enquiry">info@azzemanhotel.com</a> for capacity and quotes.' },
    { k:['ethiopian time','time','clock','calendar'], chip:'Ethiopian time?', a:'Ethiopia counts the hours from sunrise, so 7:00 in the morning is 1 o\'clock, and the calendar has 13 months. Today is ' + MONTHS[eDate.month - 1] + ' ' + eDate.day + ', ' + eDate.year + '. Our front desk works on international time, so you won\'t miss a flight.' },
    { k:['wifi','wi-fi','internet'], chip:'Wi-Fi', a:'High-speed Wi-Fi is complimentary throughout the hotel.' },
    { k:['food','restaurant','breakfast','dinner','bar','cafe','café','eat','drink'], chip:'Dining', a:'Our restaurant serves Ethiopian and international dishes, and the bar and café run from morning coffee to evening cocktails. Room service is available too.' },
    { k:['where','address','location','map','direction'], chip:'Location', a:'Bole Sub-City, Woreda 03, Addis Ababa, next to 2000 Habesha cultural restaurant and about 1 km from Edna Mall. <a href="#contact">See the map</a>' },
    { k:['book','reserve','reservation','price','rate','cost','availability'], chip:'Book', a:'Fill in your boarding pass above and tap “Check availability”. Our reservations team replies with rates. You can also call <a href="tel:+251116393132">+251 116 393 132</a>.' },
    { k:['laundry','concierge','24','desk'], chip:'Concierge', a:'Our front desk and concierge are available 24/7, and laundry service is available every day.' },
    { k:['contact','phone','call','email','whatsapp'], chip:'Contact', a:'Call <a href="tel:+251116393132">+251 116 393 132</a>, email <a href="mailto:info@azzemanhotel.com">info@azzemanhotel.com</a>, or message us on ' + waLink + '.' }
  ];
  function say(html, who){ var m = document.createElement('div'); m.className = 'msg ' + (who || 'bot'); m.innerHTML = html; body.appendChild(m); body.scrollTop = body.scrollHeight; }
  function answer(q){
    var t = q.toLowerCase();
    var hit = KB.filter(function(e){ return e.k.some(function(k){ return t.indexOf(k) > -1; }); })[0];
    setTimeout(function(){ say(hit ? hit.a : 'I don\'t have that answer here, but our team does. Message us on ' + waLink + ' or call <a href="tel:+251116393132">+251 116 393 132</a>.'); }, 380);
  }
  KB.slice(0, 7).forEach(function(e){
    var c = document.createElement('button'); c.className = 'qchip'; c.type = 'button'; c.textContent = e.chip;
    c.addEventListener('click', function(){ say(e.chip, 'me'); answer(e.k[0]); });
    chipsEl.appendChild(c);
  });
  var greeted = false;
  function openAs(){ asEl.classList.add('open'); fab.setAttribute('aria-expanded','true'); if (!greeted){ say('Welcome to Azzeman Hotel! How can I assist you today? I can answer questions about our rooms, spa and meeting facilities.'); greeted = true; } setTimeout(function(){ input.focus(); }, 300); frame(); }
  function closeAs(){ asEl.classList.remove('open'); fab.setAttribute('aria-expanded','false'); frame(); }
  fab.addEventListener('click', function(){ asEl.classList.contains('open') ? closeAs() : openAs(); });
  $('as-close').addEventListener('click', function(){ closeAs(); fab.focus(); });
  form.addEventListener('submit', function(e){
    e.preventDefault(); var q = input.value.trim(); if (!q) return;
    var safe = document.createElement('div'); safe.textContent = q; say(safe.innerHTML, 'me'); input.value = ''; answer(q);
  });
  }

  document.addEventListener('keydown', function(e){
    if (box && box.classList.contains('open')){
      if (e.key === 'Escape') closeLb(); if (e.key === 'ArrowRight') step(1); if (e.key === 'ArrowLeft') step(-1);
    }
  });
})();
