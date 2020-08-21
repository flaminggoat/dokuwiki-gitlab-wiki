<?php
/**
 * DokuWiki Plugin gitlabwiki (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Theo Hussey <husseytg@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class syntax_plugin_gitlabwiki_gitlabwiki extends DokuWiki_Syntax_Plugin
{
    /**
     * @return string Syntax mode type
     */
    public function getType()
    {
        return 'substition';
    }

    /**
     * @return string Paragraph type
     */
    public function getPType()
    {
        return 'normal';
    }

    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort()
    {
        return 196;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('<gitlab-wiki[^>]*/>', $mode, 'plugin_gitlabwiki_gitlabwiki');
    }

//    public function postConnect()
//    {
//        $this->Lexer->addExitPattern('</FIXME>', 'plugin_gitlabwiki_gitlabwiki');
//    }

    /**
     * Handle matches of the gitlabwiki syntax
     *
     * @param string       $match   The match of the syntax
     * @param int          $state   The state of the handler
     * @param int          $pos     The position in the document
     * @param Doku_Handler $handler The handler
     *
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        switch($state){
            case DOKU_LEXER_SPECIAL :
                // Init @data
                $data = array(
                    'state' => $state
                );

                // Match @server and @token
                // preg_match("/server *= *(['\"])(.*?)\\1/", $match, $server);
                if (!isset($data['server'])) {
                    $data['server'] = $this->getConf('server.default');
                }
                if (!isset($data['token'])) {
                    $data['token'] = $this->getConf('token.default');
                }

                // Match @project
                preg_match("/project *= *(['\"])(.*?)\\1/", $match, $project);
                print_r($project)
                if (count($project) != 0) {
                    $data['project'] = $project[2];
                }

                return $data;
            case DOKU_LEXER_UNMATCHED :
                return array('state'=>$state, 'text'=>$match);
            default:
                return array('state'=>$state, 'bytepos_end' => $pos + strlen($match));
        }
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string        $mode     Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer $renderer The renderer
     * @param array         $data     The data from the handler() function
     *
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode !== 'xhtml') {
            return false;
        }

        return true;
    }
}

