<?php if (count($users) > 0) : ?>

    <?php foreach ($users as ['id' => $id, 'nickname' => $nickname]) : ?>
        <p>id: <?= htmlspecialchars($id) ?> nickname: <a href="/user/<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($nickname) ?></a></p>
    <?php endforeach; ?>

<?php else : ?>

    <p>Больше нет пользователей</p>

<?php endif; ?>
<a href="?page=<?= $page < 2 ? 1 : $page - 1 ?>">previous</a> <a href="?page=<?= $page + 1 ?>">next</a>

<!-- написать функцию которая новый массив пользователей записывает json ом в файл -->