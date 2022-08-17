<?php
/**
 * @var \Avolle\UpcomingMatches\Game[]|\Cake\Collection\CollectionInterface $matchesCollection
 */
?>
<table>
    <thead>
    <tr>
        <th>Dato</th>
        <th>Dag</th>
        <th>Tid</th>
        <th>Hjemmelag</th>
        <th>Bortelag</th>
        <th>Bane</th>
        <th>Turnering</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($matchesCollection as $match): ?>
        <tr>
            <td><?= $match->date->format("d.m.Y"); ?></td>
            <td><?= h($match->day); ?></td>
            <td><?= h($match->time); ?></td>
            <td><?= h($match->homeTeam); ?></td>
            <td><?= h($match->awayTeam); ?></td>
            <td><?= h($match->pitch); ?></td>
            <td><?= h($match->tournament); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

