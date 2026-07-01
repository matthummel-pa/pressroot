(function (wp) {
  if (!wp || !wp.blocks) return;
  var el = wp.element.createElement, Fragment = wp.element.Fragment, __ = wp.i18n.__;
  var be = wp.blockEditor || wp.editor, IC = be.InspectorControls, ubp = be.useBlockProps;
  var c = wp.components, SSR = wp.serverSideRender || wp.components.ServerSideRender;

  wp.blocks.registerBlockType('prt/cta-band', {
    apiVersion: 2,
    title: __('CTA Band', 'pressroot'),
    description: __('A call-to-action section with heading, body, and button. Dark, green, or light variants.', 'pressroot'),
    icon: 'megaphone',
    category: 'pressroot',
    keywords: ['cta', 'call to action', 'band', 'section', 'button'],
    attributes: {
      heading: { type: 'string', default: 'Open to select side projects' },
      body:    { type: 'string', default: "I'm available for freelance work. Let's talk." },
      btnText: { type: 'string', default: 'Get in touch' },
      btnUrl:  { type: 'string', default: '/contact/' },
      variant: { type: 'string', default: 'dark' }
    },
    edit: function (props) {
      var a = props.attributes;
      var set = function (k) { return function (v) { var o = {}; o[k] = v; props.setAttributes(o); }; };

      var controls = el(IC, {},
        el(c.PanelBody, { title: __('Content', 'pressroot'), initialOpen: true },
          el(c.TextControl, { label: __('Heading', 'pressroot'), value: a.heading, onChange: set('heading') }),
          el(c.TextareaControl, { label: __('Body text', 'pressroot'), value: a.body, rows: 3, onChange: set('body') }),
          el(c.TextControl, { label: __('Button text', 'pressroot'), value: a.btnText, onChange: set('btnText') }),
          el(c.TextControl, { label: __('Button URL', 'pressroot'), value: a.btnUrl, type: 'url', onChange: set('btnUrl') })
        ),
        el(c.PanelBody, { title: __('Style', 'pressroot'), initialOpen: false },
          el(c.SelectControl, {
            label: __('Variant', 'pressroot'),
            value: a.variant,
            options: [
              { label: __('Dark (ink background)', 'pressroot'), value: 'dark' },
              { label: __('Green (brand colour)', 'pressroot'), value: 'green' },
              { label: __('Light (cream background)', 'pressroot'), value: 'light' }
            ],
            onChange: set('variant')
          })
        )
      );
      return el(Fragment, {}, controls, el('div', ubp ? ubp() : {}, el(SSR, { block: 'prt/cta-band', attributes: a })));
    },
    save: function () { return null; }
  });
})(window.wp);
