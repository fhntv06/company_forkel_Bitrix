<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** Окна «Написать нам» и «Заказать звонок» (разметка V4, один <dialog>; режим переключает js/feedback.js). Отправка — /local/ajax/feedback.php. */
?>
<dialog class="fk-fb" aria-labelledby="fb-title" data-fb-action="/local/ajax/feedback.php">
    <div class="fk-fb__panel">
        <div class="fk-fb__head">
            <h2 class="fk-fb__title" id="fb-title" tabindex="-1">Написать нам</h2>
            <button class="fk-fb__close" type="button" aria-label="Закрыть окно"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg></button>
        </div>
        <div class="fk-fb__body">
            <form class="fk-fb__form" novalidate method="post">
                <input type="hidden" name="sessid" value="<?= bitrix_sessid() ?>">
                <input type="hidden" name="mode" value="feedback">
                <div style="position:absolute;left:-9999px" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                <p class="fk-fb__lead" data-fb-lead>Оставьте сообщение — мы свяжемся с вами.</p>
                <div class="fk-fb__errors fk-co-errors" role="alert" hidden></div>
                <div class="fk-co-field"><label class="fk-field__label" for="fb-name">Имя</label><input class="fk-field" id="fb-name" name="name" type="text" autocomplete="name" required aria-describedby="fb-name-err"><p class="fk-co-error" id="fb-name-err" hidden></p></div>
                <div class="fk-co-field"><label class="fk-field__label" for="fb-contact" data-fb-contact-label>Телефон или email</label><input class="fk-field" id="fb-contact" name="contact" type="text" autocomplete="off" required aria-describedby="fb-contact-err"><p class="fk-co-error" id="fb-contact-err" hidden></p></div>
                <div class="fk-co-field" data-fb-msg-field><label class="fk-field__label" for="fb-msg">Сообщение</label><textarea class="fk-field fk-co-textarea" id="fb-msg" name="msg" rows="4" required aria-describedby="fb-msg-err"></textarea><p class="fk-co-error" id="fb-msg-err" hidden></p></div>
                <div class="fk-co-field fk-fb__consent"><label class="fk-check"><input type="checkbox" id="fb-consent" name="consent" value="Y" required aria-describedby="fb-consent-err"><span>Согласен с обработкой персональных данных (<a href="/about/privacy/" target="_blank" rel="noopener">политика</a>)</span></label><p class="fk-co-error" id="fb-consent-err" hidden></p></div>
                <button class="fk-btn fk-btn--primary fk-fb__submit" type="submit">Отправить</button>
            </form>
            <div class="fk-fb__done" hidden>
                <h3 class="fk-fb__done-title" tabindex="-1">Спасибо!</h3>
                <p data-fb-done-text>Сообщение отправлено. Мы свяжемся с вами.</p>
                <button class="fk-btn fk-btn--primary fk-fb__ok" type="button">Закрыть</button>
            </div>
        </div>
    </div>
</dialog>
