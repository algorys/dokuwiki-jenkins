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

    function getURLProtocol($url) {
        if (strpos($url, 'https') !== false) {
            $url_protocol = array(
                'protocol' => 'https',
                'url' => str_replace('https://', '', $url)
            );
            return $url_protocol;
        } elseif (strpos($url, 'http') !== false) {
            $url_protocol = array(
                'protocol' => 'http',
                'url' => str_replace('http://', '', $url)
            );
            return $url_protocol;
        } else {
            return array('state'=>$state, 'bytepos_end' => $pos + strlen($match));
        }
    }

    // Dokuwiki Handler
    function handle($match, $state, $pos, Doku_Handler $handler) {
        switch($state){
            case DOKU_LEXER_SPECIAL :
                $data = array(
                        'state'=>$state,
                );

                // Jenkins Configuration 
                $jenkins_data = $this->getURLProtocol($this->getConf('jenkins.url'));
                $data['url'] = $jenkins_data['url'];
                $data['protocol'] = $jenkins_data['protocol'];
                $data['user'] = $this->getConf('jenkins.user');
                $data['token'] = $this->getConf('jenkins.token');

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
                $this->rendererJenkins($renderer, $data);
            case DOKU_LEXER_EXIT:
            case DOKU_LEXER_ENTER:
            case DOKU_LEXER_UNMATCHED:
                $renderer->doc .= $renderer->_xmlEntities($data['text']);
                break;
        }
        return true;
    }

    function rendererJenkins($renderer, $data) {
        // Get Jenkins data
        $jenkins = new DokuwikiJenkins($data);
        $url = $jenkins->getJobURLRequest($data['job']);
        $request = $jenkins->request($url);

        if ($request == '') {
            $this->renderErrorRequest($renderer, $data);
        } else {
            // Manage data
            $img = $this->getBuildIcon($request['result']);
            $duration = $this->getDurationFromMilliseconds($request['duration']);
            $short_desc = $request['actions'][0]['causes'][0]['shortDescription'];

            // Renderer
            $renderer->doc .= '<div><p>';
            $renderer->doc .= '<span><img src="lib/plugins/jenkins/images/jenkins.png" class="jenkinslogo"></span> ';
            $renderer->doc .= '<span class="jenkins">';
            $renderer->doc .= '<a href="'.$request['url'].'" class="jenkins" target="_blank">'.$request['fullDisplayName'].'</a> ';
            $renderer->doc .= '<img src="lib/plugins/jenkins/images/'.$img.'" class="jenkins" title="'.$request['result'].'">';
            $renderer->doc .= '</span></p>';
            $renderer->doc .= '<p>';
            $renderer->doc .= '<span> <b>'.$this->getLang('jenkins.duration').':</b> '.$duration.'</span>';
            $renderer->doc .= '<span> <b>'.$this->getLang('jenkins.msg').'</b> ';
            if ($short_desc != '')
                $renderer->doc .= $short_desc.'</span>';
            else
                $renderer->doc .= $this->getLang('jenkins.nodesc').'</span>';
            $renderer->doc .= '</p>';
            $renderer->doc .= '</div>';
        }
    }

    function renderErrorRequest($renderer, $data) {
        $renderer->doc .= '<div><p>';
        $renderer->doc .= '<span><img src="lib/plugins/jenkins/images/jenkins.png" class="jenkinslogo"></span> ';
        $renderer->doc .= '<span class="jenkinsfailed">';
        $renderer->doc .= sprintf($this->getLang('jenkins.error'), $data['job']);
        $renderer->doc .= '</span></p>';
        $renderer->doc .= '</div>';
    }

    function getBuildIcon($result) {
        $icons = Array(
            'SUCCESS' => 'success.svg',
            'ABORTED' => 'aborted.svg',
            'FAILURE' => 'failed.svg'
        );

        return $icons[$result];
    }

    function getDurationFromMilliseconds($ms) {
        $x = $ms / 1000;
        $seconds = $x % 60;
        $x /= 60;
        $minutes = $x % 60;
        $x /= 60;
        $hours = $x % 24;
        $x /= 24;
        $days = $x;

        $duration = '';
        if ($days >= 1) {
            $duration .= $days.'d ';
        }
        if ($hours >= 1) {
            $duration .= $hours.'h ';
        }
        if ($minutes >= 1) {
            $duration .= $minutes.'m ';
        }
        if ($seconds >= 1) {
            $duration .= $seconds.'s ';
        }

        return $duration;
    }

}
