<?php
/**
 * Jenkins Action Plugin: Inserts a button into the toolbar
 *
 * @author Algorys
 */

if (!defined('DOKU_INC')) die();

class action_plugin_jenkins extends DokuWiki_Action_Plugin {

    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array ());
    }

    /**
   * Inserts a toolbar button
   */
    function insert_button(&$event, $param) {
        $event->data[] = array (
            'type' => 'format',
            'title' => $this->getLang('button'),
            'icon' => '../../plugins/jenkins/images/button.png',
            'open' => '<jenkins job="JOB_NAME"',
            'close' => ' />',
        );
  }

}
