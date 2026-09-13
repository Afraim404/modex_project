<?php
function layoutBottom() {
    echo '
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <span class="footer-logo">MOD<span>EX</span></span>
      <p>Premium 3D printed products crafted with precision. Delivered across all 64 districts of Bangladesh.</p>
    </div>
    <div class="footer-links">
      <h4>Quick Links</h4>
      <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/products.php">Products</a></li>
        <li><a href="/cart.php">Cart</a></li>
        <li><a href="/order.php">Place Order</a></li>
        <li><a href="/track.php">Track Order</a></li>
        <li><a href="/about.php">About</a></li>
        <li><a href="/contact.php">Contact</a></li>
      </ul>
    </div>
    <div class="footer-contact">
      <h4>Contact</h4>
      <p>&#128222; 01641007937/01830026382</p>
      <p>&#128232; <a href="https://wa.me/881641007937" style="color:inherit">WhatsApp Us</a></p>
      <p>&#128231; modexbd06@gmail.com</p>
      <p>&#128205; 10/K/2 Modhubag, Dhaka-1208</p>
      <div class="delivery-info">
        <span>&#128666; Inside Dhaka: &#2547;80</span>
        <span>&#128666; Outside Dhaka: &#2547;120</span>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; ' . date('Y') . ' MODEX. All rights reserved.</p>
  </div>
</footer>
<a href="https://wa.me/8801641007937?text=Hi%20MODEX%2C%20I%20want%20to%20order" class="whatsapp-float" target="_blank" aria-label="WhatsApp">&#128232;</a>
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <button class="lightbox-close" onclick="closeLightbox()">&#10005;</button>
  <img id="lightbox-img" src="" alt="">
</div>
<script src="/assets/js/main.js"></script>
<script>
function openLightbox(src){
  document.getElementById("lightbox-img").src=src;
  document.getElementById("lightbox").classList.add("open");
  document.body.style.overflow="hidden";
}
function closeLightbox(){
  document.getElementById("lightbox").classList.remove("open");
  document.body.style.overflow="";
}
document.addEventListener("keydown",function(e){if(e.key==="Escape")closeLightbox();});
(function(){
  var s=document.createElement("style");
  s.textContent=".nav-toggle span{transition:transform .25s,opacity .25s}.nav-toggle.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}.nav-toggle.open span:nth-child(2){opacity:0}.nav-toggle.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}";
  document.head.appendChild(s);
  var link=document.createElement("link");
  link.rel="icon";link.type="image/svg+xml";
  link.href="data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2064%2064%22%3E%3Crect%20width%3D%2264%22%20height%3D%2264%22%20rx%3D%2212%22%20fill%3D%22%230a0a0a%22%2F%3E%3Cpath%20d%3D%22M32%2010L54%2023V41L32%2054L10%2041V23Z%22%20fill%3D%22none%22%20stroke%3D%22%2300e5ff%22%20stroke-width%3D%223%22%2F%3E%3Ctext%20x%3D%2232%22%20y%3D%2238%22%20text-anchor%3D%22middle%22%20font-family%3D%22sans-serif%22%20font-weight%3D%22700%22%20font-size%3D%2214%22%20fill%3D%22%2300e5ff%22%3EMX%3C%2Ftext%3E%3C%2Fsvg%3E";
  document.head.appendChild(link);
})();
</script>
</body></html>';
}
