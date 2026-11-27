<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle('Избранное');
$APPLICATION->SetPageProperty('fk_lead', 'Сохранённые товары FORKEL для быстрого сравнения и заказа.');

use Bitrix\Main\Loader;

$ids = \Forkel\Wishlist::getIds();
$items = [];
if ($ids && Loader::includeModule('iblock')) {
    $catalogId = fk_iblock_id('catalog');
    if ($catalogId > 0) {
        $rs = CIBlockElement::GetList(
            ['ID' => 'DESC'],
            ['IBLOCK_ID' => $catalogId, 'ID' => $ids, 'ACTIVE' => 'Y'],
            false,
            false,
            ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'PROPERTY_ARTICLE']
        );
        while ($row = $rs->GetNext()) {
            $img = $row['PREVIEW_PICTURE'] ? (string)CFile::GetPath((int)$row['PREVIEW_PICTURE']) : SITE_TEMPLATE_PATH . '/assets/images/designs/1G Cashmere.jpg';
            $items[] = [
                'id' => (int)$row['ID'],
                'name' => (string)$row['NAME'],
                'url' => (string)$row['DETAIL_PAGE_URL'],
                'article' => (string)($row['PROPERTY_ARTICLE_VALUE'] ?? ''),
                'img' => $img,
            ];
        }
    }
}
?>
<section class="fk-sec" data-wishlist-page>
    <?php if (!$items) { ?>
        <div class="fk-card" style="padding:32px">
            <h2 style="margin-top:0">Список избранного пока пуст</h2>
            <p>Добавляйте понравившиеся розетки, выключатели и рамки в избранное в каталоге, чтобы вернуться к ним позже.</p>
            <p><a class="fk-btn fk-btn--primary" href="/catalog/">Перейти в каталог</a></p>
        </div>
    <?php } else { ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:24px">
            <?php foreach ($items as $it) { ?>
                <article class="fk-card" style="padding:20px;display:flex;flex-direction:column;gap:12px" data-wl-card="<?= $it['id'] ?>">
                    <a href="<?= htmlspecialcharsbx($it['url']) ?>" style="display:block;aspect-ratio:4/3;background:#f5f5f5;border-radius:12px;overflow:hidden">
                        <img src="<?= htmlspecialcharsbx($it['img']) ?>" alt="<?= htmlspecialcharsbx($it['name']) ?>" style="width:100%;height:100%;object-fit:contain" loading="lazy">
                    </a>
                    <h3 style="margin:0;font-size:1.0625rem"><a href="<?= htmlspecialcharsbx($it['url']) ?>"><?= htmlspecialcharsbx($it['name']) ?></a></h3>
                    <?php if ($it['article'] !== '') { ?>
                        <p style="margin:0;color:#6b7280;font-size:.875rem">Артикул: <?= htmlspecialcharsbx($it['article']) ?></p>
                    <?php } ?>
                    <div style="margin-top:auto;display:flex;gap:8px;flex-wrap:wrap">
                        <a class="fk-btn fk-btn--primary" href="<?= htmlspecialcharsbx($it['url']) ?>">Подробнее</a>
                        <button type="button" class="fk-btn fk-btn--secondary" data-wl-remove="<?= $it['id'] ?>">Удалить</button>
                    </div>
                </article>
            <?php } ?>
        </div>
        <script>
        document.querySelectorAll('[data-wl-remove]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-wl-remove');
                const s = await (await fetch('/local/ajax/sessid.php')).json();
                const fd = new FormData();
                fd.append('sessid', s.sessid || '');
                fd.append('id', id);
                const res = await (await fetch('/local/ajax/wishlist.php', { method: 'POST', body: fd })).json();
                if (res.ok) {
                    document.querySelector('[data-wl-card="' + id + '"]')?.remove();
                    if (!document.querySelector('[data-wl-card]')) location.reload();
                }
            });
        });
        </script>
    <?php } ?>
</section>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
