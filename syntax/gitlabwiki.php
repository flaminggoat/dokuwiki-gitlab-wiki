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

require  __DIR__ .'/../parsedown/Parsedown.php';

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
        dbg("DEBUG MESSAGE");

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
                // print_r($project);
                if (count($project) != 0) {
                    $data['project'] = $project[2];
                }

                // Get markdown file from repository instead of project wiki
                // Match @mdfile
                preg_match("/mdfile *= *(['\"])(.*?)\\1/", $match, $mdfile);
                if (count($mdfile) != 0) {
                    $data['mdfile'] = $mdfile[2];
                }

                // Use specific ref of file, eg branch name, or commit hash
                // Match @ref
                preg_match("/ref *= *(['\"])(.*?)\\1/", $match, $ref);
                if (count($ref) != 0) {
                    $data['ref'] = $ref[2];
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

        if($mode != 'xhtml') return false;
        if($data['error']) {
            $renderer->doc .= $data['text'];
            return true;
        }

        $renderer->info['cache'] = false;
        switch($data['state']) {
            case DOKU_LEXER_SPECIAL:
                if (isset($data['mdfile'])) {
                    $this->renderGitlabMdFile($renderer, $data);
                } else {
                    $this->renderGitlabWiki($renderer, $data);
                }
                break;
            case DOKU_LEXER_ENTER:
            case DOKU_LEXER_EXIT:
            case DOKU_LEXER_UNMATCHED:
                $renderer->doc .= $renderer->_xmlEntities($data['text']);
                break;
        }

        return true;
    }

    function renderGitlabWiki($renderer, $data) {

        $client = curl_init();
        $url_request = $data['server'].'/api/v4/projects/'.urlencode($data['project']).'/wikis?with_content=1';
        curl_setopt($client, CURLOPT_URL, $url_request);
        curl_setopt($client, CURLOPT_HTTPHEADER, array("PRIVATE-TOKEN: ".$data['token']));
        curl_setopt($client, CURLOPT_SSL_VERIFYHOST, '1');
        curl_setopt($client, CURLOPT_SSL_VERIFYPEER, '0');
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        $answer = curl_exec($client);
        curl_close($client);

        $json = json_decode($answer, true);

        $Parsedown = new Parsedown();
        $Parsedown->setSafeMode(true);

        foreach($json as &$page) {
            $renderer->doc .= '<h1>'.$page["title"].'</h1>';
            $result = $Parsedown->text($page["content"]);
            $renderer->doc .= $result;
        }
    }

    function renderGitlabMdFile($renderer, $data) {

        if(isset($data['ref']) == false) {
            $data['ref'] = 'master';
        }

        $client = curl_init();
        $url_request = $data['server'].'/api/v4/projects/'.urlencode($data['project']).'/repository/files/'.urlencode($data['mdfile']).'?ref='.$data['ref'];
        curl_setopt($client, CURLOPT_URL, $url_request);
        curl_setopt($client, CURLOPT_HTTPHEADER, array("PRIVATE-TOKEN: ".$data['token']));
        curl_setopt($client, CURLOPT_SSL_VERIFYHOST, '1');
        curl_setopt($client, CURLOPT_SSL_VERIFYPEER, '0');
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        $answer = curl_exec($client);
        curl_close($client);

        $json = json_decode($answer, true);

        $Parsedown = new Parsedown();
        $Parsedown->setSafeMode(true);

        
        // $renderer->doc .= '<h1>'.$json["title"].'</h1>';
        $result = $Parsedown->text(base64_decode($json["content"]));
        $renderer->doc .= $result;
        
    }
}

