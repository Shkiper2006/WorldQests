<?php
/** @var array<string,mixed> $quest */
?>
<div class="world-quest-viewer" data-world-quest-viewer>
    <style>
        .world-quest-layout{display:grid;grid-template-columns:1fr 320px;gap:16px}
        .world-quest-media img{max-width:100%;height:auto;display:block;border-radius:8px}
        .world-quest-choices{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px; margin-top:14px}
        .world-quest-choices button,.world-quest-choices a{padding:10px;border-radius:8px;border:1px solid #ccc;background:#fff;cursor:pointer;text-align:center;text-decoration:none}
        @media (max-width: 900px){.world-quest-layout{grid-template-columns:1fr}.world-quest-choices{grid-template-columns:1fr}}
    </style>
    <h2><?php echo esc_html((string) $quest['title']); ?></h2>
    <div class="world-quest-layout">
        <section>
            <article data-node-content></article>
        </section>
        <aside class="world-quest-media" data-node-media></aside>
    </div>
    <div class="world-quest-choices" data-node-choices></div>
</div>
