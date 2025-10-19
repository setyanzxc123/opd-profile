<?php
/**
 * @var array<int,array<string,mixed>> $trail
 * @var string|null $ariaLabel
 */
  $trail = $trail ?? [];
  $ariaLabel = $ariaLabel ?? 'Breadcrumb';

  if ($trail === []) {
      return;
  }
?>
<nav aria-label="<?= esc($ariaLabel) ?>" class="public-breadcrumb">
  <ol class="breadcrumb mb-0">
    <?php foreach ($trail as $index => $item): ?>
      <?php
        $isActive = ! empty($item['active']) || $index === array_key_last($trail);
        $label    = (string) ($item['label'] ?? '');
        $url      = (string) ($item['url'] ?? '');
      ?>
      <li class="breadcrumb-item<?= $isActive ? ' active' : '' ?>"<?= $isActive ? ' aria-current="page"' : '' ?>>
        <?php if (! $isActive && $url !== ''): ?>
          <a href="<?= esc($url) ?>"><?= esc($label) ?></a>
        <?php else: ?>
          <?= esc($label) ?>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</nav>
