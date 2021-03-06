<?php defined("SYSPATH") or die("No direct script access.") ?>

<? if (access::can("view_full", $theme->item())): ?>
<!-- Use javascript to show the full size as an overlay on the current page -->
<script>
  $(document).ready(function() {
    $(".gFullSizeLink").click(function() {
      $.gallery_show_full_size("<?= $theme->item()->file_url() ?>", "<?= $theme->item()->width ?>", "<?= $theme->item()->height ?>");
      return false;
    });
  });
</script>
<? endif ?>

<div id="gItem">
  <?= $theme->photo_top() ?>

  <ul class="gPager">
    <li>
      <? if ($previous_item): ?>
      <a href="<?= $previous_item->url() ?>" class="gButtonLink ui-icon-left ui-state-default ui-corner-all">
      <span class="ui-icon ui-icon-triangle-1-w"></span><?= t("previous") ?></a>
      <? else: ?>
      <a class="gButtonLink ui-icon-left ui-state-disabled ui-corner-all">
      <span class="ui-icon ui-icon-triangle-1-w"></span><?= t("previous") ?></a>
      <? endif; ?>
    </li>
    <li class="gInfo"><?= t("%position of %total", array("position" => $position, "total" => $sibling_count)) ?></li>
    <li class="txtright">
      <? if ($next_item): ?>
      <a href="<?= $next_item->url() ?>" class="gButtonLink ui-icon-right ui-state-default ui-corner-all">
      <span class="ui-icon ui-icon-triangle-1-e"></span><?= t("next") ?></a>
      <? else: ?>
      <a class="gButtonLink ui-icon-right ui-state-disabled ui-corner-all">
      <span class="ui-icon ui-icon-triangle-1-e"></span><?= t("next") ?></a>
      <? endif ?>
    </li>
  </ul>

  <div id="gPhoto">
	<?= $theme->context_menu($item, "#gPhotoId-{$item->id}") ?>
    <?= $theme->resize_top($item) ?>
    <? if (access::can("view_full", $item)): ?>
    <a href="<?= $item->file_url() ?>" class="gFullSizeLink" title="<?= t("View full size") ?>">
      <? endif ?>
      <?= $item->resize_img(array("id" => "gPhotoId-{$item->id}", "class" => "gResize")) ?>
      <? if (access::can("view_full", $item)): ?>
    </a>
    <? endif ?>
    <?= $theme->resize_bottom($item) ?>
    
  </div>

  <div id="gInfo">
    <h3><?= p::purify($item->title) ?></h3>
    <div><?= nl2br(p::purify($item->description)) ?></div>
  </div>

  <script type="text/javascript">
    var ADD_A_COMMENT = "<?= t("Add a comment") ?>";
  </script>
  <?= $theme->photo_bottom() ?>
</div>
