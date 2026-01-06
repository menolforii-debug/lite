<section class="infoblock-detail">
    <h2><?= htmlspecialchars($title) ?></h2>
    <?php if ($record === null): ?>
        <p>Элемент не найден.</p>
    <?php else: ?>
        <article>
            <h3><?= htmlspecialchars($record['title']) ?></h3>
            <div><?= $record['content_html'] ?></div>
        </article>
    <?php endif; ?>
</section>
