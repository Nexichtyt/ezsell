<div id="modal-pay" class="modal">
    <div class="modal-foreign"></div>
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <div class="modal-title">Пополнение</div>
        <div class="payment-form" data-pay-form="start">
            <div class="form-field">
                <label>Сумма</label>
                <input type="text" class="form-control" name="payment-amount" placeholder="Введите сумму пополнения" value="100" />
            </div>
            <button type="button" class="btn-pay">Продолжить</button>
        </div>
        <div class="payment-form" data-pay-form="pending" style="display: none;">
            <div class="form-field">
                <label>Номер карты</label>
                <input type="text" class="form-control" name="pending-payment-card" value="" />
            </div>
            <div class="form-field">
                <label>Сумма</label>
                <input type="text" class="form-control" name="pending-payment-amount" readonly value="" />
            </div>
            <small>Платеж проверяется каждые 5 секунд</small>
        </div>
    </div>
</div>
