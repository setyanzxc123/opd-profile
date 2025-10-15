<?php
  $eyebrow     = trim((string) ($eyebrow ?? ''));
  $title       = trim((string) ($title ?? ''));
  $description = trim((string) ($description ?? ''));
  $align       = strtolower(trim((string) ($align ?? 'left')));
  $maxWidth    = strtolower(trim((string) ($maxWidth ?? '2xl')));
  $headingId   = trim((string) ($id ?? ''));

  $wrapperClasses = ['space-y-3'];
  $descriptionClasses = ['text-sm', 'leading-relaxed', 'text-neutral-600'];

  if ($align === 'center') {
      $wrapperClasses[] = 'mx-auto';
      $wrapperClasses[] = 'text-center';
      $descriptionClasses[] = 'mx-auto';
  }

  $maxWidthMap = [
      'lg'  => 'max-w-lg',
      'xl'  => 'max-w-xl',
      '2xl' => 'max-w-2xl',
      '3xl' => 'max-w-3xl',
  ];

  if (isset($maxWidthMap[$maxWidth])) {
      $wrapperClasses[] = $maxWidthMap[$maxWidth];
  }
?>
<div class="<?= esc(implode(' ', $wrapperClasses), 'attr') ?>">
  <?php if ($eyebrow !== ''): ?>
    <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.35em] text-sky-600"><?= esc($eyebrow) ?></span>
  <?php endif; ?>

  <?php if ($title !== ''): ?>
    <h2 class="text-3xl font-semibold text-neutral-900"<?= $headingId !== '' ? ' id="' . esc($headingId, 'attr') . '"' : '' ?>><?= esc($title) ?></h2>
  <?php endif; ?>

  <?php if ($description !== ''): ?>
    <p class="<?= esc(implode(' ', $descriptionClasses), 'attr') ?>"><?= esc($description) ?></p>
  <?php endif; ?>
</div>
