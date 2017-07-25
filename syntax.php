<?php
/**
 * Jenkins Syntax Plugin: display and trigger Jenkins job inside Dokuwiki
 *
 * @author Algorys
 */

if (!defined('DOKU_INC')) die();
require 'jenkinsapi/jenkins.php';

class syntax_plugin_jenkins extends DokuWiki_Syntax_Plugin {

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'normal';
    }
    // Keep syntax inside plugin
    function getAllowedTypes() {
        return array('container', 'baseonly', 'substition','protected','disabled','formatting','paragraphs');
    }

    public function getSort() {
        return 199;
    }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<jenkins[^>]*/>', $mode, 'plugin_jenkins');
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        switch($state){
            case DOKU_LEXER_SPECIAL :
                $data = array(
                        'state'=>$state,
                );

                // Jenkins Job
                preg_match("/job *= *(['\"])(.*?)\\1/", $match, $job);
                if (count($job) != 0) {
                    $data['job'] = $job[2];
                }

                return $data;
            case DOKU_LEXER_UNMATCHED :
                return array('state'=>$state, 'text'=>$match);
            default:
                return array('state'=>$state, 'bytepos_end' => $pos + strlen($match));
        }
    }

    // Dokuwiki Renderer
    function render($mode, Doku_Renderer $renderer, $data) {
        if($mode != 'xhtml') return false;

        $renderer->info['cache'] = false;
        switch($data['state']) {
            case DOKU_LEXER_SPECIAL:
                $this->connectToServer($renderer, $data);
            case DOKU_LEXER_EXIT:
            case DOKU_LEXER_ENTER:
            case DOKU_LEXER_UNMATCHED:
                $renderer->doc .= $renderer->_xmlEntities($data['text']);
                break;
        }
        return true;
    }

    function connectToServer($renderer, $data) {
        $jenkins = new DokuwikiJenkins();

        $url = $jenkins->getJobURLRequest($data['job']);
        $request = $jenkins->request($url);
        print_r($request);
        $img = $this->getBuildIcon($request['result']);
        $renderer->doc .= '<div class="jenkins">';
        $renderer->doc .= '<span><img src="lib/plugins/jenkins/images/'.$img.'"></span> ';
        $renderer->doc .= '<a href="'.$request['url'].'">'.$request['fullDisplayName'].'</a>';
        $renderer->doc .= '</div>';
    }

    function getBuildIcon($result) {
        $icons = Array(
            'SUCCESS' => 'success.svg',
            'ABORTED' => 'aborted.svg',
            'FAILED' => 'failed.svg'
        );

        return $icons[$result];
    }

}
