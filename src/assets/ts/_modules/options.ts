import Swal from 'sweetalert2';

declare global {
  interface Window {
    wto_options_data: WtoOptionsData;
  }
}
declare let jQuery: any;

interface WtoOptionsData {
  nonce: string;
  action: string;
  ajax_url: string;
}

export const options = (): void => {
  jQuery('#wpbody-content form input[name=apply]').on('click', (): boolean => {
    Swal.queue([
      {
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, apply!',
        showLoaderOnConfirm: true,
        preConfirm(): Promise<string> {
          return new Promise<string>((resolve): void => {
            jQuery
              .ajax({
                url: window.wto_options_data.ajax_url,
                dataType: 'json',
                data: {
                  nonce: window.wto_options_data.nonce,
                  action: window.wto_options_data.action,
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
              .done((): void => {})
              .fail((): void => {
                alert('Load Error. Please Reload...');
              })
              .always((data: number): void => {
                const h = `<p><strong>Applied to the ${data} posts.</strong></p>`;
                jQuery('#setting-apply-settings_updated').html(h);
                Swal.insertQueueStep({
                  icon: 'success',
                  title: `Applied to ${data} posts.`,
                });
                resolve();
              });
          });
        },
      },
    ]);

    return false;
  });
};
