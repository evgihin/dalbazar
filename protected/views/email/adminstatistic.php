<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<h3>Статистика за день:</h3>
    <table>
        <tr>
            <th>Действие</th>
            <th>Описание</th>
            <th>Кол-во</th>
        </tr>
        <?php if (!$day || !count($day)): ?>
            <tr><td colspan="3">Действий не зарегистрировано</td></tr>
            <?php
        else:
            foreach ($day as $actId => $data) {
                ?>
                <tr>
                    <td><?= $actId ?></td>
                    <td><?= $data['description'] ?></td>
                    <td><?= $data['count'] ?></td>
                </tr>
            <?php
            }
        endif;
        ?>
    </table>
<h3>Статистика за неделю:</h3>
    <table>
        <tr>
            <th>Действие</th>
            <th>Описание</th>
            <th>Кол-во</th>
        </tr>
        <?php if (!$week || !count($week)): ?>
            <tr><td colspan="3">Действий не зарегистрировано</td></tr>
            <?php
        else:
            foreach ($week as $actId => $data) {
                ?>
                <tr>
                    <td><?= $actId ?></td>
                    <td><?= $data['description'] ?></td>
                    <td><?= $data['count'] ?></td>
                </tr>
            <?php
            }
        endif;
        ?>
    </table>
<h3>Статистика за месяц:</h3>
    <table>
        <tr>
            <th>Действие</th>
            <th>Описание</th>
            <th>Кол-во</th>
        </tr>
        <?php if (!$month || !count($month)): ?>
            <tr><td colspan="3">Действий не зарегистрировано</td></tr>
            <?php
        else:
            foreach ($month as $actId => $data) {
                ?>
                <tr>
                    <td><?= $actId ?></td>
                    <td><?= $data['description'] ?></td>
                    <td><?= $data['count'] ?></td>
                </tr>
            <?php
            }
        endif;
        ?>
    </table>
