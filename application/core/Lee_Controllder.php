<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lee_Controllder extends CI_Controller {
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
}
