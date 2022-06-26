declare global {
  interface Window {
    wto_data: WtoData;
  }
}

interface WtoData {
  post_id: string;
  nonce_sync: string;
  action_sync: string;
  nonce_update: string;
  action_update: string;
  ajax_url: string;
}

export const post = (): void => {
  // const removeElements = (text, selector) => {
  //   const wrapped = jQuery(`<div>${text}</div>`);
  //   wrapped.find(selector).remove();
  //   return wrapped.html();
  // };
  /*= =================================================
  Sync Tags to "#tagsdiv-post_tag" box
  ================================================== */
  let flag = '';
  setTimeout(() => {
    jQuery("[id^='tagsdiv-']")
      .find('.tagchecklist')
      .on('DOMSubtreeModified propertychange', e => {
        setTimeout(() => {
          const cont = jQuery(e.currentTarget).html();
          if (cont === flag && cont !== '') {
            return; // prevent multiple simultaneous triggers
          }
          flag = cont;

          const postboxid =
            jQuery(e.currentTarget).closest('.postbox').attr('id') ?? '';
          const t = postboxid.replace('tagsdiv-', '');
          const s = jQuery(e.currentTarget)
            .siblings()
            .find('textarea.the-tags')
            .val() as string;
          jQuery
            .ajax({
              url: window.wto_data.ajax_url,
              dataType: 'json',
              data: {
                id: window.wto_data.post_id,
                nonce: window.wto_data.nonce_sync,
                action: window.wto_data.action_sync,
                taxonomy: t,
                tags: s,
              },
              type: 'post',
              // eslint-disable-next-line prefer-arrow/prefer-arrow-functions
              beforeSend() {
                jQuery(`#tagsdiv-${t} h2, #wpto_meta_box-${t} h2`).addClass(
                  'ready',
                );
              },
            })
            .done((data: HTMLElement) => {
              jQuery(`#wpto_meta_box-${t} .inside .inner ul`).html(data);
            })
            .fail(() => {
              alert('Load Error. Please Reload...');
            })
            .always(() => {
              setTimeout(() => {
                jQuery(`#tagsdiv-${t} h2, #wpto_meta_box-${t} h2`).removeClass(
                  'ready',
                );
              }, 300);
            });
        }, 20);
      });
  }, 400);
  /*= =================================================
  jQuery UI Sortable
  ================================================== */
  (jQuery('.wpto_meta_box_panel .inside .inner ul') as any).sortable({
    update() {
      const postboxid = jQuery(this).closest('.postbox').attr('id') ?? '';
      const t = postboxid.replace('wpto_meta_box-', '');
      const ary: string[] = [];
      jQuery(this)
        .find('input[type="hidden"]')
        .each((_i, el) => {
          const tag = jQuery(el).val() as string;
          ary.push(tag);
        });
      const s = ary.join();
      jQuery
        .ajax({
          url: window.wto_data.ajax_url,
          type: 'post',
          dataType: 'json',
          data: {
            id: window.wto_data.post_id,
            nonce: window.wto_data.nonce_update,
            action: window.wto_data.action_update,
            taxonomy: t,
            tags: s,
          },
          // eslint-disable-next-line prefer-arrow/prefer-arrow-functions
          beforeSend() {
            jQuery(`#wpto_meta_box-${t} h2`).addClass('ready');
          },
        })
        .done(() => {})
        .fail(() => {
          alert('Load Error. Please Reload...');
        })
        .always(() => {
          setTimeout(() => {
            jQuery(`#wpto_meta_box-${t} h2`).removeClass('ready');
          }, 300);
        });
    },
  });
};
