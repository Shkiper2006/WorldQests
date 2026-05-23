<?php
/** @var array<int,array<string,mixed>> $quests */
?>
<div class="world-quests-list" data-world-quests>
    <style>
        .world-quests-list .toolbar { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px; }
        .world-quests-list .toolbar input, .world-quests-list .toolbar select { padding:8px; }
        .world-quests-list .items { display:grid; gap:10px; }
        .world-quests-list .item { border:1px solid #ddd; border-radius:8px; padding:12px; }
    </style>
    <div class="toolbar">
        <input type="search" data-search placeholder="<?php echo esc_attr__('Search quests…', 'world-quest'); ?>" />
        <select data-status>
            <option value=""><?php echo esc_html__('All statuses', 'world-quest'); ?></option>
            <option value="published"><?php echo esc_html__('Published', 'world-quest'); ?></option>
            <option value="draft"><?php echo esc_html__('Draft', 'world-quest'); ?></option>
        </select>
        <select data-sort>
            <option value="desc"><?php echo esc_html__('Newest first', 'world-quest'); ?></option>
            <option value="asc"><?php echo esc_html__('Oldest first', 'world-quest'); ?></option>
            <option value="title"><?php echo esc_html__('Title A-Z', 'world-quest'); ?></option>
        </select>
    </div>
    <div class="items" data-items>
        <?php foreach ($quests as $quest) : ?>
            <article class="item" data-title="<?php echo esc_attr(mb_strtolower((string) $quest['title'])); ?>" data-status="<?php echo esc_attr((string) $quest['status']); ?>" data-id="<?php echo esc_attr((string) $quest['id']); ?>">
                <h3><?php echo esc_html((string) $quest['title']); ?></h3>
                <a href="<?php echo esc_url(add_query_arg(['quest_id' => (int) $quest['id']], get_permalink() ?: '')); ?>">[world_quest id=&quot;<?php echo (int) $quest['id']; ?>&quot;]</a>
            </article>
        <?php endforeach; ?>
    </div>
    <form data-worldquest-public-quest-form>
        <h3>Предложить новый квест</h3>
        <input type="text" name="title" placeholder="Название квеста" required>
        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;">
        <input type="hidden" name="recaptcha_token" value="">
        <button type="submit">Отправить</button>
        <p data-worldquest-quest-message></p>
    </form>
</div>
<script>
(() => {
const root = document.querySelector('[data-world-quests]'); if (!root) return;
const search = root.querySelector('[data-search]'); const status = root.querySelector('[data-status]'); const sort = root.querySelector('[data-sort]');
const list = root.querySelector('[data-items]'); const items = Array.from(list.children);
const apply = () => {
 const q = (search.value || '').toLowerCase(); const st = status.value; const mode = sort.value;
 const filtered = items.filter((el) => (!q || el.dataset.title.includes(q)) && (!st || el.dataset.status === st));
 filtered.sort((a,b)=> mode==='asc'?a.dataset.id-b.dataset.id:mode==='title'?a.dataset.title.localeCompare(b.dataset.title):b.dataset.id-a.dataset.id);
 list.innerHTML=''; filtered.forEach((el)=>list.appendChild(el));
};
[search,status,sort].forEach((el)=>el.addEventListener('input', apply));
const form = root.querySelector('[data-worldquest-public-quest-form]');
if (form) {
 form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(form);
  const req = await fetch('<?php echo esc_url_raw(rest_url('worldquest/v1/public/quests')); ?>', { method: 'POST', body: fd });
  root.querySelector('[data-worldquest-quest-message]').textContent = req.ok ? 'Отправлено на модерацию.' : 'Ошибка отправки.';
 });
}
})();
</script>
