<?php

/**
 * Reading UX on single posts: estimated reading time.
 *
 * The reading-progress bar, table of contents, and code copy buttons are
 * handled by resources/js/reading-progress.js driving the markup in
 * partials/content-single.blade.php (the authority). Do NOT duplicate
 * that logic here — two scripts produce doubled bars/TOCs/buttons.
 */

namespace App;

add_action('wp_footer', function () {
    if (! is_singular('post')) {
        return;
    }
    ?>
    <script>
    (function(){
      var content = document.querySelector('.post-prose');
      if(!content) return;

      var words = (content.innerText || '').trim().split(/\s+/).length;
      var mins = Math.max(1, Math.round(words / 200));
      var meta = document.querySelector('.post-single-header .post-meta');
      if(meta && !meta.querySelector('.prt-readtime')){
        var rt = document.createElement('span');
        rt.className = 'prt-readtime';
        rt.textContent = ' · ' + mins + ' min read';
        meta.appendChild(rt);
      }
    })();
    </script>
    <?php
}, 40);
