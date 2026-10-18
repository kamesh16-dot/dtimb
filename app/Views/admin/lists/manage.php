<?php
/** @var App\Models\ListItemModel $list */
$fields  = $list->fields();
?>
<div class="page-header">
  <h1><?= esc($list->label()) ?></h1>
  <div style="display:flex;gap:.5rem;flex-wrap:wrap">
    <a href="<?= admin_url('lists') ?>" class="btn btn-secondary">← All lists</a>
    <a href="<?= admin_url('lists/') ?><?= esc($list->key(), 'url') ?>/add" class="btn btn-primary">
      <svg viewBox="0 0 24 24"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
      Add item
    </a>
  </div>
</div>

<p style="margin:0 0 1rem;font-size:.78rem;color:var(--dim)">
  <?= esc($def['description']) ?> Shown on: <strong><?= esc($def['consumer']) ?></strong>.
</p>

<?php
// The table shows a few fields (the registry's `columns`, else the first
// two); every field is in the row's drawer.
$columns = [];
foreach ($def['columns'] ?? array_slice(array_keys($fields), 0, 2) as $name) {
    if (isset($fields[$name])) {
        $columns[$name] = $fields[$name];
    }
}
// The drawer is titled by the most descriptive field, not a key or a number.
$titleField = array_key_first(array_intersect_key(
    array_flip(['name', 'title', 'question', 'value', 'body']),
    $fields
)) ?? array_key_first($fields);
$listUrl = admin_url('lists/') . $list->key();
?>
<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th style="width:3rem">#</th>
          <?php foreach ($columns as $f): ?><th><?= esc($f['label']) ?></th><?php endforeach ?>
          <th>Status</th>
          <th>Order</th>
          <th><span class="sr-only">Details</span></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="<?= count($columns) + 4 ?>" style="text-align:center;color:var(--dim);padding:2rem">Nothing in this list yet. <a href="<?= admin_url('lists/') ?><?= esc($list->key(), 'url') ?>/add" style="color:var(--red)">Add the first item →</a></td></tr>
        <?php else: ?>
          <?php foreach ($rows as $i => $r): ?>
          <?php $pos = $pager->offset + $i; ?>
          <tr data-drawer="item-<?= $r['id'] ?>" tabindex="0" aria-expanded="false"
              aria-label="Item <?= $pos + 1 ?>: show all details"<?= $r['is_active'] ? '' : ' style="opacity:.5"' ?>>
            <td style="color:var(--dim);font-size:.78rem"><?= $pos + 1 ?></td>
            <?php foreach ($columns as $name => $f): ?>
              <?php $v = $r['values'][$name] ?? ''; $v = is_array($v) ? implode(' · ', $v) : (string) $v; ?>
              <td style="font-size:.82rem"><span class="cell-clip"><?= esc(strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], ' ', $v))) ?></span></td>
            <?php endforeach ?>
            <td>
              <?= $r['is_active']
                    ? '<span class="pill pill-green">Shown</span>'
                    : '<span class="pill pill-orange">Hidden</span>' ?>
            </td>
            <td>
              <div class="actions" style="white-space:nowrap">
                <form method="POST" action="<?= esc($listUrl, 'attr') ?>/move/<?= $r['id'] ?>/up" style="display:inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-secondary" style="padding:.25rem .5rem;font-size:.75rem" title="Move up" aria-label="Move up" <?= $pos === 0 ? 'disabled' : '' ?>>↑</button>
                </form>
                <form method="POST" action="<?= esc($listUrl, 'attr') ?>/move/<?= $r['id'] ?>/down" style="display:inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-secondary" style="padding:.25rem .5rem;font-size:.75rem" title="Move down" aria-label="Move down" <?= $pos === $pager->total - 1 ? 'disabled' : '' ?>>↓</button>
                </form>
              </div>
            </td>
            <td class="row-more" aria-hidden="true">›</td>
          </tr>
          <?php endforeach ?>
        <?php endif ?>
      </tbody>
    </table>
  </div>
</div>
<?php if (! empty($rows)): ?><p class="table-hint">Click a row to see everything in it, and to edit, hide or delete it.</p><?php endif ?>

<?php foreach ($rows as $i => $r): ?>
  <?php
  $details = [];
  foreach ($fields as $name => $f) {
      $v    = $r['values'][$name] ?? null;
      $type = match (true) {
          $f['type'] === 'lines'                    => 'lines',
          $f['type'] === 'numbers'                  => 'text',
          $f['type'] === 'textarea'                 => 'long',
          str_ends_with($name, 'image_key')         => 'image',
          default                                   => 'text',
      };
      if ($f['type'] === 'numbers') {
          $v = implode(', ', (array) $v);
      } elseif ($f['type'] === 'select') {
          $v = $f['options'][$v] ?? $v;
      }
      $details[] = ['label' => $f['label'], 'value' => $v, 'type' => $type];
  }
  $details[] = ['label' => 'On the website', 'type' => 'pill', 'value' => $r['is_active'] ? ['Shown', 'green'] : ['Hidden', 'orange']];
  $details[] = ['label' => 'Position', 'value' => ($pager->offset + $i + 1) . ' of ' . $pager->total];

  $actions = '<a class="btn btn-primary" href="' . esc($listUrl . '/edit/' . $r['id'], 'attr') . '">Edit</a>'
      . '<form method="POST" action="' . esc($listUrl, 'attr') . '/toggle/' . $r['id'] . '">' . csrf_field()
      . '<button type="submit" class="btn btn-secondary">' . ($r['is_active'] ? 'Hide from website' : 'Show on website') . '</button></form>'
      . '<form method="POST" action="' . esc($listUrl, 'attr') . '/delete/' . $r['id'] . '">' . csrf_field()
      . '<button type="submit" class="btn btn-danger" data-confirm="Delete this item permanently? Hiding it instead keeps it recoverable.">Delete</button></form>';

  $first = $r['values'][$titleField] ?? '';
  ?>
  <?= view('admin/partials/drawer', [
      'id'       => 'item-' . $r['id'],
      'title'    => mb_strimwidth(strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], ' ', is_array($first) ? implode(' ', $first) : (string) $first)), 0, 70, '…') ?: $list->label(),
      'subtitle' => $list->label() . ' · item ' . ($pager->offset + $i + 1),
      'rows'     => $details,
      'actions'  => $actions,
  ]) ?>
<?php endforeach ?>

<?= view('admin/partials/pagination', ['pager' => $pager, 'noun' => 'items', 'one' => 'item']) ?>
