import Swal from 'sweetalert2';

declare let jQuery: any;
declare let wto_options_data: any; // eslint-disable-line @typescript-eslint/camelcase

export const options = (): void => {
  jQuery('#wpbody-content form input[name=apply]').on('click', () => {
    Swal.queue([
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
          return new Promise<string>((resolve: any | null): void => {
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
                  const h = '<p><strong class="processing">Processing.</strong></p>';
                  jQuery('#setting-apply-settings_updated')
                    .html(h)
                    .fadeIn();
                },
              })
              .done(() => {})
              .fail(() => {
                alert('Load Error. Please Reload...');
              })
              .always((data: number) => {
                const h = `<p><strong>Applied to the ${data} posts.</strong></p>`;
                jQuery('#setting-apply-settings_updated').html(h);
                // For sweetalert2.js
                const str: any = `Applied to ${data} posts.`;
                Swal.insertQueueStep(str);
                resolve();
              });
          });
        },
      },
    ]);

    return false;
  });
};
