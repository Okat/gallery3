<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class L10n_Client_Controller extends Controller {
  public function save() {
    access::verify_csrf();
    if (!user::active()->admin) {
      access::forbidden();
    }

    $locale = I18n::instance()->locale();
    $input = Input::instance();
    $key = $input->post("l10n-message-key");

    $root_message = ORM::factory("incoming_translation")
        ->where(array("key" => $key,
                      "locale" => "root"))
        ->find();

    if (!$root_message->loaded) {
      throw new Exception("@todo bad request data / illegal state");
    }
    $is_plural = I18n::is_plural_message(unserialize($root_message->message));

    if ($is_plural) {
      $plural_forms = l10n_client::plural_forms($locale);
      $translation = array();
      foreach($plural_forms as $plural_form) {
        $value = $input->post("l10n-edit-plural-translation-$plural_form");
        if (null === $value || !is_string($value)) {
          throw new Exception("@todo bad request data");
        }
        $translation[$plural_form] = $value;
      }
    } else {
      $translation = $input->post("l10n-edit-translation");
      if (null === $translation || !is_string($translation)) {
        throw new Exception("@todo bad request data");
      }
    }

    $entry = ORM::factory("outgoing_translation")
      ->where(array("key" => $key,
                    "locale" => $locale))
      ->find();

    if (!$entry->loaded) {
      $entry->key = $key;
      $entry->locale = $locale;
      $entry->message = $root_message->message;
      $entry->base_revision = null;
    }

    $entry->translation = serialize($translation);

    $entry_from_incoming = ORM::factory("incoming_translation")
      ->where(array("key" => $key,
                    "locale" => $locale))
      ->find();

    if (!$entry_from_incoming->loaded) {
      $entry->base_revision = $entry_from_incoming->revision;
    }

    $entry->save();

    print json_encode(new stdClass());
  }

  public function toggle_l10n_mode() {
    access::verify_csrf();
    if (!user::active()->admin) {
      access::forbidden();
    }

    $session = Session::instance();
                $l10n_mode = $session->get("l10n_mode", false);
    $session->set("l10n_mode", !$l10n_mode);

    $redirect_url = "admin/languages";
                if (!$l10n_mode) {
                        $redirect_url .= "#l10n-client";
                }
 
    url::redirect($redirect_url);
  }

  private static function _l10n_client_search_form() {
    $form = new Forge("l10n_client/search", "", "post", array("id" => "gL10nSearchForm"));
    $group = $form->group("l10n_search");
    $group->input("l10n-search")->id("gL10nSearch");
    $group->submit("l10n-search-filter-clear")->value(t("X"));

    return $form;
  }

  public static function l10n_form() {
    if (Input::instance()->get("show_all_l10n_messages")) {
      $calls = array();
      foreach (Database::instance()
               ->select("key", "message")
               ->from("incoming_translations")
               ->where(array("locale" => 'root'))
               ->get()
               ->as_array() as $row) {
        $calls[$row->key] = array(unserialize($row->message), array());
      }
    } else {
      $calls = I18n::instance()->call_log();
    }
    $locale = I18n::instance()->locale();

    if ($calls) {
      $translations = array();
      foreach (Database::instance()
               ->select("key", "translation")
               ->from("incoming_translations")
               ->where(array("locale" => $locale))
               ->get()
               ->as_array() as $row) {
        $translations[$row->key] = unserialize($row->translation);
      }
      // Override incoming with outgoing...
      foreach (Database::instance()
               ->select("key", "translation")
               ->from("outgoing_translations")
               ->where(array("locale" => $locale))
               ->get()
               ->as_array() as $row) {
        $translations[$row->key] = unserialize($row->translation);
      }

      $string_list = array();
      $cache = array();
      foreach ($calls as $key => $call) {
        list ($message, $options) = $call;
        // Ensure that the message is in the DB
        l10n_scanner::process_message($message, $cache);
        // Note: Not interpolating placeholders for the actual translation input field.
        // TODO: Might show a preview w/ interpolations (using $options)
        $translation = isset($translations[$key]) ? $translations[$key] : '';
        $string_list[] = array('source' => $message,
                               'key' => $key,
                               'translation' => $translation);
      }

      $v = new View('l10n_client.html');
      $v->string_list = $string_list;
      $v->l10n_search_form = self::_l10n_client_search_form();
      $v->plural_forms = l10n_client::plural_forms($locale);
      return $v;
    }

    return '';
  }
}
