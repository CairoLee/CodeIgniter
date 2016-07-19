<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lee_Controller extends CI_Controller {
    /**
     * @var $em
     */
    protected $em;

    function __construct()
    {
        parent::__construct();

        /** @var  $em Doctrine\ORM\EntityManager */
        $em = $this->doctrine->em;
        $this->em = $em;
    }

    public function assign($tpl_var, $value = NULL, $nocache = FALSE) {
        $this->smartytpl->assign($tpl_var, $value, $nocache);
    }

    public function display($template) {
        $this->smartytpl->display($template);
    }
}
