declare global {
  interface Window {
    wto_data: WtoData;
  }
}

/**
 * Represents the global data structure for WordPress post operations
 *
 * @remarks
 * Contains essential information for AJAX synchronization and updates
 */
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

/**
 * Manages post-related tag synchronization and UI interactions
 *
 * @remarks
 * Handles two primary functionalities:
 * 1. Synchronizes tags across different taxonomy boxes
 * 2. Enables drag-and-drop sorting of tags with AJAX updates
 */
export const post = () => {
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
                ) as HTMLTextAreaElement | null
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
                // eslint-disable-next-line no-alert
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
          beforeSend() {
            jQuery(`#wpto_meta_box-${t} h2`).addClass('ready');
          },
        })
        .done(() => {})
        .fail(() => {
          // eslint-disable-next-line no-alert
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
