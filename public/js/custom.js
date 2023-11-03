function truncate(str, limit) {
  return str.length > limit ? str.substr(0, limit) + '&hellip;' : str;
}

const Toast = Swal.mixin({
  toast: true,
  position: 'top-right',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer)
    toast.addEventListener('mouseleave', Swal.resumeTimer)
  }
});

function display_errors(errors, $o, keys, offset_top = 0) {
  let $first = null;

  for(let k of keys) {
    let $obj = $o[k];
    let $feedback = $obj.siblings('.invalid-feedback');

    if (k in errors) {
      $feedback.html(errors[k][0]);
      $obj.removeClass('is-valid').addClass('is-invalid');

      if (!$first) {
        $first = $feedback;
      }
    }
    else {
      $obj.removeClass('is-invalid').addClass('is-valid');
    }
  }

  if ($first) {
    let top = $first.parent().offset().top - offset_top;
    window.scrollTo({top, behavior: 'smooth'});
  }
}

$(() => {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
})