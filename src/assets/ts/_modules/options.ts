declare let jQuery: any;
declare let swal: any;
declare let wto_options_data: any; // eslint-disable-line @typescript-eslint/camelcase

export const options = (): void => {
  jQuery('#wpbody-content form input[name=apply]').on('click', () => {
    swal.queue([
      {
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, apply!',
        showLoaderOnConfirm: true,
        preConfirm(): any {
          return new Promise<string>((resolve: any | null): any => {
            jQuery
              .ajax({
                url: wto_options_data.ajax_url,
                dataType: 'json',
                data: {
                  nonce: wto_options_data.nonce,
                  action: wto_options_data.action,
                },
                type: 'post',
                beforeSend() {
                  jQuery('#wpbody-content form input[name=apply]').prop('disabled', true);
                  const $html = '<p><strong class="processing">Processing.</strong></p>';
                  jQuery('#setting-apply-settings_updated')
                    .html($html)
                    .fadeIn();
                },
              })
              .done(() => {})
              .fail(() => {
                alert('Load Error. Please Reload...');
              })
              .always((data: any) => {
                const $html = `<p><strong>Applied to the ${data} posts.</strong></p>`;
                jQuery('#setting-apply-settings_updated').html($html);
                // For sweetalert2.js
                const str = `Applied to ${data} posts.`;
                swal.insertQueueStep(str);
                resolve();
              });
          });
        },
      },
    ]);

    return false;
  });
};
