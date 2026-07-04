/* AI Setup Assistant — starter hero copy generator.
 * Same free/no-API-key Pollinations service as the Hero Image Finder
 * (resources/js/hero-image-finder.js), but the text endpoint instead of the
 * image one: https://text.pollinations.ai/{prompt} returns plain text, no
 * key, no server proxy needed. We ask it to answer in a fixed
 * "HEADLINE: ... / SUBHEAD: ..." shape so it's easy to split back into the
 * two fields the UI shows; if the model ever ignores that shape, we fall
 * back to just showing the raw text as the headline so nothing silently
 * breaks.
 */
(function () {
  var input   = document.getElementById('prt-ai-copy-input');
  var go      = document.getElementById('prt-ai-copy-go');
  var note    = document.getElementById('prt-ai-copy-note');
  var result  = document.getElementById('prt-ai-copy-result');
  var headEl  = document.getElementById('prt-ai-copy-headline');
  var subEl   = document.getElementById('prt-ai-copy-subhead');
  var regen   = document.getElementById('prt-ai-copy-regen');
  if (!input || !go) return; // Not on the AI Setup Assistant screen.

  var lastPrompt = '';

  function setNote(text) {
    note.textContent = text || '';
  }

  function buildPrompt(description) {
    return 'Write website hero copy for this business: "' + description + '". ' +
      'Respond in exactly this format with no extra commentary:\n' +
      'HEADLINE: <a punchy headline, under 10 words>\n' +
      'SUBHEAD: <one supporting sentence, under 25 words>';
  }

  function parse(text) {
    var headline = '';
    var subhead  = '';
    var hMatch = text.match(/HEADLINE:\s*(.+)/i);
    var sMatch = text.match(/SUBHEAD:\s*(.+)/i);
    if (hMatch) headline = hMatch[1].trim();
    if (sMatch) subhead = sMatch[1].trim();
    if (!headline) {
      // Model didn't follow the format — show whatever it said rather than
      // failing silently, so the owner still gets something to work with.
      headline = text.trim().split('\n')[0] || '';
    }
    return { headline: headline, subhead: subhead };
  }

  function generate(description) {
    lastPrompt = description;
    setNote('Generating…');
    result.style.display = 'none';
    var url = 'https://text.pollinations.ai/' + encodeURIComponent(buildPrompt(description));
    fetch(url)
      .then(function (r) {
        if (!r.ok) throw new Error('bad response');
        return r.text();
      })
      .then(function (text) {
        var parsed = parse(text);
        headEl.textContent = parsed.headline;
        subEl.textContent = parsed.subhead;
        result.style.display = parsed.headline ? 'block' : 'none';
        setNote(parsed.headline ? 'Generated. Copy what you like into your Hero pattern, or Regenerate for another take.' : 'Got an empty response — try again or rephrase.');
      })
      .catch(function () {
        setNote('Generation failed — check your connection and try again.');
      });
  }

  go.addEventListener('click', function () {
    var description = (input.value || '').trim();
    if (!description) {
      setNote('Describe your business first.');
      return;
    }
    generate(description);
  });

  if (regen) {
    regen.addEventListener('click', function () {
      if (lastPrompt) generate(lastPrompt);
    });
  }

  result.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-copy]');
    if (!btn) return;
    var field = btn.getAttribute('data-copy') === 'headline' ? headEl : subEl;
    var text = field.textContent || '';
    if (!text) return;
    navigator.clipboard.writeText(text).then(function () {
      var original = btn.textContent;
      btn.textContent = 'Copied!';
      setTimeout(function () { btn.textContent = original; }, 1200);
    });
  });
})();
