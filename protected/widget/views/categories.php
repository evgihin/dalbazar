<?php
/* $this UCategoryChanger */
/* $categories array список категорий */
$params = '';
if ($this->params) {
    foreach ($this->params as $id => $val) {
        $params .= ' ' . $id . '="' . $val . '"';
    }
}
if (!$this->id)
    $this->id = $this->name;
$this->name = 'name="'.$this->name.'"';
$this->id = 'id="'.$this->id.'"';
?>
<select <?= implode(' ',array($params, $this->name, $this->id)) ?><?= (!$this->autocomplete) ? ' autocomplete="off"' : '' ?>>
    <option><?= $this->emptyText ?></option>
    <?php
    foreach ($categories[$this->parent_id] as $pid => $category) {
        if (!empty($categories[$category['category_id']])) {
            ?>
            <optgroup label="<?= $category['name'] ?>">
                <?php
                foreach ($categories[$category['category_id']] as $subCategory) {
                    ?>
                    <option value="<?= $subCategory['category_id'] ?>" <?= ($subCategory['category_id']==$this->category_id)?'selected="selected"':'' ?>><?= $subCategory['name'] ?></option>
                    <?php
                }
                ?>
            </optgroup>
        <?php } else { ?>
            <option value="<?= $category['category_id'] ?>" <?= ($category['category_id']==$this->category_id)?'selected="selected"':'' ?>><?= $category['name'] ?></option>
            <?php
        }
    }
    ?>
</select>