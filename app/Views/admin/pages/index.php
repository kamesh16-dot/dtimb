<div class="page-header"><h1>Pages</h1></div>
<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Page</th><th>Meta title</th><th></th></tr></thead>
      <tbody>
        <?php
        $pages = [
          'home' => 'Home','about' => 'About','machines' => 'Machines',
          'infrastructure' => 'Infrastructure','projects' => 'Projects',
          'clients' => 'Clients','industries' => 'Industries',
          'careers' => 'Careers','news' => 'News','contact' => 'Contact','disclaimer' => 'Disclaimer'
        ];
        $rowsBySlug = array_column($rows ?? [], null, 'slug');
        foreach ($pages as $slug => $label):
          $r = $rowsBySlug[$slug] ?? [];
        ?>
        <tr>
          <td><?= esc($label) ?></td>
          <td style="font-size:.78rem;color:var(--muted)"><?= esc($r['meta_title'] ?? '—') ?></td>
          <td><a href="/admin/pages/edit/<?= $slug ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Edit</a></td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
