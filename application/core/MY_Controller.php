<?php
/**
 * Created by PhpStorm.
 * User: 11400277
 * Date: 28/03/2017
 * Time: 19:55
 */
class MY_Controller extends CI_Controller
{
    /**
     * MY_Controller constructor.
     * Uses model LanguageModel->get_languages
     * Uses private language_check
     * Uses private login_check
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * View builder
     * @param $url
     * @param array $data
     */
    protected function display($url, $data = array())
    {
        $this->twig->addGlobal('session', $this->session);
        $this->twig->display($url, $data);
    }
}