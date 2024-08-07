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

const {
  ajax_url: url,
  post_id: id,
  nonce_sync: nonceSync,
  action_sync: actionSync,
  nonce_update: nonceUpdate,
  action_update: actionUpdate,
} = window.wto_data;

export const post = () => {
  /*= =================================================
  Sync Tags to "#tagsdiv-post_tag" box
  ================================================== */
  let flag = '';
  setTimeout(() => {
    const tagchecklists = document.querySelectorAll(
      "[id^='tagsdiv-'] .tagchecklist",
    );

    const observer = new MutationObserver(mutations => {
      mutations.forEach(mutation => {
        if (mutation.type === 'childList') {
          const target = mutation.target as HTMLElement;
          setTimeout(() => {
            const cont = target.innerHTML;
            if (cont === flag && cont !== '') {
              return; // prevent multiple simultaneous triggers
            }
            flag = cont;

            const postboxid = target.closest('.postbox')?.id ?? '';
            const t = postboxid.replace('tagsdiv-', '');
            const s =
              (
                target.parentElement?.querySelector(
                  'textarea.the-tags',
                ) as HTMLTextAreaElement
              )?.value ?? '';

            jQuery
              .ajax({
                url,
                dataType: 'json',
                data: {
                  id,
                  nonce: nonceSync,
                  action: actionSync,
                  taxonomy: t,
                  tags: s,
                },
                type: 'post',
                beforeSend: () => {
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
                  jQuery(
                    `#tagsdiv-${t} h2, #wpto_meta_box-${t} h2`,
                  ).removeClass('ready');
                }, 300);
              });
          }, 20);
        }
      });
    });

    tagchecklists.forEach(tagchecklist => {
      observer.observe(tagchecklist, { childList: true, subtree: true });
    });
  }, 400);
  /*= =================================================
  jQuery UI Sortable
  ================================================== */
  jQuery('.wpto_meta_box_panel .inside .inner ul').sortable({
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
          url,
          type: 'post',
          dataType: 'json',
          data: {
            id,
            nonce: nonceUpdate,
            action: actionUpdate,
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
