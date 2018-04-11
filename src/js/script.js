// Set zipfile name
$('.btn-unzip').on('click', function (e) {
    e.preventDefault();
    let zipfile = $(this).prev().val();
    $('input[name=zipfile]').val(zipfile);
    $('.form-unzip').submit();
});

// Set delfile name
$('.btn-delete').on('click', function (e) {
    e.preventDefault();
    let delfile = $(this).prev().val();
    $('input[name=delfile]').val(delfile);
    $('.form-unzip').submit();
});

// Set modal data
$('.btn-modal').on('click', function (e) {
    let title = $(this).data('modal-title');
    if (title) {
        $('.modal-title').html(title);
    }
    let body = $(this).data('modal-body');
    if (body) {
        $('.modal-body').html(body);
    }
});

// Form submit confirm with modal dialog
$('.form-unzip').submit(function(e){
    if ($(this).hasClass('confirmed')) {
        return true;
    } else {
        e.preventDefault();
        $('.modal').modal('show');
    }
});
$('.form-confirm').on('click', function (e) {
    $('.form-unzip').addClass('confirmed');
    $('.form-unzip').submit();
});
$('.modal').on('hidden.bs.modal', function (e) {
    $('input[name=zipfile]').val('');
    $('input[name=delfile]').val('');
});

// Notification auto close
let delay = 5000; // 5 s
setTimeout(function(){
    $('.notification').alert('close');
}, delay);
