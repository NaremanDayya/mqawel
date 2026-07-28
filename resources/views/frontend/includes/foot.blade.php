<!-- Legacy scripts (still required by non-landing frontend pages: sign-up, policies, which use Bootstrap markup) -->
<script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('assets/js/wow.min.js')}}"></script>
<script src="{{asset('assets/js/main.js')}}"></script>
<script>
    // ==== for menu scroll (legacy pages only)
    const pageLink = document.querySelectorAll(".ud-menu-scroll");

    pageLink.forEach((elem) => {
        elem.addEventListener("click", (e) => {
            e.preventDefault();
            const target = document.querySelector(elem.getAttribute("href"));
            if (target) {
                target.scrollIntoView({
                    behavior: "smooth",
                    offsetTop: 1 - 60,
                });
            }
        });
    });

    // section menu active
    function onScroll(event) {
        const sections = document.querySelectorAll(".ud-menu-scroll");
        const scrollPos =
            window.pageYOffset ||
            document.documentElement.scrollTop ||
            document.body.scrollTop;

        for (let i = 0; i < sections.length; i++) {
            const currLink = sections[i];
            const val = currLink.getAttribute("href");
            const refElement = document.querySelector(val);
            if (!refElement) {
                continue;
            }
            const scrollTopMinus = scrollPos + 73;
            if (
                refElement.offsetTop <= scrollTopMinus &&
                refElement.offsetTop + refElement.offsetHeight > scrollTopMinus
            ) {
                document
                    .querySelector(".ud-menu-scroll")
                    .classList.remove("active");
                currLink.classList.add("active");
            } else {
                currLink.classList.remove("active");
            }
        }
    }

    window.document.addEventListener("scroll", onScroll);
</script>

<!-- ===== New landing page scripts (from client design Mqwel.html), scoped so they no-op on other pages ===== -->

<!-- Hero canvas wave animation -->
<script>
(function() {
  var canvas = document.getElementById('hero-canvas');
  if (!canvas) { return; }
  var ctx    = canvas.getContext('2d');
  var mouse  = {x:0,y:0}, target = {x:0,y:0};
  var time   = 0, animId;

  var WAVES = [
    {offset:0,              amp:70, freq:0.003,  color:'#1E40AF', op:0.55},
    {offset:Math.PI/2,      amp:90, freq:0.0026, color:'#2563EB', op:0.40},
    {offset:Math.PI,        amp:60, freq:0.0034, color:'#3B82F6', op:0.35},
    {offset:Math.PI*1.5,    amp:80, freq:0.0022, color:'#93C5FD', op:0.25},
  ];

  function resize() {
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;
    mouse.x = target.x = canvas.width/2;
    mouse.y = target.y = canvas.height/2;
  }

  function drawWave(w) {
    ctx.save(); ctx.beginPath();
    for (var x=0; x<=canvas.width; x+=4) {
      var dx=x-mouse.x, dy=canvas.height/2-mouse.y;
      var dist=Math.sqrt(dx*dx+dy*dy), infl=Math.max(0,1-dist/320);
      var me=infl*70*Math.sin(time*0.001+x*0.01+w.offset);
      var y=canvas.height/2+Math.sin(x*w.freq+time*0.002+w.offset)*w.amp+Math.sin(x*w.freq*0.4+time*0.003)*(w.amp*0.45)+me;
      x===0?ctx.moveTo(x,y):ctx.lineTo(x,y);
    }
    ctx.lineWidth=2.5; ctx.strokeStyle=w.color; ctx.globalAlpha=w.op;
    ctx.shadowBlur=40; ctx.shadowColor=w.color; ctx.stroke(); ctx.restore();
  }

  function animate() {
    time++;
    mouse.x += (target.x - mouse.x)*0.1;
    mouse.y += (target.y - mouse.y)*0.1;
    var g=ctx.createLinearGradient(0,0,0,canvas.height);
    g.addColorStop(0,'#060f22'); g.addColorStop(1,'#0d1f42');
    ctx.fillStyle=g; ctx.fillRect(0,0,canvas.width,canvas.height);
    ctx.globalAlpha=1; ctx.shadowBlur=0;
    WAVES.forEach(drawWave);
    animId=requestAnimationFrame(animate);
  }

  resize();
  animate();
  window.addEventListener('resize', resize);
  window.addEventListener('mousemove', function(e){ target.x=e.clientX; target.y=e.clientY; });
  window.addEventListener('mouseleave', function(){ target.x=canvas.width/2; target.y=canvas.height/2; });

  // Fade in elements
  var els = document.querySelectorAll('.hero-badge-new,.hero-h1,.hero-para,.hero-btns-new,.hero-stats-new,.hero-img-side');
  els.forEach(function(el, i) {
    el.style.opacity='0';
    el.style.transform='translateY(20px)';
    setTimeout(function() {
      el.style.transition='opacity 0.6s ease, transform 0.6s ease';
      el.style.opacity='1';
      el.style.transform='translateY(0)';
    }, 100 + i*120);
  });
})();
</script>

<!-- Pricing monthly/yearly toggle (kept for visual fidelity with the design; SubscriptionPackage
     records don't model a monthly/yearly price variant, so the toggle only switches the active
     pill state — it does not currently swap the displayed price on the dynamically rendered cards) -->
<script>
  function applyCycle(cycle) {
    document.querySelectorAll('.price-amount, .price-period, .price-term')
      .forEach(el => { if (el.dataset[cycle] !== undefined) { el.textContent = el.dataset[cycle]; } });
  }
  function setMonthly(btn) {
    btn.classList.add('active');
    if (btn.nextElementSibling) { btn.nextElementSibling.classList.remove('active'); }
    applyCycle('monthly');
  }
  function setYearly(btn) {
    btn.classList.add('active');
    if (btn.previousElementSibling) { btn.previousElementSibling.classList.remove('active'); }
    applyCycle('yearly');
  }
</script>

<!-- FAQ accordion toggle -->
<script>
  function toggleFaq(item) {
    const isActive = item.classList.contains('active');
    document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('active'));
    if (!isActive) item.classList.add('active');
  }
</script>

<!-- Reviews carousel scroll -->
<script>
  function scrollReviews(dir) {
    var track = document.getElementById('reviewsTrack');
    if (track) { track.scrollBy({ left: dir * 360, behavior: 'smooth' }); }
  }
</script>

<!-- Mobile hamburger menu (new nav) -->
<script>
  const ham   = document.querySelector('.hamburger');
  const links = document.querySelector('.nav-links');
  if (ham && links) {
    ham.addEventListener('click', () => {
      const open = ham.getAttribute('aria-expanded') === 'true';
      ham.setAttribute('aria-expanded', !open);
      if (!open) {
        links.style.cssText = 'display:flex;flex-direction:column;position:absolute;top:80px;left:0;right:0;background:white;padding:16px 20px;border-bottom:1px solid #EEF0F6;gap:4px;';
      } else {
        links.style.cssText = '';
      }
    });
  }
</script>
