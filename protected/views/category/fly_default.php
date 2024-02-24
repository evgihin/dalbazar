<?php /* @var $this SiteController */ ?>
<table id="advertList">
  <?php foreach ($adverts as $advert) { ?>
    <tr>
      <td class="advListPicture">
          <?php if (isset($images[$advert['advert_id']][0]['name'])): ?>
          <img src="<?= Helpers::getImageUrl( $images[$advert['advert_id']][0]['name'],120,120) ?>" width="120px">
              <?php else: ?>
          <img src="<?= Helpers::getImageUrl("",120,120) ?>" width="120px">
              <?php endif; ?>
      </td>
      <td class="advListDescr">
          <div class="advListTitle"><a href="<?= $this->createUrl('advert/show', array('advert_id' => $advert['advert_id'])) ?>"><?= $advert['zagolovok'] ?></a></div>
        <?php
        if (isset($filters[$advert['advert_id']]))
          foreach ($filters[$advert['advert_id']] as $filter) {
            echo '<b>' . $filter['name'] . '</b>' . ':' . $filter['value'] . '<br>';
          }
        ?>
      </td>
    </tr>
  <?php } ?>
</table>