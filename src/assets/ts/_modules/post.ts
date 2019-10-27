declare let jQuery: any;
declare let wto_data: any; // eslint-disable-line @typescript-eslint/camelcase

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
      .on('DOMSubtreeModified propertychange', function(): void {
        setTimeout(
          jQuery.proxy(function(): void {
            const cont: string = jQuery(this).html();
            if (cont === flag && cont !== '') {
              return; // prevent multiple simultaneous triggers
            }
            flag = cont;

            const postboxid: string = jQuery(this)
              .closest('.postbox')
              .attr('id');
            const t: string = postboxid.replace('tagsdiv-', '');
            const s: string = jQuery(this)
              .siblings()
              .find('textarea.the-tags')
              .val();
            jQuery
              .ajax({
                url: wto_data.ajax_url,
                dataType: 'json',
                data: {
                  id: wto_data.post_id,
                  nonce: wto_data.nonce_sync,
                  action: wto_data.action_sync,
                  taxonomy: t,
                  tags: s,
                },
                type: 'post',
                beforeSend(): void {
                  jQuery(`#tagsdiv-${t} h2, #wpto_meta_box-${t} h2`).addClass('ready');
                },
              })
              .done((data: HTMLElement): void => {
                jQuery(`#wpto_meta_box-${t} .inside .inner ul`).html(data);
              })
              .fail((): void => {
                alert('Load Error. Please Reload...');
              })
              .always((): void => {
                setTimeout(() => {
                  jQuery(`#tagsdiv-${t} h2, #wpto_meta_box-${t} h2`).removeClass('ready');
                }, 300);
              });
          }, this),
          20,
        );
      });
  }, 400);
  /*= =================================================
  jQuery UI Sortable
  ================================================== */
  jQuery('.wpto_meta_box_panel .inside .inner ul').sortable({
    update() {
      const postboxid: string = jQuery(this)
        .closest('.postbox')
        .attr('id');
      const t: string = postboxid.replace('wpto_meta_box-', '');
      const ary: string[] = [];
      jQuery(this)
        .find('input[type="hidden"]')
        .each(function(): void {
          const tag: string = jQuery(this).val();
          ary.push(tag);
        });
      const s: string = ary.join();
      jQuery
        .ajax({
          url: wto_data.ajax_url,
          type: 'post',
          dataType: 'json',
          data: {
            id: wto_data.post_id,
            nonce: wto_data.nonce_update,
            action: wto_data.action_update,
            taxonomy: t,
            tags: s,
          },
          beforeSend(): void {
            jQuery(`#wpto_meta_box-${t} h2`).addClass('ready');
          },
        })
        .done((): void => {})
        .fail((): void => {
          alert('Load Error. Please Reload...');
        })
        .always((): void => {
          setTimeout(() => {
            jQuery(`#wpto_meta_box-${t} h2`).removeClass('ready');
          }, 300);
        });
    },
  });
};
