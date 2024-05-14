$(document).ready(function () {
    let paymentCheckInterval;
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    $('[data-modal]').click(function () {
        const id = $(this).attr('data-modal'),
            el = $('#modal-' + id);
        if(el.hasClass('open')) {
            el.removeClass('open');
            if(id === "pay" && paymentCheckInterval) closeModalPendingPayment();
        } else {
            el.addClass('open');
        }
    });
    $('.modal .modal-foreign').click(function () {
        const getModalId = $(this).parent().attr('id'), el = $('#' + getModalId);
        if(el.hasClass('open')) {
            el.removeClass('open');
            if(getModalId === "modal-pay" && paymentCheckInterval) {
                closeModalPendingPayment();
            }
        }
    });
    $('.modal .modal-content .modal-close').click(function () {
        const getModalId = $(this).parent().parent().attr('id'), el = $('#' + getModalId);
        if(el.hasClass('open')) {
            el.removeClass('open');
            if(getModalId === "modal-pay" && paymentCheckInterval) {
                closeModalPendingPayment();
            }
        }
    });
    $('#modal-pay .btn-pay').click(function () {
        if($('#modal-pay .btn-pay').prop('disabled')) return;
        $('#modal-pay .btn-pay').prop('disabled', true);
        const amount = $('#modal-pay input[name="payment-amount"]').val();
        $.ajax({
            url: "/api/user/payment/create",
            type: "post",
            data: {
                amount: amount
            },
            success: function (data) {
                $('#modal-pay .btn-pay').prop('disabled', false);
                if(!data.success) return $.notify(data.msg, {type: "danger"});
                $('#modal-pay .payment-form[data-pay-form="start"]').css({display: 'none'});
                $('#modal-pay .payment-form[data-pay-form="pending"]').css({display: 'flex'});
                $('#modal-pay .payment-form[data-pay-form="pending"] input[name="pending-payment-card"]').val(data.pay.card);
                $('#modal-pay .payment-form[data-pay-form="pending"] input[name="pending-payment-amount"]').val(data.pay.amount + ' руб.');
                paymentCheckInterval = setInterval(() => checkPayment(data.pay.id), 5000);
            },
        });
    });

    function checkPayment(id) {
        $.ajax({
            url: "/api/user/payment/check",
            type: "post",
            data: {
                id: id
            },
            success: function (data) {
                if(!data.success) {
                    if(data.status === "cancel") closeModalPendingPayment();
                    if(data.msg !== undefined) return $.notify(data.msg, {type: "danger"});
                } else {
                    closeModalPendingPayment();
                    $('.header .header-content .profile .balance').text(data.balance + 'р');
                    return $.notify(data.msg, {type: "success"});
                }
            },
        });
    }

    function closeModalPendingPayment() {
        const el = $('#modal-pay');
        if(el.hasClass('open')) {
            el.removeClass('open');
        }
        $('#modal-pay .payment-form[data-pay-form="start"]').css({display: 'flex'});
        $('#modal-pay .payment-form[data-pay-form="pending"]').css({display: 'none'});
        $('#modal-pay .payment-form[data-pay-form="pending"] input[name="pending-payment-card"]').val('');
        $('#modal-pay .payment-form[data-pay-form="pending"] input[name="pending-payment-amount"]').val('');
        if(paymentCheckInterval) {
            clearInterval(paymentCheckInterval);
        }
    }
});
