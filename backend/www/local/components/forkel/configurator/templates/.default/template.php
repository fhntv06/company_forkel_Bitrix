<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
$e = static fn(string $s): string => htmlspecialcharsbx($s);
$cfgJson = \Bitrix\Main\Web\Json::encode($arResult);
?>
<section class="fk-sec" id="fk-configurator" data-configurator>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:32px;align-items:start">
        <div class="fk-card" style="padding:32px;text-align:center;background:#f7f7f7">
            <div style="position:relative;width:100%;max-width:420px;aspect-ratio:1/1;margin:0 auto;display:grid;place-items:center">
                <img id="fk-cfg-frame" src="<?= $e($arResult['COLORS']['white_matte']['frame']) ?>" alt="Рамка FORKEL" style="position:absolute;inset:0;width:100%;height:100%;object-fit:contain">
                <img id="fk-cfg-mech" src="<?= $e($arResult['COLORS']['white_matte']['mech']) ?>" alt="Механизм FORKEL" style="position:absolute;inset:0;width:100%;height:100%;object-fit:contain">
            </div>
            <p id="fk-cfg-summary" style="margin-top:16px;font-weight:600;color:#031627"></p>
            <p id="fk-cfg-price" style="font-size:1.5rem;font-weight:700;margin:8px 0 0;color:#031627"></p>
        </div>

        <form class="fk-card" id="fk-cfg-form" style="padding:32px;display:grid;gap:20px">
            <div>
                <label class="fk-step__label" for="cfg-collection">Коллекция</label>
                <select id="cfg-collection" name="collection" style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:10px;margin-top:6px">
                    <?php foreach ($arResult['COLLECTIONS'] as $k => $c) { ?>
                        <option value="<?= $e($k) ?>"><?= $e($c['name']) ?> — <?= $e($c['desc']) ?></option>
                    <?php } ?>
                </select>
            </div>

            <div>
                <label class="fk-step__label" for="cfg-type">Тип изделия</label>
                <select id="cfg-type" name="product_type" style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:10px;margin-top:6px">
                    <?php foreach ($arResult['TYPES'] as $k => $t) { ?>
                        <option value="<?= $e($k) ?>"><?= $e($t['name']) ?> (<?= (int)$t['price'] ?> ₽)</option>
                    <?php } ?>
                </select>
            </div>

            <div>
                <label class="fk-step__label" for="cfg-material">Материал рамки</label>
                <select id="cfg-material" name="material" style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:10px;margin-top:6px">
                    <?php foreach ($arResult['MATERIALS'] as $k => $m) { ?>
                        <option value="<?= $e($k) ?>"><?= $e($m['name']) ?><?= $m['add'] > 0 ? ' (+' . (int)$m['add'] . ' ₽)' : '' ?></option>
                    <?php } ?>
                </select>
            </div>

            <div>
                <p class="fk-step__label">Цвет рамки: <span id="cfg-frame-label">Белый матовый</span></p>
                <div class="fk-swatches" data-cfg-group="frame_color">
                    <?php foreach ($arResult['COLORS'] as $k => $c) { ?>
                        <button type="button" class="fk-swatch" data-val="<?= $e($k) ?>" style="--finish:<?= $e($c['hex']) ?>" aria-label="<?= $e($c['name']) ?>"<?= $k === 'white_matte' ? ' aria-current="true"' : '' ?>></button>
                    <?php } ?>
                </div>
                <input type="hidden" name="frame_color" value="white_matte">
            </div>

            <div>
                <p class="fk-step__label">Цвет механизма: <span id="cfg-mech-label">Белый матовый</span></p>
                <div class="fk-swatches" data-cfg-group="mech_color">
                    <?php foreach ($arResult['COLORS'] as $k => $c) { ?>
                        <button type="button" class="fk-swatch" data-val="<?= $e($k) ?>" style="--finish:<?= $e($c['hex']) ?>" aria-label="<?= $e($c['name']) ?>"<?= $k === 'white_matte' ? ' aria-current="true"' : '' ?>></button>
                    <?php } ?>
                </div>
                <input type="hidden" name="mech_color" value="white_matte">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div>
                    <label class="fk-step__label" for="cfg-posts">Количество постов</label>
                    <select id="cfg-posts" name="posts" style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:10px;margin-top:6px">
                        <?php for ($p = 1; $p <= 5; $p++) { ?>
                            <option value="<?= $p ?>"><?= $p ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label class="fk-step__label" for="cfg-qty">Количество комплектов</label>
                    <input id="cfg-qty" name="qty" type="number" min="1" max="99" value="1" style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:10px;margin-top:6px">
                </div>
            </div>

            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
                <button type="submit" class="fk-btn fk-btn--primary" id="fk-cfg-add">Добавить комплект в корзину</button>
                <a class="fk-btn fk-btn--secondary" href="<?= $e($arResult['CART_URL']) ?>" id="fk-cfg-go-cart" hidden>Перейти в корзину</a>
            </div>
            <p id="fk-cfg-msg" role="status" aria-live="polite" style="margin:0;font-size:.9375rem"></p>
        </form>
    </div>
</section>
<script>
(function () {
    const cfg = <?= $cfgJson ?>;
    const form = document.getElementById('fk-cfg-form');
    if (!form) return;
    const imgFrame = document.getElementById('fk-cfg-frame');
    const imgMech = document.getElementById('fk-cfg-mech');
    const summary = document.getElementById('fk-cfg-summary');
    const priceEl = document.getElementById('fk-cfg-price');
    const msgEl = document.getElementById('fk-cfg-msg');
    const goCart = document.getElementById('fk-cfg-go-cart');

    function updatePreview() {
        const col = form.collection.value;
        const type = form.product_type.value;
        const mat = form.material.value;
        const fc = form.frame_color.value;
        const mc = form.mech_color.value;
        const posts = parseInt(form.posts.value, 10) || 1;
        const qty = Math.max(1, parseInt(form.qty.value, 10) || 1);

        imgFrame.src = cfg.COLORS[fc].frame;
        imgMech.src = cfg.COLORS[mc].mech;
        document.getElementById('cfg-frame-label').textContent = cfg.COLORS[fc].name;
        document.getElementById('cfg-mech-label').textContent = cfg.COLORS[mc].name;

        const unit = (cfg.TYPES[type].price + cfg.MATERIALS[mat].add) * posts;
        const total = unit * qty;
        summary.textContent = `${cfg.COLLECTIONS[col].name} · ${cfg.TYPES[type].name} · ${posts} пост(а) · рамка ${cfg.COLORS[fc].name} (${cfg.MATERIALS[mat].name}) / механизм ${cfg.COLORS[mc].name}`;
        priceEl.textContent = total.toLocaleString('ru-RU') + ' ₽';
    }

    form.querySelectorAll('[data-cfg-group]').forEach(group => {
        const name = group.getAttribute('data-cfg-group');
        group.querySelectorAll('button[data-val]').forEach(btn => {
            btn.addEventListener('click', () => {
                group.querySelectorAll('button').forEach(b => b.removeAttribute('aria-current'));
                btn.setAttribute('aria-current', 'true');
                form.elements[name].value = btn.getAttribute('data-val');
                updatePreview();
            });
        });
    });

    ['collection', 'product_type', 'material', 'posts', 'qty'].forEach(n => {
        form.elements[n].addEventListener('change', updatePreview);
    });
    updatePreview();

    form.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        const btn = document.getElementById('fk-cfg-add');
        btn.disabled = true;
        msgEl.textContent = 'Добавляем комплект в корзину...';
        try {
            const sRes = await fetch(cfg.SESSID_URL);
            const sData = await sRes.json();
            const fd = new FormData(form);
            fd.append('sessid', sData.sessid || '');
            const res = await fetch(cfg.ADD_KIT_URL, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.ok) {
                msgEl.textContent = 'Комплект добавлен в корзину!';
                goCart.hidden = false;
                document.querySelectorAll('[data-cart-badge]').forEach(el => {
                    el.textContent = data.count;
                    el.hidden = !data.count;
                });
            } else {
                msgEl.textContent = (data.errors || ['Ошибка добавления']).join(' ');
            }
        } catch (e) {
            msgEl.textContent = 'Ошибка соединения с сервером.';
        } finally {
            btn.disabled = false;
        }
    });
})();
</script>
