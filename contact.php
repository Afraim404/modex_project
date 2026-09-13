<?php
require_once 'includes/config.php';
require_once 'includes/layout_top.php';
require_once 'includes/layout_bottom.php';
$sent = isset($_GET['sent']) && $_GET['sent'] === '1';
layoutTop('Contact');
?>
<div class="page-hero" style="padding-bottom:2rem">
  <span class="section-eyebrow">Get In Touch</span>
  <h1>Contact <span style="color:var(--accent)">MODEX</span></h1>
  <p>Have a question or custom request? We'd love to hear from you.</p>
</div>
<section class="section" style="padding-top:1.5rem">
  <div class="section-inner">
    <div class="contact-grid">
      <div>
        <h2 style="font-size:1.3rem;margin-bottom:1.25rem">We're here to help</h2>
        <div class="contact-item"><div class="contact-icon">&#128222;</div><div class="contact-item-text"><h4>Phone / WhatsApp</h4><p>+8801641007937/
01830026382</p></div></div>
        <div class="contact-item"><div class="contact-icon">&#128231;</div><div class="contact-item-text"><h4>Email</h4><p>modexbd06@gmail.com</p></div></div>
        <div class="contact-item"><div class="contact-icon">&#128205;</div><div class="contact-item-text"><h4>Location</h4><p>10/K/2 Modhubag, Dhaka-1208</p></div></div>
        <div class="contact-item"><div class="contact-icon">&#128337;</div><div class="contact-item-text"><h4>Response Time</h4><p>Within 24 hours</p></div></div>
        <div style="margin-top:1.25rem;padding:1rem;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm)">
          <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:.65rem">Delivery Charges</div>
          <div style="font-size:.875rem;display:flex;flex-direction:column;gap:.35rem">
            <span>&#128666; Inside Dhaka: <strong style="color:var(--accent)">&#2547;80</strong></span>
            <span>&#128666; Outside Dhaka: <strong style="color:var(--accent)">&#2547;120</strong></span>
            <span style="color:var(--text-muted)">All 64 districts covered</span>
          </div>
        </div>
      </div>
      <div>
        <?php if ($sent): ?><div class="alert alert-success" style="margin-bottom:1rem">&#10003; Message sent! We'll reply soon.</div><?php endif; ?>
        <div class="form-card">
          <h3>&#128172; Send a Message</h3>
          <form method="POST" action="/contact_send.php">
            <div class="form-grid">
              <div class="form-group"><label>Name *</label><input type="text" name="name" placeholder="Your name" required></div>
              <div class="form-group"><label>Phone *</label><input type="tel" name="phone" placeholder="01XXXXXXXXX" required></div>
              <div class="form-group" style="grid-column:1/-1"><label>Email</label><input type="email" name="email" placeholder="optional"></div>
              <div class="form-group" style="grid-column:1/-1"><label>Subject *</label><input type="text" name="subject" placeholder="What is this about?" required></div>
              <div class="form-group" style="grid-column:1/-1"><label>Message *</label><textarea name="message" placeholder="Write your message..." style="min-height:110px" required></textarea></div>
            </div>
            <button type="submit" class="btn-primary btn-full" style="margin-top:.35rem">Send Message</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php layoutBottom(); ?>
