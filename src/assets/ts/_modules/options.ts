import Swal from 'sweetalert2';

declare global {
  interface Window {
    wto_options_data: WtoOptionsData;
  }
}

interface WtoOptionsData {
  nonce: string;
  action: string;
  ajax_url: string;
}

interface WtoOptionsResponse {
  count: number;
}

export const options = (): void => {
  const { nonce, action, ajax_url: url } = window.wto_options_data;

  const beforeSend = () =>
    new Promise<string>(resolve => {
      jQuery('#wpbody-content form input[name=apply]').prop('disabled', true);
      const h = '<p><strong class="processing">Processing.</strong></p>';
      jQuery('#setting-apply-settings_updated').html(h).fadeIn();

      resolve('resolved');
    });

  const asyncPreConfirm = async () => {
    await beforeSend();

    try {
      const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8',
          'Cache-Control': 'no-cache',
        },
        body: `action=${action}&nonce=${nonce}`,
      });

      if (!response.ok) {
        throw new Error(response.statusText);
      }

      const data: WtoOptionsResponse = await response.json();
      return data;
    } catch (error) {
      Swal.showValidationMessage(`Request failed: ${error}`);
      console.log(error);
      throw error;
    }
  };

  jQuery('#wpbody-content form input[name=apply]').on('click', () => {
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'OK',
      showLoaderOnConfirm: true,
      preConfirm: () => {
        return asyncPreConfirm();
      },
    }).then(result => {
      if (result.isConfirmed) {
        const h = `<p><strong>Applied to the ${result.value?.count} posts.</strong></p>`;
        jQuery('#setting-apply-settings_updated').html(h);
        Swal.fire({
          icon: 'success',
          title: `Applied to ${result.value?.count} posts.`,
        });
      }
    });

    return false;
  });
};
