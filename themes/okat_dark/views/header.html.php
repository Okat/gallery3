<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gBanner">
  <?= $theme->header_top() ?>
  <? if ($header_text = module::get_var("gallery", "header_text")): ?>
  <?= $header_text ?>
  <? else: ?>
  <a id="gLogo" href="<?= url::site("albums/1") ?>" title="<?= t("go back to the Gallery home") ?>">
    <img width="110" height="61" style="border:none" alt="<?= t("Gallery logo: Your photos on your web site") ?>" src="<?= $theme->url("images/okat_logo_31-03-09.jpg") ?>" />
  </a>
  <? endif ?>
  <div id="gSiteMenu">
  <?= $theme->site_menu() ?>
  </div>
  <?= $theme->header_bottom() ?>
</div>

<? if (!empty($parents)): ?>
<ul class="gBreadcrumbs">
  <? foreach ($parents as $parent): ?>
  <li>
    <a href="<?= url::site("albums/{$parent->id}?show=$item->id") ?>">
      <?= p::purify($parent->title) ?>
    </a>
  </li>
  <? endforeach ?>
  <li class="active"><?= p::purify($item->title) ?></li>
</ul>
<? endif ?>