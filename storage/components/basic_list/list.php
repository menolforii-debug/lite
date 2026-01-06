<section class="infoblock">
    <h2><?= htmlspecialchars($title) ?></h2>
    <?php if ($list === []): ?>
        <p>Нет элементов.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($list as $row): ?>
                <li>
                    <strong><?= htmlspecialchars($row['title']) ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
